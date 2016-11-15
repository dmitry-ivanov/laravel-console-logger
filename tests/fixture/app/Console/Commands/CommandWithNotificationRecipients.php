<?php

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class CommandWithNotificationRecipients extends Command
{
    use Loggable;

    protected $signature = 'command-with-notification-recipients';

    protected function getNotificationRecipients()
    {
        return [
            ['address' => 'john.doe@example.com', 'name' => 'John Doe'],
            ['address' => 'jane.smith@example.com', 'name' => 'Jane Smith'],
            ['address' => 'dmitry.g.ivanov@gmail.com', 'name' => 'Dmitry Ivanov'],
        ];
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
