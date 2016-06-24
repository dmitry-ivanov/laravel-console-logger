<?php

namespace Illuminated\Console\Log;

use App\Exceptions\Handler;
use Exception;

class ExceptionHandler extends Handler
{
    public function __construct()
    {
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
}
