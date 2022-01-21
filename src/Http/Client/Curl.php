<?php

namespace EasyTool\Framework\Http\Client;

use EasyTool\Framework\Http\Message\Request;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class Curl implements ClientInterface
{
    private RequestFactoryInterface $requestFactory;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        RequestFactoryInterface $requestFactory,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $url = (string)$request->getUri();
        $headers = [];
        foreach (array_keys($request->getHeaders()) as $name) {
            $headers[] = $request->getHeaderLine($name);
        }
        $data = (string)$request->getBody();

        $opts = [
            CURLOPT_HEADER         => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTP_VERSION   => 1,
            CURLOPT_URL            => $url,
            CURLOPT_HTTPHEADER     => $headers
        ];

        switch ($request->getMethod()) {
            case Request::METHOD_POST:
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $data;
                break;

            case Request::METHOD_PUT:
                $opts[CURLOPT_POST] = 0;
                $opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $opts[CURLOPT_POSTFIELDS] = $data;
                break;

            case Request::METHOD_DELETE:
                $opts[CURLOPT_POST] = 0;
                $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                $opts[CURLOPT_POSTFIELDS] = $data;
                break;

            case Request::METHOD_GET:
                $opts[CURLOPT_POST] = 0;
                if (!empty($data)) {
                    $opts[CURLOPT_URL] .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($data);
                }
                break;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $remoteResponse = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception\ClientException($error);
        }

        $response = $this->responseFactory->createResponse();
        return $response->withBody($response->getBody()->write($remoteResponse));
    }
}