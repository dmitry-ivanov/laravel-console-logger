<?php

namespace Illuminated\Console\Tests\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class EmailNotificationsCommand extends Command
{
    use Loggable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email-notifications-command';

    /**
     * Get the email notifications recipients.
     *
     * @return array
     */
    protected function getEmailNotificationsRecipients()
    {
        return [
            ['address' => 'john.doe@example.com', 'name' => 'John Doe'],
            ['address' => 'jane.smith@example.com', 'name' => 'Jane Smith'],
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
     * Create the email channel handler.
     *
     * @return \Monolog\Handler\SymfonyMailerHandler|\Monolog\Handler\DeduplicationHandler|false
     */
    public function createEmailChannelHandler()
    {
        return $this->getEmailChannelHandler();
    }

    /**
     * Get the email channel handler.
     *
     * @return \Monolog\Handler\SymfonyMailerHandler|\Monolog\Handler\DeduplicationHandler|false
     */
    public function emailChannelHandler()
    {
        return last($this->icLogger()->getHandlers());
    }
}
