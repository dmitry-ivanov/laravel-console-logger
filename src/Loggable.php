<?php

namespace Illuminated\Console;

use Illuminate\Support\Str;
use Illuminated\Console\Log\Formatter;
use Monolog\ErrorHandler;
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
        ErrorHandler::register($log);

        $log->info('Hello World!');
        $log->info('Message with context!', [
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
                ],
            ],
        ]);
        $log->error('Error Test!');
    }

    private function getLogHandlers()
    {
        $rotatingFileHandler = new RotatingFileHandler($this->getLogPath(), 30);
        $rotatingFileHandler->setFilenameFormat('{date}', 'Y-m-d');
        $rotatingFileHandler->setFormatter(new Formatter());

        return [$rotatingFileHandler];
    }

    protected function getLogPath()
    {
        $name = Str::replaceFirst(':', '/', $this->getName());
        return storage_path("logs/{$name}/date.log");
    }
}
