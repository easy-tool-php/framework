<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Module\Controller;

use EasyTool\Framework\App\Di\Container as DiContainer;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractController implements ControllerInterface
{
    public const CONTENT_TYPE_JSON = 'application/json';
    public const CONTENT_TYPE_FILE = 'application/octet-stream';
    public const CONTENT_TYPE_PDF = 'application/pdf';
    public const CONTENT_TYPE_XHTML = 'application/xhtml+xml';
    public const CONTENT_TYPE_XML = 'application/xml';
    public const CONTENT_TYPE_GIF = 'image/gif';
    public const CONTENT_TYPE_JPEG = 'image/jpeg';
    public const CONTENT_TYPE_PNG = 'image/png';
    public const CONTENT_TYPE_HTML = 'text/html';

    protected DiContainer $diContainer;
    protected ServerRequestInterface $request;
    protected ResponseFactoryInterface $responseFactory;

    public function __construct(Context $context)
    {
        $this->diContainer = $context->getDiContainer();
        $this->request = $context->getRequest();
        $this->responseFactory = $context->getResponseFactory();
    }

    protected function createResponse($content, $contentType = self::CONTENT_TYPE_HTML): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        $body = $response->getBody();
        $body->write($content);
        return $response
            ->withHeader('content_type', $contentType)
            ->withBody($body);
    }

    protected function createJsonResponse(array $result): ResponseInterface
    {
        return $this->createResponse(json_encode($result), self::CONTENT_TYPE_JSON);
    }
}
