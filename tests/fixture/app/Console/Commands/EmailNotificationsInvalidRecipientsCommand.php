<?php

namespace Illuminated\Console\Tests\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class EmailNotificationsInvalidRecipientsCommand extends Command
{
    use Loggable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email-notifications-invalid-recipients-command';

    /**
     * Get the email notifications recipients.
     *
     * @return array
     */
    protected function getEmailNotificationsRecipients()
    {
        return [
            ['address' => 'not_an_email', 'name' => 'John Doe'],
            ['address' => false, 'name' => 'John Doe'],
            ['address' => null, 'name' => 'Jane Smith'],
            ['name' => 'Jane Smith'],
        ];
    }

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->logInfo('Done!');
    }

    /**
     * Get the email channel handler.
     *
     * @return \Monolog\Handler\NativeMailerHandler|\Monolog\Handler\SwiftMailerHandler|\Monolog\Handler\DeduplicationHandler|false
     */
    public function emailChannelHandler()
    {
        return last($this->icLogger()->getHandlers());
    }
}
