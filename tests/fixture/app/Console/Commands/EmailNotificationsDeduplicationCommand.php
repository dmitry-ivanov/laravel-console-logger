<?php

namespace Illuminated\Console\Tests\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class EmailNotificationsDeduplicationCommand extends Command
{
    use Loggable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email-notifications-deduplication-command';

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
     * Defines whether to use email notifications deduplication or not.
     *
     * @return bool
     */
    protected function useEmailNotificationsDeduplication()
    {
        return true;
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
     * @return \Monolog\Handler\SymfonyMailerHandler|\Monolog\Handler\DeduplicationHandler|false
     */
    public function emailChannelHandler()
    {
        return last($this->icLogger()->getHandlers());
    }
}
