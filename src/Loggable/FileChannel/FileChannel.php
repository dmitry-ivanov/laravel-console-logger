<?php

namespace Illuminated\Console\Loggable\FileChannel;

use Monolog\Handler\RotatingFileHandler;

trait FileChannel
{
    protected function getFileChannelHandler()
    {
        $fileHandler = new RotatingFileHandler($this->getLogPath(), 30);
        $fileHandler->setFilenameFormat('{date}', 'Y-m-d');
        $fileHandler->setFormatter(new MonologFormatter);

        return $fileHandler;
    }

    protected function getLogPath()
    {
        $name = str_replace(':', '/', $this->getName());
        return storage_path("logs/{$name}/date.log");
    }
}
