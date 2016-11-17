<?php

namespace Illuminated\Console;

use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminated\Console\Exceptions\ExceptionHandler;
use Illuminated\Console\Loggable\DatabaseChannel\DatabaseChannel;
use Illuminated\Console\Loggable\FileChannel\FileChannel;
use Illuminated\Console\Loggable\MailerChannel\MailerChannel;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait Loggable
{
    use FileChannel;
    use MailerChannel;
    use DatabaseChannel;

    protected $icLogger;

    protected function getNotificationLevel()
    {
        return Logger::NOTICE;
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

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initializeLogging();

        return parent::initialize($input, $output);
    }

    protected function initializeLogging()
    {
        $this->initializeErrorHandling();
        $this->logIterationHeaderInformation();
    }

    private function initializeErrorHandling()
    {
        app()->singleton('log.iclogger', function () {
            return new Logger('ICLogger', $this->getChannelHandlers());
        });
        $this->icLogger = app('log.iclogger');

        app()->singleton(ExceptionHandlerContract::class, ExceptionHandler::class);
        app(ExceptionHandlerContract::class)->initialize($this->icLogger);
    }

    private function logIterationHeaderInformation()
    {
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

    private function getChannelHandlers()
    {
        $handlers = [];

        foreach (class_uses_recursive($this) as $trait) {
            if ($this->isLoggableChannelTrait($trait)) {
                $method = 'get' . class_basename($trait) . 'Handler';
                $handler = $this->$method();
                if (!empty($handler)) {
                    $handlers[] = $handler;
                }
            }
        }

        return $handlers;
    }

    private function isLoggableChannelTrait($name)
    {
        return preg_match('/Illuminated\\\Console\\\Loggable\\\.*?Channel\\\.*?Channel/', $name);
    }

    protected function icLogger()
    {
        return $this->icLogger;
    }
}
