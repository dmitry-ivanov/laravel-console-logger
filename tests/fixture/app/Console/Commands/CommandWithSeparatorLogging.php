<?php

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class CommandWithSeparatorLogging extends Command
{
    use Loggable;

    protected $signature = 'command-with-separator-logging';

    public function handle()
    {
        $this->logInfo('Testing separator!');
        $this->logInfo('%separator%');
    }
}
