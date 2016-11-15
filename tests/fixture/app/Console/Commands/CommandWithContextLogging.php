<?php

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class CommandWithContextLogging extends Command
{
    use Loggable;

    protected $signature = 'command-with-context-logging';

    public function handle()
    {
        $this->logInfo('Testing context!');
        $this->logInfo('Some log with data.', [
            'foo' => 'bar',
            'baz' => 111,
            'faz' => true,
            3 => null,
        ]);
    }
}
