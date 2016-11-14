<?php

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class GenericCommand extends Command
{
    use Loggable;

    protected $signature = 'generic';

    public function handle()
    {
        $this->logInfo('Done!');
    }

    public function emulateFileHandlerClose()
    {
        $this->icLogger->popHandler()->close();
        $this->icLogger->pushHandler($this->getFileHandler());
    }
}
