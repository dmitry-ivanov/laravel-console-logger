<?php

namespace Illuminated\Console\Tests\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class ContextLoggingCommand extends Command
{
    use Loggable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'context-logging-command';

    /**
     * Handle the command.
     *
     * @return void
     */
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
