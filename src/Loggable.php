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

        $class = get_class($this);
        $host = gethostname();
        $ip = gethostbyname($host);
        $log->info("Command `{$class}` initialized.");
        $log->info("Host: `{$host}` (`{$ip}`).");

        if (laravel_db_is_mysql()) {
            $dbIp   = (string) laravel_db_mysql_variable('wsrep_node_address');
            $dbHost = (string) laravel_db_mysql_variable('hostname');
            $dbPort = (string) laravel_db_mysql_variable('port');
            $now = laravel_db_mysql_now();
            $log->info("Database host: `{$dbHost}`, port: `{$dbPort}`, ip: `{$dbIp}`.");
            $log->info("Database date: `{$now}`.");
        }
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
