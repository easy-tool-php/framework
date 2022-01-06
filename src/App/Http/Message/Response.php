<?php

namespace EasyTool\Framework\App\Http\Message;

use EasyTool\Framework\App\Http\Message;
use Psr\Http\Message\ResponseInterface;

class Response extends Message implements ResponseInterface
{
    private int $statusCode;
    private string $reasonPhrase;

    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @inheritDoc
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * @inheritDoc
     */
    public function withStatus($code, $reasonPhrase = ''): Response
    {
        $this->statusCode = $code;
        $this->reasonPhrase = $reasonPhrase;
        return $this;
    }
}
