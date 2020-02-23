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
     *
     * @var \Monolog\Logger
     */
    protected $icLogger;

    /**
     * Overwrite the console command initialization.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initializeLogging();

        parent::initialize($input, $output);
    }

    /**
     * Initialize the logging.
     *
     * @return void
     */
    protected function initializeLogging()
    {
        $this->initializeICLogger();
        $this->initializeErrorHandling();
        $this->logIterationHeaderInformation();
    }

    /**
     * Log debug message.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logDebug(string $message, array $context = [])
    {
        $this->icLogger->debug($message, $context);
    }

    /**
     * Log info message.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logInfo(string $message, array $context = [])
    {
        $this->icLogger->info($message, $context);
    }

    /**
     * Log notice message.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logNotice(string $message, array $context = [])
    {
        $this->icLogger->notice($message, $context);
    }

    /**
     * Log warning message.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logWarning(string $message, array $context = [])
    {
        $this->icLogger->warning($message, $context);
    }

    /**
     * Log error message.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logError(string $message, array $context = [])
    {
        $this->icLogger->error($message, $context);
    }

    /**
     * Log critical message.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logCritical(string $message, array $context = [])
    {
        $this->icLogger->critical($message, $context);
    }

    /**
     * Log alert message.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logAlert(string $message, array $context = [])
    {
        $this->icLogger->alert($message, $context);
    }

    /**
     * Log emergency message.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logEmergency(string $message, array $context = [])
    {
        $this->icLogger->emergency($message, $context);
    }

    /**
     * Initialize the logger.
     *
     * @return void
     */
    private function initializeICLogger()
    {
        app()->singleton('log.iclogger', function () {
            return new Logger('ICLogger', $this->getChannelHandlers());
        });

        $this->icLogger = app('log.iclogger');
    }

    /**
     * Initialize error handling.
     *
     * @return void
     */
    private function initializeErrorHandling()
    {
        app()->singleton(ExceptionHandlerContract::class, ExceptionHandler::class);

        app(ExceptionHandlerContract::class)->initialize($this->icLogger);
    }

    /**
     * Log the command iteration's header information.
     *
     * @return void
     */
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

    /**
     * Get used channel handlers.
     *
     * @return array
     */
    private function getChannelHandlers()
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
     *
     * @param string $name
     * @return bool
     */
    private function isLoggableChannelTrait(string $name)
    {
        return Str::startsWith($name, __NAMESPACE__ . '\Loggable')
            && Str::endsWith($name, 'Channel');
    }

    /**
     * Get the logger.
     *
     * @return \Monolog\Logger
     */
    protected function icLogger()
    {
        return $this->icLogger;
    }
}
