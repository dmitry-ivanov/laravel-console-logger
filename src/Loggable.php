<?php

namespace Illuminated\Console;

use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Support\Str;
use Illuminated\Console\Log\ExceptionHandler;
use Illuminated\Console\Log\Formatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait Loggable
{
    private $icl;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initializeLogging();

        return parent::initialize($input, $output);
    }

    protected function initializeLogging()
    {
        $this->initializeLoggingBindings();

        $class = get_class($this);
        $host = gethostname();
        $ip = gethostbyname($host);
        $this->logInfo("Command `{$class}` initialized.");
        $this->logInfo("Host: `{$host}` (`{$ip}`).");

        if (laravel_db_is_mysql()) {
            $dbIp = (string) laravel_db_mysql_variable('wsrep_node_address');
            $dbHost = (string) laravel_db_mysql_variable('hostname');
            $dbPort = (string) laravel_db_mysql_variable('port');
            $now = laravel_db_mysql_now();
            $this->logInfo("Database host: `{$dbHost}`, port: `{$dbPort}`, ip: `{$dbIp}`.");
            $this->logInfo("Database date: `{$now}`.");
        }
    }

    private function initializeLoggingBindings()
    {
        app()->singleton('log.icl', function () {
            return new Logger('ICL', $this->getLogHandlers());
        });
        $this->icl = app('log.icl');

        app()->singleton(ExceptionHandlerContract::class, ExceptionHandler::class);
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

    protected function logDebug($message, array $context = [])
    {
        return $this->icl->debug($message, $context);
    }

    protected function logInfo($message, array $context = [])
    {
        return $this->icl->info($message, $context);
    }

    protected function logNotice($message, array $context = [])
    {
        return $this->icl->notice($message, $context);
    }

    protected function logWarning($message, array $context = [])
    {
        return $this->icl->warning($message, $context);
    }

    protected function logError($message, array $context = [])
    {
        return $this->icl->error($message, $context);
    }

    protected function logCritical($message, array $context = [])
    {
        return $this->icl->critical($message, $context);
    }

    protected function logAlert($message, array $context = [])
    {
        return $this->icl->alert($message, $context);
    }

    protected function logEmergency($message, array $context = [])
    {
        return $this->icl->emergency($message, $context);
    }
}
