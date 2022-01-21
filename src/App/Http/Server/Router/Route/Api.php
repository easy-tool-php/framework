<?php

namespace EasyTool\Framework\App\Http\Server\Router\Route;

use EasyTool\Framework\App\Http\Server\Request;
use Psr\Http\Message\ServerRequestInterface;

class Api extends AbstractRoute
{
    public const CONFIG_PATH = 'api/route';

    private array $routes = [];

    /**
     * Check whether a given string matches variable part format
     */
    private function checkVariablePart($part)
    {
        return (strpos($part, ':') === 0) ? substr($part, 1) : false;
    }

    /**
     * Add routes
     */
    public function addRoutes(array $routes): void
    {
        $this->routes = array_merge($this->routes, $routes);
    }

    /**
     * Check whether the request path matches an API route
     */
    public function match(ServerRequestInterface $request): bool
    {
        [$prefix, $path] = array_pad(explode('/', trim($request->getUri()->getPath(), '/'), 2), 2, null);
        if ($prefix != $this->config->get(self::CONFIG_PATH, self::CONFIG_NAME)) {
            return false;
        }

        $arrPath = explode('/', trim($path, '/'));
        foreach ($this->routes as $route => $action) {
            [$method, $apiPath] = explode(':', $route, 2);
            if ($method != $request->getMethod()) {
                continue;
            }

            $arrRoute = explode('/', trim($apiPath, '/'));
            if (count($arrPath) != count($arrRoute)) {
                continue;
            }

            $matched = true;
            $variables = [];
            foreach ($arrRoute as $i => $part) {
                if (($variable = $this->checkVariablePart($part))) {
                    $variables[$variable] = $arrPath[$i];
                    continue;
                }
                if ($arrPath[$i] != $part) {
                    $matched = false;
                    break;
                }
            }
            if ($matched) {
                $request->withAttribute(Request::ACTION, [$this->objectManager->create($action), 'execute']);
                $request->withAttribute(Request::API_PARAMS, $variables);
                return true;
            }
        }
        return false;
    }
}
