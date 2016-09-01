<?php

namespace Illuminated\Console;

use Exception;
use Illuminate\Foundation\Exceptions\Handler;

class ExceptionHandler extends Handler
{
    private $log;
    private $timeStarted;
    private $timeFinished;
    private static $reservedMemory;

    public function __construct()
    {
        $this->log = app('log.iclogger');
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

        $this->log->error($e->getMessage(), $context);
    }

    private function registerShutdownFunction()
    {
        $this->timeStarted = microtime(true);
        self::$reservedMemory = str_repeat(' ', 20 * 1024);

        register_shutdown_function(function () {
            self::$reservedMemory = null;

            $this->timeFinished = microtime(true);
            $executionTime = round($this->timeFinished - $this->timeStarted, 3);
            $this->log->info("Execution time: {$executionTime} sec.");

            $memoryPeak = format_bytes(memory_get_peak_usage(true));
            $this->log->info("Memory peak usage: {$memoryPeak}.");

            $this->log->info('%separator%');

            $handlers = $this->log->getHandlers();
            foreach ($handlers as $handler) {
                $handler->close();
            }
        });
    }
}
