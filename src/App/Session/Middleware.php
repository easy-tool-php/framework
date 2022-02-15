<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Session;

use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Di\Container as DiContainer;
use EasyTool\Framework\App\Env\Config as EnvConfig;
use Laminas\Session\Config\SessionConfig;
use Laminas\Session\SessionManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Middleware implements MiddlewareInterface
{
    public const ENV_PATH = 'session';
    public const CONFIG_ADAPTER = 'adapter';
    public const CONFIG_ADAPTER_OPTS = 'adapter_options';
    public const CONFIG_OPTIONS = 'options';

    private Area $area;
    private DiContainer $diContainer;
    private EnvConfig $envConfig;
    private array $adapters;

    public function __construct(
        Area $area,
        DiContainer $diContainer,
        EnvConfig $envConfig,
        array $adapters = []
    ) {
        $this->adapters = $adapters;
        $this->area = $area;
        $this->diContainer = $diContainer;
        $this->envConfig = $envConfig;
    }

    /**
     * Only backend or frontend area needs to handle session
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array($this->area->getCode(), [Area::BACKEND, Area::FRONTEND])) {
            /** @var SessionConfig $sessionConfig */
            /** @var SessionManager $sessionManager */
            $configData = $this->envConfig->get(self::ENV_PATH);
            $sessionConfig = $this->diContainer->create(SessionConfig::class);
            $sessionManager = $this->diContainer->create(SessionManager::class, [
                'config' => $sessionConfig->setOptions($configData[self::CONFIG_OPTIONS] ?? []),
                'saveHandler' => $this->diContainer->create(
                    $this->adapters[$configData[self::CONFIG_ADAPTER]],
                    ['options' => $configData[self::CONFIG_ADAPTER_OPTS] ?? []]
                )
            ]);
            $sessionManager->start();
            $this->diContainer->setInstance(SessionManager::class, $sessionManager);
        }
        return $handler->handle($request);
    }
}
