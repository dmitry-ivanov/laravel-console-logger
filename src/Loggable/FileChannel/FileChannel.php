<?php

namespace Illuminated\Console\Loggable\FileChannel;

use Monolog\Handler\RotatingFileHandler;

trait FileChannel
{
    protected $useFileChannel = true;

    protected function getFileChannelHandler()
    {
        if (!$this->useFileChannel) {
            return;
        }

        $handler = new RotatingFileHandler($this->getLogPath(), $this->getLogMaxFiles());
        $handler->setFilenameFormat('{date}', 'Y-m-d');
        $handler->setFormatter(new MonologFormatter);
        return $handler;
    }

    protected function getLogPath()
    {
        $name = str_replace(':', '/', $this->getName());
        return storage_path("logs/{$name}/date.log");
    }

    protected function getLogMaxFiles()
    {
        return 30;
    }
}
