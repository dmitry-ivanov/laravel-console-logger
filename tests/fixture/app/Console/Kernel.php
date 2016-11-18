<?php

class Kernel extends \Orchestra\Testbench\Console\Kernel
{
    protected $commands = [
        GenericCommand::class,
        NamespacedCommand::class,
        ContextLoggingCommand::class,
        SeparatorLoggingCommand::class,
        CommandWithEmailNotifications::class,
        CommandWithDatabaseNotifications::class,
        CommandWithoutDatabaseNotifications::class,
        CommandWithDatabaseNotificationsCallback::class,
        CommandWithInvalidEmailNotificationsRecipients::class,
        CommandWithEmailNotificationsDeduplication::class,
    ];
}
