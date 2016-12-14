<?php

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class EmailNotificationsInvalidRecipientsCommand extends Command
{
    use Loggable;

    protected $signature = 'email-notifications-invalid-recipients-command';

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
        $this->logInfo('Done!');
    }

    public function emailChannelHandler()
    {
        return last($this->icLogger()->getHandlers());
    }
}
