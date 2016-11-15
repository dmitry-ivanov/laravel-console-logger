<?php

namespace Illuminated\Console;

use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminated\Console\Log\Formatter;
use Illuminated\Console\Log\HtmlFormatter;
use Illuminated\Console\Log\MysqlHandler;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\MandrillHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait Loggable
{
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

        $dbHandler = $this->getDbHandler();
        if (!empty($dbHandler)) {
            $handlers[] = $dbHandler;
        }

        return $handlers;
    }

    protected function getFileHandler()
    {
        $fileHandler = new RotatingFileHandler($this->getLogPath(), 30);
        $fileHandler->setFilenameFormat('{date}', 'Y-m-d');
        $fileHandler->setFormatter(new Formatter());

        return $fileHandler;
    }

    protected function getMailerHandler()
    {
        $recipients = $this->getFilteredNotificationRecipients();
        if (empty($recipients)) {
            return false;
        }

        $subject = $this->getNotificationSubject();
        $from = $this->getNotificationFrom();
        $level = $this->getNotificationLevel();

        $driver = config('mail.driver');
        switch ($driver) {
            case 'mail':
            case 'smtp':
            case 'sendmail':
            case 'mandrill':
                $mailer = app('swift.mailer');
                $message = $mailer->createMessage();
                $message->setSubject($subject);
                $message->setFrom(to_swiftmailer_emails($from));
                $message->setTo(to_swiftmailer_emails($recipients));
                $message->setContentType('text/html');
                $message->setCharset('utf-8');

                if ($driver == 'mandrill') {
                    $mailerHandler = new MandrillHandler(config('services.mandrill.secret'), $message, $level);
                } else {
                    $mailerHandler = new SwiftMailerHandler($mailer, $message, $level);
                }
                break;

            default:
                $to = to_rfc2822_email($recipients);
                $from = to_rfc2822_email($from);
                $mailerHandler = new NativeMailerHandler($to, $subject, $from, $level);
                $mailerHandler->setContentType('text/html');
                break;
        }
        $mailerHandler->setFormatter(new HtmlFormatter());

        return $mailerHandler;
    }

    protected function getDbHandler()
    {
        if (!$this->enableNotificationDbStoring()) {
            return false;
        }

        $table = $this->getNotificationDbTable();
        $callback = $this->getNotificationDbCallback();
        $level = $this->getNotificationLevel();

        return (new MysqlHandler($table, $callback, $level));
    }

    private function getFilteredNotificationRecipients()
    {
        $result = [];

        $recipients = $this->getNotificationRecipients();
        foreach ($recipients as $recipient) {
            if (!empty($recipient['address']) && is_email($recipient['address'])) {
                $result[] = $recipient;
            }
        }

        return $result;
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

    protected function getLogPath()
    {
        $name = str_replace(':', '/', $this->getName());
        return storage_path("logs/{$name}/date.log");
    }

    protected function getNotificationRecipients()
    {
        return [
            ['address' => null, 'name' => null],
        ];
    }

    protected function getNotificationSubject()
    {
        $env = str_upper(app()->environment());
        $name = $this->getName();
        return "[{$env}] %level_name% in `{$name}` command";
    }

    protected function getNotificationFrom()
    {
        return ['address' => 'no-reply@example.com', 'name' => 'ICLogger Notification'];
    }

    protected function getNotificationLevel()
    {
        return Logger::NOTICE;
    }

    protected function enableNotificationDeduplication()
    {
        return false;
    }

    protected function getNotificationDeduplicationTime()
    {
        return 60;
    }

    protected function enableNotificationDbStoring()
    {
        return false;
    }

    protected function getNotificationDbTable()
    {
        return 'iclogger_notifications';
    }

    protected function getNotificationDbCallback()
    {
        return null;
    }

    protected function icLogger()
    {
        return $this->icLogger;
    }
}
