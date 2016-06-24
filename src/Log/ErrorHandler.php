<?php

namespace Illuminated\Console\Log;

use Monolog\ErrorHandler as MonologErrorHandler;

class ErrorHandler extends MonologErrorHandler
{
    public static function registerIcl()
    {
        $handler = new static(app('log.icl'));
        $handler->registerFatalHandler();

        return $handler;
    }

    public function handleFatalError()
    {
        $logger = app('log.icl');

        $logger->info('Execution time: trash.');
        $logger->info('Memory peak usage: trash.');

        foreach ($logger->getHandlers() as $handler) {
            $handler->close();
        }
    }
}
