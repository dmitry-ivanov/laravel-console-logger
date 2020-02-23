<?php

namespace Illuminated\Console\Tests\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class SeparatorLoggingCommand extends Command
{
    use Loggable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'separator-logging-command';

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->logInfo('Testing separator!');
        $this->logInfo('%separator%');
    }
}
