<?php

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class CommandWithNotificationDeduplication extends Command
{
    use Loggable;

    protected $signature = 'command-with-notification-deduplication';

    protected function getEmailNotificationRecipients()
    {
        return [
            ['address' => 'john.doe@example.com', 'name' => 'John Doe'],
            ['address' => 'jane.smith@example.com', 'name' => 'Jane Smith'],
        ];
    }

    protected function useEmailNotificationsDeduplication()
    {
        return true;
    }

    public function handle()
    {
    }

    public function mailerHandler()
    {
        return last($this->icLogger()->getHandlers());
    }
}
