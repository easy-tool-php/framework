<?php

namespace EasyTool\Framework\App;

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

        if (!is_dir(($dir = $this->fileManager->getDirectoryPath(FileManager::DIR_LOG)))) {
            mkdir($dir, 0644, true);
        }

        $handle = fopen($dir . '/' . $file, 'a');
        fwrite($handle, sprintf("[ %s ][ %s ] %s\n", $level, date('Y-m-d H:i:s'), $message));
        fclose($handle);
    }
}
