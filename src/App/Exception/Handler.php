<?php

namespace EasyTool\Framework\App\Exception;

use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Http\Server\Response\Handler as ResponseHandler;
use EasyTool\Framework\App\Logger;
use Psr\Http\Message\ResponseFactoryInterface;
use ReflectionClass;
use Throwable;

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
        body {color: #545454; font:14px/22px ''; margin: 20px;}
        div.message {margin-bottom: 10px; padding: 6px 12px;}
        div.step {display: flex;}
        span.index {display: block; flex: 0 0 20px; padding: 6px 12px;}
        span.detail {display: block; flex: 0 1 auto; padding: 6px 12px;}
        span.execution {color: #B71C1C; display: block;}
        span.argument {color: #545454;}
        span.position {display: block; font-size: 12px;}
        span.file {color: #F57F17;}
        </style>
    </head>
    <body>
        $content
    </body>
</html>
HTML;
    }

    private function formatArgsHtml($arguments, $method = null, $class = null): string
    {
        if (empty($arguments)) {
            return '';
        }

        $params = [];
        if ($class) {
            $reflectionClass = new ReflectionClass($class);
            foreach ($reflectionClass->getMethod($method)->getParameters() as $parameter) {
                $params[] = $parameter->getName();
            }
        }

        $argsHtml = [];
        foreach ($arguments as $i => $argument) {
            $title = is_object($argument)
                ? ('[' . get_class($argument) . ']')
                : (is_array($argument)
                    ? '<Array>'
                    : (is_string($argument) ? ('\'' . $argument . '\'') : $argument));
            $argsHtml[] = '<span class="argument" title="' . html_entity_decode($title) . '">'
                . (isset($params[$i]) ? ('$' . $params[$i]) : ('$argument_' . ($i + 1)))
                . '</span>';
        }
        return ' <span class="arguments">' . implode(', ', $argsHtml) . '</span> ';
    }

    /**
     * Handle output of exception
     */
    public function handle(Throwable $exception): void
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
                    $steps[] = ''
                        . '<span class="index">#' . ($totalSteps - $index) . '</span>'
                        . '<span class="detail">'
                        . '<span class="execution">'
                        . (isset($step['class']) ? ('<span class="class">' . $step['class'] . '</span> :: ') : '')
                        . '<span class="function">' . $step['function'] . '('
                        . $this->formatArgsHtml($step['args'], $step['function'] ?? null, $step['class'] ?? null)
                        . ')</span>'
                        . '</span>'
                        . (isset($step['file'])
                            ? ('<span class="position">'
                                . '<span class="file">' . $step['file'] . '</span>'
                                . ' (<span class="line">' . $step['line'] . '</span>)'
                                . '</span>')
                            : '')
                        . '</span>';
                }
                $body->write(
                    $this->wrapHtml(
                        '<div class="message">' . $exception->getMessage() . '</div>'
                        . '<div class="trace"><div class="step">'
                        . implode('</div><div class="step">', $steps)
                        . '</div></div>'
                    )
                );
        }

        $this->responseHandler->handle($response->withBody($body));
    }
}
