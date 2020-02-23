<?php

namespace Illuminated\Console\Loggable\FileChannel;

use Monolog\Handler\RotatingFileHandler;

trait FileChannel
{
    /**
     * Get the file channel handler.
     *
     * @return \Monolog\Handler\RotatingFileHandler
     */
    protected function getFileChannelHandler()
    {
        $handler = new RotatingFileHandler($this->getLogPath(), $this->getLogMaxFiles());

        $handler->setFilenameFormat('{date}', 'Y-m-d');
        $handler->setFormatter(new MonologFormatter);

        return $handler;
    }

    /**
     * Get the log file path.
     *
     * @return string
     */
    protected function getLogPath()
    {
        $name = str_replace(':', '/', $this->getName());

        return storage_path("logs/{$name}/date.log");
    }

    /**
     * Get the max number of stored log files.
     *
     * @return int
     */
    protected function getLogMaxFiles()
    {
        return 30;
    }
}
