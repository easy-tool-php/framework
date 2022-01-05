<?php

namespace EasyTool\Framework\App\Http\Message;

use EasyTool\Framework\App\Http\Message;
use Psr\Http\Message\ResponseInterface;

class Response extends Message implements ResponseInterface
{
    /**
     * @inheritDoc
     */
    public function getStatusCode()
    {
    }

    /**
     * @inheritDoc
     */
    public function withStatus($code, $reasonPhrase = '')
    {
    }

    /**
     * @inheritDoc
     */
    public function getReasonPhrase()
    {
    }
}
