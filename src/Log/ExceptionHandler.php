<?php

namespace Illuminated\Console\Log;

use App\Exceptions\Handler;
use Exception;

class ExceptionHandler extends Handler
{
    private $timeStarted;
    private $timeFinished;
    protected $reservedMemory;

    public function __construct()
    {
        $this->registerShutdownFunction();

        parent::__construct(app('log.icl'));
    }

    public function report(Exception $e)
    {
        $this->log->error($e->getMessage(), [
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }

    private function registerShutdownFunction()
    {
        $this->timeStarted = microtime(true);
        $this->reservedMemory = str_repeat(' ', 20 * 1024);

        register_shutdown_function(function () {
            $this->reservedMemory = null;

            $this->timeFinished = microtime(true);
            $executionTime = round($this->timeFinished - $this->timeStarted, 3);
            $this->log->info("Execution time: {$executionTime} sec.");

            $memoryPeak = round(memory_get_peak_usage(true) / (1024 * 1024));
            $this->log->info("Memory peak usage: {$memoryPeak}M.");

            $this->log->info('%separator%');

            $handlers = $this->log->getHandlers();
            foreach ($handlers as $handler) {
                $handler->close();
            }
        });
    }
}
