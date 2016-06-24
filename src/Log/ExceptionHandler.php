<?php

namespace Illuminated\Console\Log;

use App\Exceptions\Handler;
use Exception;
use Monolog\ErrorHandler;

class ExceptionHandler extends Handler
{
    public function __construct()
    {
        $log = app('log.icl');
        ErrorHandler::register($log);

        parent::__construct($log);
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
}
