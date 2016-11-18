<?php

class Kernel extends \Orchestra\Testbench\Console\Kernel
{
    protected $commands = [
        GenericCommand::class,
        NamespacedCommand::class,
        CommandWithSeparatorLogging::class,
        CommandWithContextLogging::class,
        CommandWithEmailNotifications::class,
        CommandWithNotificationDbStoring::class,
        CommandWithoutNotificationDbStoring::class,
        CommandWithCustomNotificationDbStoring::class,
        CommandWithInvalidNotificationRecipients::class,
        CommandWithNotificationDeduplication::class,
    ];
}
