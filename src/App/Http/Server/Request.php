<?php

namespace EasyTool\Framework\App\Http\Server;

use EasyTool\Framework\App\Http\Message\Request as HttpRequest;
use Psr\Http\Message\ServerRequestInterface;

class Request extends HttpRequest implements ServerRequestInterface
{
    /**
     * @inheritDoc
     */
    public function getServerParams()
    {
    }

    /**
     * @inheritDoc
     */
    public function getCookieParams()
    {
    }

    /**
     * @inheritDoc
     */
    public function withCookieParams(array $cookies)
    {
    }

    /**
     * @inheritDoc
     */
    public function getQueryParams()
    {
    }

    /**
     * @inheritDoc
     */
    public function withQueryParams(array $query)
    {
    }

    /**
     * @inheritDoc
     */
    public function getUploadedFiles()
    {
    }

    /**
     * @inheritDoc
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
    }

    /**
     * @inheritDoc
     */
    public function getParsedBody()
    {
    }

    /**
     * @inheritDoc
     */
    public function withParsedBody($data)
    {
    }

    public function getAttributes()
    {
    }

    /**
     * @inheritDoc
     */
    public function getAttribute($name, $default = null)
    {
    }

    /**
     * @inheritDoc
     */
    public function withAttribute($name, $value)
    {
    }

    /**
     * @inheritDoc
     */
    public function withoutAttribute($name)
    {
    }
}
