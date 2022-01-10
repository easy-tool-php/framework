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

    /**
     * Wrap given content with styled HTML wrapper
     */
    private function wrapHtml($content): string
    {
        return <<<HTML
<html>
    <head>
        <style>
        body {color: #545454; font:14px/22px '';}
        span.class {color: #B71C1C;}
        span.type {color: #545454;}
        span.function {color: #43A047;}
        span.file {color: #F57F17;}
        span.line {color: #43A047;}
        </style>
    </head>
    <body>
        $content
    </body>
</html>
HTML;
    }

    /**
     * Handle output of exception
     */
    public function handle(Exception $exception): void
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
                $steps = [];
                foreach ($traces as $index => $step) {
                    $steps[] = sprintf(
                        '#%d %s%s%s%s',
                        $totalSteps - $index,
                        isset($step['class']) ? ('<span class="class">' . $step['class'] . '</span>') : '',
                        $step['type'] ? ('<span class="type">' . $step['type'] . '</span>') : '',
                        '<span class="function">' . $step['function'] . '</span>',
                        isset($step['file']) ? sprintf(
                            ' at <span class="file">%s</span> line <span class="line">%s</span>',
                            $step['file'],
                            $step['line']
                        ) : ''
                    );
                }
                $body->write(
                    $this->wrapHtml(
                        '<p>' . $exception->getMessage() . '</p>'
                        . '<p>' . implode('<br/>', $steps) . '</p>'
                    )
                );
        }

        $this->responseHandler->handle($response->withBody($body));
    }
}
