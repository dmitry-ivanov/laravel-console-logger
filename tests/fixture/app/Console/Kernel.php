<?php

class Kernel extends \Orchestra\Testbench\Console\Kernel
{
    protected $commands = [
        GenericCommand::class,
        NamespacedCommand::class,
        CommandWithSeparatorLogging::class,
        CommandWithContextLogging::class,
        CommandWithEmailNotifications::class,
        CommandWithDatabaseNotifications::class,
        CommandWithoutDatabaseNotifications::class,
        CommandWithCustomNotificationDbStoring::class,
        CommandWithInvalidNotificationRecipients::class,
        CommandWithNotificationDeduplication::class,
    ];
}
