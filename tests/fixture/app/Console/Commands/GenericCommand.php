<?php

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;
use Monolog\Handler\NullHandler;

class GenericCommand extends Command
{
    use Loggable;

    protected $signature = 'generic';

    public function handle()
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

    public function emulateFileHandlerClose()
    {
        $this->icLogger()->popHandler()->close();
        $this->icLogger()->pushHandler(new NullHandler);
    }
}
