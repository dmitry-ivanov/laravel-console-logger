<?php

namespace Illuminated\Console\Exceptions;

use Illuminate\Foundation\Exceptions\Handler;
use Monolog\Logger;
use Throwable;

class ExceptionHandler extends Handler
{
    /**
     * The logger instance.
     */
    private Logger $logger;

    /**
     * Time when execution started.
     */
    private float $timeStarted;

    /**
     * Reserved memory for the shutdown execution.
     *
     * @see https://github.com/dmitry-ivanov/laravel-console-logger/issues/4
     */
    protected ?string $reservedMemory;

    /**
     * Initialize the exception handler.
     */
    public function initialize(Logger $logger): void
    {
        $this->setLogger($logger);
        $this->registerShutdownFunction();
    }

    /**
     * Set the logger.
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Report or log an exception.
     *
     * Note that this method doesn't decorate, but overwrite the parent method:
     * @see https://github.com/dmitry-ivanov/laravel-console-logger/pull/11
     */
    public function report(Throwable $e): void
    {
        $context = [
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        if ($e instanceof RuntimeException) {
            $exceptionContext = $e->getContext();
            if (!empty($exceptionContext)) {
                $context['context'] = $exceptionContext;
            }
        }

        $this->logger->error($e->getMessage(), $context);

        $this->addSentrySupport($e);
    }

    /**
     * Add Sentry support.
     */
    private function addSentrySupport(Throwable $e): void
    {
        if (app()->bound('sentry') && $this->shouldReport($e)) {
            app('sentry')->captureException($e);
        }
    }

    /**
     * Register the shutdown function.
     */
    private function registerShutdownFunction(): void
    {
        $this->timeStarted = microtime(true);
        $this->reservedMemory = str_repeat(' ', 20 * 1024);

        register_shutdown_function([$this, 'onShutdown']);
    }

    /**
     * Callback for the shutdown function.
     */
    public function onShutdown(): void
    {
        $this->reservedMemory = null;

        $timeFinished = microtime(true);
        $executionTime = round($timeFinished - $this->timeStarted, 3);
        $this->logger->info("Execution time: {$executionTime} sec.");

        $memoryPeak = format_bytes(memory_get_peak_usage(true));
        $this->logger->info("Memory peak usage: {$memoryPeak}.");

        $this->logger->info('%separator%');

        $handlers = $this->logger->getHandlers();
        foreach ($handlers as $handler) {
            $handler->close();
        }
    }
}
