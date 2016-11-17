<?php

namespace Illuminated\Console\Loggable\FileHandler;

use Monolog\Handler\RotatingFileHandler;

trait FileHandler
{
    protected function getFileHandler()
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
