<?php

namespace Illuminated\Console\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler;
use Psr\Log\LoggerInterface;

class ExceptionHandler extends Handler
{
    private $logger;
    private $timeStarted;
    private $timeFinished;
    protected $reservedMemory;

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

        $handlers = $this->logger->getHandlers();
        foreach ($handlers as $handler) {
            $handler->close();
        }
    }
}
