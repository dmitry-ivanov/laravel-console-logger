<?php

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class CommandWithCustomNotificationDbStoring extends Command
{
    use Loggable;

    protected $signature = 'command-with-custom-notification-db-storing';

    protected function useDatabaseNotifications()
    {
        return true;
    }

    protected function getDatabaseNotificationsTable()
    {
        return 'my_custom_notifications';
    }

    protected function getNotificationDbCallback()
    {
        return function (array $record) {
            MyCustomNotification::create([
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

    public function handle()
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
