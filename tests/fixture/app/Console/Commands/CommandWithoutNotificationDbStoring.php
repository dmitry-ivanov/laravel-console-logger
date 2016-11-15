<?php

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class CommandWithoutNotificationDbStoring extends Command
{
    use Loggable;

    protected $signature = 'command-without-notification-db-storing';

    protected function enableNotificationDbStoring()
    {
        return false;
    }

    public function handle()
    {
        $this->logDebug('Debug!', ['foo' => 'bar']);
        $this->logInfo('Info!', ['foo' => 'bar']);
        $this->logNotice('Notice!', ['foo' => 'bar']);
        $this->logWarning('Warning!', ['foo' => 'bar']);
        $this->logError('Error!', ['foo' => 'bar']);
        $this->logCritical('Critical!', ['foo' => 'bar']);
        $this->logAlert('Alert!', ['foo' => 'bar']);
        $this->logEmergency('Emergency!', ['foo' => 'bar']);
    }
}
