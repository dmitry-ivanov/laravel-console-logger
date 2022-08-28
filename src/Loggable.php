<?php

namespace Illuminated\Console;

use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Support\Str;
use Illuminated\Console\Exceptions\ExceptionHandler;
use Illuminated\Console\Loggable\FileChannel\FileChannel;
use Illuminated\Console\Loggable\Notifications\DatabaseChannel\DatabaseChannel;
use Illuminated\Console\Loggable\Notifications\EmailChannel\EmailChannel;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait Loggable
{
    use FileChannel;
    use EmailChannel;
    use DatabaseChannel;

    /**
     * The logger.
     */
    protected Logger $icLogger;

    /**
     * Overwrite the console command initialization.
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->initializeLogging();

        /** @noinspection PhpMultipleClassDeclarationsInspection */
        parent::initialize($input, $output);
    }

    /**
     * Initialize the logging.
     */
    protected function initializeLogging(): void
    {
        $this->initializeICLogger();
        $this->initializeErrorHandling();
        $this->logIterationHeaderInformation();
    }

    /**
     * Log debug message.
     */
    protected function logDebug(string $message, array $context = []): void
    {
        $this->icLogger->debug($message, $context);
    }

    /**
     * Log info message.
     */
    protected function logInfo(string $message, array $context = []): void
    {
        $this->icLogger->info($message, $context);
    }

    /**
     * Log notice message.
     */
    protected function logNotice(string $message, array $context = []): void
    {
        $this->icLogger->notice($message, $context);
    }

    /**
     * Log warning message.
     */
    protected function logWarning(string $message, array $context = []): void
    {
        $this->icLogger->warning($message, $context);
    }

    /**
     * Log error message.
     */
    protected function logError(string $message, array $context = []): void
    {
        $this->icLogger->error($message, $context);
    }

    /**
     * Log critical message.
     */
    protected function logCritical(string $message, array $context = []): void
    {
        $this->icLogger->critical($message, $context);
    }

    /**
     * Log alert message.
     */
    protected function logAlert(string $message, array $context = []): void
    {
        $this->icLogger->alert($message, $context);
    }

    /**
     * Log emergency message.
     */
    protected function logEmergency(string $message, array $context = []): void
    {
        $this->icLogger->emergency($message, $context);
    }

    /**
     * Initialize the logger.
     */
    private function initializeICLogger(): void
    {
        app()->singleton('log.iclogger', function () {
            return new Logger('ICLogger', $this->getChannelHandlers());
        });

        $this->icLogger = app('log.iclogger');
    }

    /**
     * Initialize error handling.
     */
    private function initializeErrorHandling(): void
    {
        app()->singleton(ExceptionHandlerContract::class, ExceptionHandler::class);

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        app(ExceptionHandlerContract::class)->initialize($this->icLogger());
    }

    /**
     * Log the command iteration's header information.
     */
    private function logIterationHeaderInformation(): void
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

    /**
     * Get used channel handlers.
     */
    private function getChannelHandlers(): array
    {
        $handlers = [];

        foreach (class_uses_recursive(get_class($this)) as $trait) {
            if (!$this->isLoggableChannelTrait($trait)) {
                continue;
            }

            $method = 'get' . class_basename($trait) . 'Handler';
            $handler = $this->$method();
            if (!empty($handler)) {
                $handlers[] = $handler;
            }
        }

        return $handlers;
    }

    /**
     * Check whether the given trait is "loggable channel" trait or not.
     */
    private function isLoggableChannelTrait(string $name): bool
    {
        return Str::startsWith($name, __NAMESPACE__ . '\Loggable')
            && Str::endsWith($name, 'Channel');
    }

    /**
     * Get the logger.
     */
    protected function icLogger(): Logger
    {
        return $this->icLogger;
    }
}
