<?php

namespace Illuminated\Console;

use Illuminated\Console\Log\Formatter;
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
        $log = new Logger('ICL', $this->getLogHandlers());
        $log->info('Hello World!', [
            'isOkay' => true,
            'count' => 3000,
            'type' => 'cool',
            'objects' => [
                [
                    'name' => 'First',
                    'date' => '2016-06-17 14:15:58',
                ],
                [
                    'name' => 'Second',
                    'date' => '2016-06-17 15:15:58',
                ],
                [
                    'name' => 'Third',
                    'date' => '2016-06-17 16:15:58',
                ]
            ],
        ]);
    }

    private function getLogHandlers()
    {
        $type = $this->type();
        $entity = $this->argument('entity');
        $path = storage_path("logs/cloud/{$type}/{$entity}/date.log");

        $rotatingFileHandler = new RotatingFileHandler($path, 30);
        $rotatingFileHandler->setFilenameFormat('{date}', 'Y-m-d');
        $rotatingFileHandler->setFormatter(new Formatter());

        return [$rotatingFileHandler];
    }
}
