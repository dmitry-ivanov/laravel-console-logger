<?php

namespace Illuminated\Console\Tests\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;
use Monolog\Handler\NullHandler;

class GenericCommand extends Command
{
    use Loggable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generic';

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle()
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

    /**
     * Emulate the closing of the file handler.
     *
     * @return void
     */
    public function emulateFileHandlerClose()
    {
        $this->icLogger()->popHandler()->close();
        $this->icLogger()->pushHandler(new NullHandler);
    }
}
