<?php

namespace Illuminated\Console\Tests\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class SeparatorLoggingCommand extends Command
{
    use Loggable;

    protected $signature = 'separator-logging-command';

    public function handle()
    {
        $this->logInfo('Testing separator!');
        $this->logInfo('%separator%');
    }
}
