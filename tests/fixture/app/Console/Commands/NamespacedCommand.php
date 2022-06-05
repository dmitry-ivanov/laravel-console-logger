<?php

namespace Illuminated\Console\Tests\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class NamespacedCommand extends Command
{
    use Loggable;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'namespaced:command';

    /**
     * Handle the command.
     */
    public function handle(): void
    {
        $this->logInfo('Done!');
    }
}
