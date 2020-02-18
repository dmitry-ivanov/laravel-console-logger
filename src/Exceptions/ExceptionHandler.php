<?php

namespace Illuminated\Console\Exceptions;

use Exception;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Psr\Log\LoggerInterface;

class ExceptionHandler implements ExceptionHandlerContract
{
    private $logger;
    private $timeStarted;
    private $timeFinished;
    protected $reservedMemory;

    /**
     * Holds an instance of the application exception handler.
     *
     * @var \Illuminate\Contracts\Debug\ExceptionHandler
     */
    protected $appExceptionHandler;

    /**
     * Creates a new instance of the ExceptionHandler.
     *
     * @param \Illuminate\Contracts\Debug\ExceptionHandler $appExceptionHandler
     */
    public function __construct(ExceptionHandlerContract $appExceptionHandler)
    {
        $this->appExceptionHandler = $appExceptionHandler;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function initialize(LoggerInterface $logger)
    {
        $this->setLogger($logger);
        $this->registerShutdownFunction();
    }

    public function report(Exception $e)
    {
        $context = [
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        if ($e instanceof RuntimeException) {
            $eContext = $e->getContext();
            if (!empty($eContext)) {
                $context['context'] = $eContext;
            }
        }

        $this->logger->error($e->getMessage(), $context);
        $this->appExceptionHandler->report($e);
    }

    private function registerShutdownFunction()
    {
        $this->timeStarted = microtime(true);
        $this->reservedMemory = str_repeat(' ', 20 * 1024);

        register_shutdown_function([$this, 'onShutdown']);
    }

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

    /**
     * {@inheritdoc}
     */
    public function render($request, Exception $e)
    {
        return $this->appExceptionHandler->render($request, $e);
    }

    /**
     * {@inheritdoc}
     */
    public function renderForConsole($output, Exception $e)
    {
        $this->appExceptionHandler->renderForConsole($output, $e);
    }

    /**
     * Determine if the exception should be reported.
     *
     * @param  \Exception  $e
     * @return bool
     */
    public function shouldReport(Exception $e)
    {
        return $this->appExceptionHandler->shouldReport($e);
    }
}
