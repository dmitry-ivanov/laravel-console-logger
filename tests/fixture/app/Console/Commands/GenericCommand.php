<?php

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class GenericCommand extends Command
{
    use Loggable;

    protected $signature = 'icl:generic';

    public function handle()
    {
        $this->info('Done!');
    }
}
