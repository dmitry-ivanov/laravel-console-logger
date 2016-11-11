<?php

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class NamespacedCommand extends Command
{
    use Loggable;

    protected $signature = 'foo:barbaz';

    public function handle()
    {
        $this->info('Done!');
    }
}
