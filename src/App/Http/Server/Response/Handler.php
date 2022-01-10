<?php

namespace EasyTool\Framework\App\Http\Server\Response;

use Psr\Http\Message\ResponseInterface;

class Handler
{
    public function handle(ResponseInterface $response)
    {
        header('HTTP/1.1 ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase());
        header('Content-Type: ' . $response->getHeaderLine('content_type'));

        echo $response->getBody();
    }
}
