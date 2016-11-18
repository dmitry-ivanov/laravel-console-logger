<?php

namespace Illuminated\Console\Loggable\FileChannel;

use Monolog\Handler\RotatingFileHandler;

trait FileChannel
{
    protected $useFileChannel = true;
    protected $fileChannelMaxFiles = 30;

    protected function getFileChannelHandler()
    {
        if (!$this->useFileChannel) {
            return;
        }

        $handler = new RotatingFileHandler($this->getLogPath(), $this->fileChannelMaxFiles);
        $handler->setFilenameFormat('{date}', 'Y-m-d');
        $handler->setFormatter(new MonologFormatter);
        return $handler;
    }

    protected function getLogPath()
    {
        $name = str_replace(':', '/', $this->getName());
        return storage_path("logs/{$name}/date.log");
    }
}
