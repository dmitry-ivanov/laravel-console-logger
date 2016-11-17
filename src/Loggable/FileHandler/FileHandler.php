<?php

namespace Illuminated\Console\Loggable\FileHandler;

use Illuminated\Console\Log\Formatter;
use Monolog\Handler\RotatingFileHandler;

trait FileHandler
{
    protected function getFileHandler()
    {
        $fileHandler = new RotatingFileHandler($this->getLogPath(), 30);
        $fileHandler->setFilenameFormat('{date}', 'Y-m-d');
        $fileHandler->setFormatter(new Formatter());

        return $fileHandler;
    }

    protected function getLogPath()
    {
        $name = str_replace(':', '/', $this->getName());
        return storage_path("logs/{$name}/date.log");
    }
}
