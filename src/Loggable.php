<?php

namespace Illuminated\Console;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait Loggable
{
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initializeLogging();

        return parent::initialize($input, $output);
    }

    protected function initializeLogging()
    {
        $type = $this->type();
        $environment = app()->environment();
        $path = storage_path("logs/cloud/{$type}/{$this->entity}/date.log");

        $handler = new RotatingFileHandler($path, 30);
        $handler->setFilenameFormat('{date}', 'Y-m-d');
        $log = new Logger($environment, [$handler]);

        $log->info('Hello World!');
    }
}
