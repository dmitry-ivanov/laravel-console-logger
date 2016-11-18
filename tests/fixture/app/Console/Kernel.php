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
        CommandWithDatabaseNotificationsCallback::class,
        CommandWithInvalidEmailNotificationsRecipients::class,
        CommandWithNotificationDeduplication::class,
    ];
}
