<?php

namespace EasyTool\Framework\App\Http\Server;

use EasyTool\Framework\App\Http\Message\Request as HttpRequest;
use Psr\Http\Message\ServerRequestInterface;

class Request extends HttpRequest implements ServerRequestInterface
{
    protected array $attributes;
    protected array $cookieParams;
    protected array $queryParams;
    protected array $serverParams;
    protected array $uploadedFiles;

    protected $parsedBody;

    /**
     * @inheritDoc
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * @inheritDoc
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * @inheritDoc
     */
    public function withCookieParams(array $cookies): Request
    {
        $this->cookieParams = $cookies;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @inheritDoc
     */
    public function withQueryParams(array $query): Request
    {
        $this->queryParams = $query;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * @inheritDoc
     */
    public function withUploadedFiles(array $uploadedFiles): Request
    {
        $this->uploadedFiles = $uploadedFiles;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * @inheritDoc
     */
    public function withParsedBody($data): Request
    {
        $this->parsedBody = $data;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @inheritDoc
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function withAttribute($name, $value): Request
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withoutAttribute($name): Request
    {
        unset($this->attributes[$name]);
        return $this;
    }
}
