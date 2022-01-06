<?php

namespace EasyTool\Framework\App\Http\Message;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    public const SCHEME_HTTP = 'http';
    public const SCHEME_HTTPS = 'https';

    public const PORT_HTTP = '80';
    public const PORT_HTTPS = '443';

    private string $scheme = '';
    private string $username = '';
    private ?string $password = null;
    private string $host = '';
    private ?int $port = null;
    private string $path = '';
    private string $query = '';
    private string $fragment = '';

    /**
     * @inheritDoc
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @inheritDoc
     */
    public function getAuthority(): string
    {
        return (($userInfo = $this->getUserInfo()) ? ($userInfo . '@') : '')
            . $this->host
            . ($this->port ? (':' . $this->port) : '');
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo(): string
    {
        return $this->username
            . ($this->password ? (':' . $this->password) : '');
    }

    /**
     * @inheritDoc
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @inheritDoc
     */
    public function getPort(): ?int
    {
        if (
            ($this->scheme == self::SCHEME_HTTP && $this->port == self::PORT_HTTP)
            || ($this->scheme == self::SCHEME_HTTPS && $this->port == self::PORT_HTTPS)
        ) {
            return null;
        }
        return $this->port;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @inheritDoc
     */
    public function withScheme($scheme): Uri
    {
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withUserInfo($user, $password = null): Uri
    {
        $this->username = $user;
        $this->password = $password;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withHost($host): Uri
    {
        $this->host = $host;
        return $this;
    }

    public function withPort($port): Uri
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withPath($path): Uri
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withQuery($query): Uri
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withFragment($fragment): Uri
    {
        $this->fragment = $fragment;
        return $this;
    }

    /**
     * Parse a string and assign the parts
     */
    public function fromString(string $uri): Uri
    {
        $info = parse_url($uri);

        $this->scheme = $info['scheme'] ?? '';
        $this->username = $info['user'] ?? '';
        $this->password = $info['pass'] ?? null;
        $this->host = $info['host'] ?? '';
        $this->port = $info['port'] ?? null;
        $this->path = $info['path'] ?? '';
        $this->query = $info['query'] ?? '';
        $this->fragment = $info['fragment'] ?? '';

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->getScheme() . '://'
            . $this->getAuthority()
            . $this->getPath()
            . (($query = $this->getQuery()) ? ('?' . $query) : '')
            . (($fragment = $this->getFragment()) ? ('#' . $fragment) : '');
    }
}
