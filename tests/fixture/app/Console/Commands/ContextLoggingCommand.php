<?php

namespace Illuminated\Console\Tests\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class ContextLoggingCommand extends Command
{
    use Loggable;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'context-logging-command';

    /**
     * Handle the command.
     */
    public function handle(): void
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
