<?php

namespace Illuminated\Console;

use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminated\Console\Exceptions\ExceptionHandler;
use Illuminated\Console\Loggable\DatabaseHandler\DatabaseHandler;
use Illuminated\Console\Loggable\FileHandler\FileHandler;
use Illuminated\Console\Loggable\MailerHandler\MailerHandler;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait Loggable
{
    use FileHandler;
    use MailerHandler;
    use DatabaseHandler;

    protected $icLogger;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initializeLogging();

        return parent::initialize($input, $output);
    }

    protected function initializeLogging()
    {
        $this->initializeErrorHandling();

        $class = get_class($this);
        $host = gethostname();
        $ip = gethostbyname($host);
        $this->logInfo("Command `{$class}` initialized.");
        $this->logInfo("Host: `{$host}` (`{$ip}`).");

        if (db_is_mysql()) {
            $dbIp = (string) db_mysql_variable('wsrep_node_address');
            $dbHost = (string) db_mysql_variable('hostname');
            $dbPort = (string) db_mysql_variable('port');
            $now = db_mysql_now();
            $this->logInfo("Database host: `{$dbHost}`, port: `{$dbPort}`, ip: `{$dbIp}`.");
            $this->logInfo("Database date: `{$now}`.");
        }
    }

    private function initializeErrorHandling()
    {
        app()->singleton('log.iclogger', function () {
            return new Logger('ICLogger', $this->getLogHandlers());
        });
        $this->icLogger = app('log.iclogger');

        app()->singleton(ExceptionHandlerContract::class, ExceptionHandler::class);
        app(ExceptionHandlerContract::class)->initialize($this->icLogger);
    }

    private function getLogHandlers()
    {
        $handlers = [];

        $fileHandler = $this->getFileHandler();
        $handlers[] = $fileHandler;

        $mailerHandler = $this->getMailerHandler();
        if (!empty($mailerHandler)) {
            if ($this->enableNotificationDeduplication()) {
                $level = $this->getNotificationLevel();
                $time = $this->getNotificationDeduplicationTime();
                $mailerHandler = new DeduplicationHandler($mailerHandler, null, $level, $time);
            }
            $handlers[] = $mailerHandler;
        }

        $databaseHandler = $this->getDatabaseHandler();
        if (!empty($databaseHandler)) {
            $handlers[] = $databaseHandler;
        }

        return $handlers;
    }

    protected function logDebug($message, array $context = [])
    {
        return $this->icLogger->debug($message, $context);
    }

    protected function logInfo($message, array $context = [])
    {
        return $this->icLogger->info($message, $context);
    }

    protected function logNotice($message, array $context = [])
    {
        return $this->icLogger->notice($message, $context);
    }

    protected function logWarning($message, array $context = [])
    {
        return $this->icLogger->warning($message, $context);
    }

    protected function logError($message, array $context = [])
    {
        return $this->icLogger->error($message, $context);
    }

    protected function logCritical($message, array $context = [])
    {
        return $this->icLogger->critical($message, $context);
    }

    protected function logAlert($message, array $context = [])
    {
        return $this->icLogger->alert($message, $context);
    }

    protected function logEmergency($message, array $context = [])
    {
        return $this->icLogger->emergency($message, $context);
    }

    protected function getNotificationLevel()
    {
        return Logger::NOTICE;
    }

    protected function icLogger()
    {
        return $this->icLogger;
    }
}
