<?php

namespace Illuminated\Console\Tests\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class EmailNotificationsInvalidRecipientsCommand extends Command
{
    use Loggable;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'email-notifications-invalid-recipients-command';

    /**
     * Get the email notifications recipients.
     */
    protected function getEmailNotificationsRecipients(): array
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
     */
    public function handle(): void
    {
        $this->logInfo('Done!');
    }
}
