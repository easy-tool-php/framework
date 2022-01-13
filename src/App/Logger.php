<?php

namespace EasyTool\Framework\App;

use EasyTool\Framework\App;
use EasyTool\Framework\Filesystem\FileManager;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class Logger extends AbstractLogger
{
    private FileManager $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = [])
    {
        switch ($level) {
            case LogLevel::CRITICAL:
            case LogLevel::ERROR:
                $file = 'exception.log';
                break;

            case LogLevel::DEBUG:
                $file = 'debug.log';
                break;

            default:
                $file = 'system.log';
                break;
        }

        if (!is_dir(($dir = $this->app->getDirectoryPath(App::DIR_LOG)))) {
            mkdir($dir, 0755, true);
        }

        $handle = fopen($dir . '/' . $file, 'a');
        fwrite($handle, sprintf("[ %s ][ %s ] %s\n\n", $level, date('Y-m-d H:i:s'), $message));
        fclose($handle);
    }
}
