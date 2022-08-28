<?php

namespace Illuminated\Console\Tests\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

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
}
