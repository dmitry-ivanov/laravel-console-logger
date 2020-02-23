<?php

namespace Illuminated\Console\Tests\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class DatabaseNotificationsDisabledCommand extends Command
{
    use Loggable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database-notifications-disabled-command';

    /**
     * Defines whether to use database notifications or not.
     *
     * @return bool
     */
    protected function useDatabaseNotifications()
    {
        return false;
    }

    /**
     * Handle the command.
     *
     * @return void
     */
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
