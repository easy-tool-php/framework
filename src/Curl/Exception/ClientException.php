<?php

namespace EasyTool\Framework\Curl\Exception;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;

class ClientException extends Exception implements ClientExceptionInterface
{
}
