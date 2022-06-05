<?php

namespace Illuminated\Console\Tests\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\SymfonyMailerHandler;

class EmailNotificationsCommand extends Command
{
    use Loggable;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'email-notifications-command';

    /**
     * Get the email notifications recipients.
     */
    protected function getEmailNotificationsRecipients(): array
    {
        return [
            ['address' => 'john.doe@example.com', 'name' => 'John Doe'],
            ['address' => 'jane.smith@example.com', 'name' => 'Jane Smith'],
        ];
    }

    /**
     * Handle the command.
     */
    public function handle(): void
    {
        $this->logInfo('Done!');
    }

    /**
     * Create the email channel handler.
     */
    public function createEmailChannelHandler(): SymfonyMailerHandler|DeduplicationHandler|false
    {
        return $this->getEmailChannelHandler();
    }

    /**
     * Get the email channel handler.
     */
    public function emailChannelHandler(): SymfonyMailerHandler|DeduplicationHandler|false
    {
        return last($this->icLogger()->getHandlers());
    }
}
