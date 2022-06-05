<?php

namespace Illuminated\Console\Tests\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class SeparatorLoggingCommand extends Command
{
    use Loggable;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'separator-logging-command';

    /**
     * Handle the command.
     */
    public function handle(): void
    {
        $this->logInfo('Testing separator!');
        $this->logInfo('%separator%');
    }
}
