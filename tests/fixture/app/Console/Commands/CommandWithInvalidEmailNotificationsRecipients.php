<?php

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class CommandWithInvalidEmailNotificationsRecipients extends Command
{
    use Loggable;

    protected $signature = 'command-with-invalid-email-notifications-recipients';

    protected function getEmailNotificationsRecipients()
    {
        return [
            ['address' => 'not_an_email', 'name' => 'John Doe'],
            ['address' => false, 'name' => 'John Doe'],
            ['address' => null, 'name' => 'Jane Smith'],
            ['name' => 'Jane Smith'],
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
