<?php

namespace EasyTool\Framework\App\Module\Controller;

use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractController implements ControllerInterface
{
    protected ServerRequestInterface $request;

    public function __construct(Context $context)
    {
        $this->request = $context->getRequest();
    }
}
