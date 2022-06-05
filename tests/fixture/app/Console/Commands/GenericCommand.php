<?php

namespace Illuminated\Console\Tests\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;
use Monolog\Handler\NullHandler;

class GenericCommand extends Command
{
    use Loggable;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'generic';

    /**
     * Handle the command.
     */
    public function handle(): void
    {
        $this->logDebug('Debug!');
        $this->logInfo('Info!');
        $this->logNotice('Notice!');
        $this->logWarning('Warning!');
        $this->logError('Error!');
        $this->logCritical('Critical!');
        $this->logAlert('Alert!');
        $this->logEmergency('Emergency!');
    }

    /**
     * Emulate the closing of the file handler.
     */
    public function emulateFileHandlerClose(): void
    {
        $this->icLogger()->popHandler()->close();
        $this->icLogger()->pushHandler(new NullHandler);
    }
}
