<?php

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class CommandWithNotificationDbStoring extends Command
{
    use Loggable;

    protected $signature = 'command-with-notification-db-storing';

    protected function enableNotificationDbStoring()
    {
        return true;
    }

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
}
