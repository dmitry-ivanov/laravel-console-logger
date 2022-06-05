<?php

namespace Illuminated\Console\Loggable\FileChannel;

use Monolog\Handler\RotatingFileHandler;

trait FileChannel
{
    /**
     * Get the file channel handler.
     *
     * @noinspection PhpUnused
     */
    protected function getFileChannelHandler(): RotatingFileHandler
    {
        $handler = new RotatingFileHandler($this->getLogPath(), $this->getLogMaxFiles());

        $handler->setFilenameFormat('{date}', 'Y-m-d');
        $handler->setFormatter(new MonologFormatter);

        return $handler;
    }

    /**
     * Get the log file path.
     */
    protected function getLogPath(): string
    {
        $name = str_replace(':', '/', $this->getName());

        return storage_path("logs/{$name}/date.log");
    }

    /**
     * Get the max number of stored log files.
     */
    protected function getLogMaxFiles(): int
    {
        return 30;
    }
}
