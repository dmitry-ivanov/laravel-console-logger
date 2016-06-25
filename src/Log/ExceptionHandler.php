<?php

namespace Illuminated\Console\Log;

use App\Exceptions\Handler;
use Exception;

class ExceptionHandler extends Handler
{
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
        $this->reservedMemory = str_repeat(' ', 20 * 1024);

        register_shutdown_function(function () {
            $this->reservedMemory = null;

            $this->log->info('Execution time: trash.');
            $this->log->info('Memory peak usage: trash.');

            $handlers = $this->log->getHandlers();
            foreach ($handlers as $handler) {
                $handler->close();
            }
        });
    }
}
