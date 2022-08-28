<?php

namespace Illuminated\Console\Tests\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class GenericCommand extends Command
{
    use Loggable;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'generic';

    /**
     * Handle the command.
     */
    public function handle(): void
    {
        $this->logDebug('Debug!');
        $this->logInfo('Info!');
        $this->logNotice('Notice!');
        $this->logWarning('Warning!');
        $this->logError('Error!');
        $this->logCritical('Critical!');
        $this->logAlert('Alert!');
        $this->logEmergency('Emergency!');
    }
}
