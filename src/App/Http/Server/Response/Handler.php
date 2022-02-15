<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

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
