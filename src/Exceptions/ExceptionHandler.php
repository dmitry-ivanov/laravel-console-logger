<?php

namespace Illuminated\Console\Exceptions;

use Illuminate\Foundation\Exceptions\Handler;
use Psr\Log\LoggerInterface;
use Throwable;

class ExceptionHandler extends Handler
{
    /**
     * The logger instance.
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Time when execution started.
     *
     * @var float
     */
    private $timeStarted;

    /**
     * Time when execution finished.
     *
     * @var float
     */
    private $timeFinished;

    /**
     * Reserved memory for the shutdown execution.
     *
     * @see https://github.com/dmitry-ivanov/laravel-console-logger/issues/4
     *
     * @var string
     */
    protected $reservedMemory;

    /**
     * Initialize the exception handler.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @return void
     */
    public function initialize(LoggerInterface $logger)
    {
        $this->setLogger($logger);
        $this->registerShutdownFunction();
    }

    /**
     * Set the logger.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Report or log an exception.
     *
     * Note that this method doesn't decorate, but overwrite the parent method:
     * @see https://github.com/dmitry-ivanov/laravel-console-logger/pull/11
     *
     * @param \Throwable $e
     * @return void
     */
    public function report(Throwable $e)
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
     *
     * @param \Throwable $e
     * @return void
     */
    private function addSentrySupport(Throwable $e)
    {
        if (app()->bound('sentry') && $this->shouldReport($e)) {
            app('sentry')->captureException($e);
        }
    }

    /**
     * Register the shutdown function.
     *
     * @return void
     */
    private function registerShutdownFunction()
    {
        $this->timeStarted = microtime(true);
        $this->reservedMemory = str_repeat(' ', 20 * 1024);

        register_shutdown_function([$this, 'onShutdown']);
    }

    /**
     * Callback for the shutdown function.
     *
     * @return void
     */
    public function onShutdown()
    {
        $this->reservedMemory = null;

        $this->timeFinished = microtime(true);
        $executionTime = round($this->timeFinished - $this->timeStarted, 3);
        $this->logger->info("Execution time: {$executionTime} sec.");

        $memoryPeak = format_bytes(memory_get_peak_usage(true));
        $this->logger->info("Memory peak usage: {$memoryPeak}.");

        $this->logger->info('%separator%');

        $handlers = (array) $this->logger->getHandlers();
        foreach ($handlers as $handler) {
            $handler->close();
        }
    }
}
