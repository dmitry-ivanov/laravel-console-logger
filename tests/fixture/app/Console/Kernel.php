<?php

class Kernel extends \Orchestra\Testbench\Console\Kernel
{
    protected $commands = [
        GenericCommand::class,
        NamespacedCommand::class,
        ContextLoggingCommand::class,
        SeparatorLoggingCommand::class,
        EmailNotificationsCommand::class,
        EmailNotificationsDeduplicationCommand::class,
        EmailNotificationsInvalidRecipientsCommand::class,
        CommandWithDatabaseNotifications::class,
        CommandWithoutDatabaseNotifications::class,
        CommandWithDatabaseNotificationsCallback::class,
    ];
}
