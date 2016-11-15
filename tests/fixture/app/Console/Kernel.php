<?php

class Kernel extends \Orchestra\Testbench\Console\Kernel
{
    protected $commands = [
        GenericCommand::class,
        NamespacedCommand::class,
        CommandWithSeparatorLogging::class,
        CommandWithContextLogging::class,
        CommandWithRecipients::class,
    ];
}
