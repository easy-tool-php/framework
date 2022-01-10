<?php

namespace EasyTool\Framework\App\Exception;

use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Http\Server\Response\Handler as ResponseHandler;
use EasyTool\Framework\App\Logger;
use Exception;
use Psr\Http\Message\ResponseFactoryInterface;

class Handler
{
    private Area $area;
    private Logger $logger;
    private ResponseHandler $responseHandler;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        Area $area,
        Logger $logger,
        ResponseHandler $responseHandler,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->area = $area;
        $this->logger = $logger;
        $this->responseHandler = $responseHandler;
        $this->responseFactory = $responseFactory;
    }

    public function handle(Exception $exception)
    {
        $traces = $exception->getTrace();
        $totalSteps = count($traces);
        $steps = [];
        foreach ($traces as $index => $step) {
            $steps[] = sprintf(
                '#%d %s%s%s%s',
                $totalSteps - $index,
                $step['class'] ?? '',
                $step['type'] ?? '',
                $step['function'],
                isset($step['file']) ? sprintf(' at %s line %s', $step['file'], $step['line']) : ''
            );
        }

        $this->logger->error($exception->getMessage() . "\n" . implode("\n", $steps));

        $response = $this->responseFactory->createResponse(500);
        $body = $response->getBody();

        switch ($this->area->getCode()) {
            case Area::API:
                $body->write(json_encode(['message' => $exception->getMessage(), 'traces' => $steps]));
                break;

            default:
                $body->write($exception->getMessage() . "\n" . implode("\n", $steps));
        }

        $this->responseHandler->handle($response->withBody($body));
    }
}
