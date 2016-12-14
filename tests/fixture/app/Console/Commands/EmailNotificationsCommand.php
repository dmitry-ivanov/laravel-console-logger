<?php

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class EmailNotificationsCommand extends Command
{
    use Loggable;

    protected $signature = 'email-notifications-command';

    protected function getEmailNotificationsRecipients()
    {
        return [
            ['address' => 'john.doe@example.com', 'name' => 'John Doe'],
            ['address' => 'jane.smith@example.com', 'name' => 'Jane Smith'],
        ];
    }

    public function handle()
    {
        $this->logInfo('Done!');
    }

    public function createEmailChannelHandler()
    {
        return $this->getEmailChannelHandler();
    }

    public function mailerHandler()
    {
        return last($this->icLogger()->getHandlers());
    }
}
