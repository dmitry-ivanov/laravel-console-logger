<?php

class Kernel extends \Orchestra\Testbench\Console\Kernel
{
    protected $commands = [
        GenericCommand::class,
        NamespacedCommand::class,
        SeparatorCommand::class,
    ];
}
