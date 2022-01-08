<?php

namespace EasyTool\Framework\App\Http\Server;

use EasyTool\Framework\App\Http\Message\Request as HttpRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class Request extends HttpRequest implements ServerRequestInterface
{
    protected array $attributes;
    protected array $cookieParams;
    protected array $queryParams;
    protected array $serverParams;
    protected array $uploadedFiles;

    protected $parsedBody = null;

    public function __construct(
        StreamFactoryInterface $streamFactory,
        UriFactoryInterface $uriFactory
    ) {
        parent::__construct($streamFactory, $uriFactory);

        $this->cookieParams = $_COOKIE;
        $this->uploadedFiles = $_FILES;
        $this->queryParams = $_GET;
        $this->serverParams = array_change_key_case($_SERVER);

        $this->processServerParams();
        $this->processHeaders();
        $this->processBody();
    }

    /**
     * Parse server parameters
     */
    private function processServerParams(): void
    {
        $this->withProtocolVersion(
            substr(
                $this->serverParams['server_protocol'],
                strpos($this->serverParams['server_protocol'], '/') + 1
            )
        );
        $this->withMethod(strtoupper($this->serverParams['request_method']));
        $this->withBody($this->streamFactory->createStreamFromResource(fopen('php://input', 'r')));
        $this->getUri()->fromString(
            $this->serverParams['request_scheme'] . '://'
            . $this->serverParams['http_host']
            . $this->serverParams['request_uri']
        );
    }

    /**
     * Collect HTTP headers
     */
    private function processHeaders(): void
    {
        foreach ($this->serverParams as $key => $values) {
            if (strpos($key, 'http_') === 0) {
                foreach (preg_split('/, ?/', $values) as $value) {
                    $this->withAddedHeader(substr($key, 5), $value);
                }
            }
        }
    }

    /**
     * Parse request body base on header data
     */
    private function processBody(): void
    {
        if ($this->getMethod() == self::METHOD_GET) {
            return;
        }

        switch ($this->getHeaderLine('content_type')) {
            case 'application/x-www-form-urlencoded':
            case 'multipart/form-data':
                $this->parsedBody = $_POST;
                break;

            case 'application/json':
                $this->parsedBody = json_decode($this->body, true);
                break;
        }
    }

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
    public function withCookieParams(array $cookies): self
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
