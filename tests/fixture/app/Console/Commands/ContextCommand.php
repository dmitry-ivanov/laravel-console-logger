<?php

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class ContextCommand extends Command
{
    use Loggable;

    protected $signature = 'context';

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
