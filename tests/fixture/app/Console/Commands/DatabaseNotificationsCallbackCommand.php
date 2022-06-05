<?php

namespace Illuminated\Console\Tests\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;
use Illuminated\Console\Tests\App\CustomNotification;

class DatabaseNotificationsCallbackCommand extends Command
{
    use Loggable;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'database-notifications-callback-command';

    /**
     * Defines whether to use database notifications or not.
     */
    protected function useDatabaseNotifications(): bool
    {
        return true;
    }

    /**
     * Get the database notifications table name.
     */
    protected function getDatabaseNotificationsTable(): string
    {
        return 'custom_notifications';
    }

    /**
     * Get the database notifications callback.
     */
    protected function getDatabaseNotificationsCallback(): ?callable
    {
        return function (array $record) {
            /** @noinspection PhpUndefinedMethodInspection */
            CustomNotification::create([
                'level' => $record['level'],
                'level_name' => $record['level_name'],
                'message' => $record['message'],
                'context' => get_dump($record['context']),
                'custom-field-1' => 'some-additional-data',
                'custom-field-2' => 'more-additional-data',
                'custom-field-foo' => $record['context']['foo'],
            ]);
        };
    }

    /**
     * Handle the command.
     */
    public function handle(): void
    {
        $this->logDebug('Debug!', ['foo' => 'bar']);
        $this->logInfo('Info!', ['foo' => 'bar']);
        $this->logNotice('Notice!', ['foo' => 'bar']);
        $this->logWarning('Warning!', ['foo' => 'bar']);
        $this->logError('Error!', ['foo' => 'bar']);
        $this->logCritical('Critical!', ['foo' => 'bar']);
        $this->logAlert('Alert!', ['foo' => 'bar']);
        $this->logEmergency('Emergency!', ['foo' => 'bar']);
    }
}
