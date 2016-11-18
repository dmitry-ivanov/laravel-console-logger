<?php

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class CommandWithNotificationRecipients extends Command
{
    use Loggable;

    protected $signature = 'command-with-notification-recipients';

    protected function getEmailNotificationsRecipients()
    {
        return [
            ['address' => 'john.doe@example.com', 'name' => 'John Doe'],
            ['address' => 'jane.smith@example.com', 'name' => 'Jane Smith'],
        ];
    }

    public function handle()
    {
    }

    public function mailerHandler()
    {
        return last($this->icLogger()->getHandlers());
    }
}
