<?php

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class SeparatorCommand extends Command
{
    use Loggable;

    protected $signature = 'separator';

    public function handle()
    {
        $this->logInfo('Testing separator!');
        $this->logInfo('%separator%');
    }
}
