<?php

namespace EasyTool\Framework\App\Module\Controller;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractController implements ControllerInterface
{
    protected ServerRequestInterface $request;
    protected ResponseFactoryInterface $responseFactory;

    public function __construct(Context $context)
    {
        $this->request = $context->getRequest();
        $this->responseFactory = $context->getResponseFactory();
    }

    protected function createJsonResponse(array $result): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        $body = $response->getBody();
        $body->write(json_encode($result));
        return $response
            ->withHeader('content_type', 'application/json')
            ->withBody($body);
    }
}
