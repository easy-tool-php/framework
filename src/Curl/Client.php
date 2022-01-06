<?php

namespace EasyTool\Framework\Curl;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class Client implements ClientInterface
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
        $request = $this->requestFactory->createRequest();
        $response = $this->responseFactory->createResponse();
        return $response;
    }
}
