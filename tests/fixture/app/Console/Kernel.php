<?php

class Kernel extends \Orchestra\Testbench\Console\Kernel
{
    protected $commands = [
        GenericCommand::class,
        NamespacedCommand::class,
        CommandWithSeparatorLogging::class,
        CommandWithContextLogging::class,
        CommandWithNotificationRecipients::class,
        CommandWithNotificationDbStoring::class,
        CommandWithoutNotificationDbStoring::class,
        CommandWithCustomNotificationDbStoring::class,
        CommandWithInvalidNotificationRecipients::class,
    ];
}
