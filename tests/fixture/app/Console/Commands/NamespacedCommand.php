<?php

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class NamespacedCommand extends Command
{
    use Loggable;

    protected $signature = 'namespaced:command';

    public function handle()
    {
        $this->info('Done!');
    }
}
