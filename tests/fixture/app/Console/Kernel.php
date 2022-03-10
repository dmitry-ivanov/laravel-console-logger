<?php

namespace Illuminated\Console\Tests\App\Console;

use Illuminated\Console\Tests\App\Console\Commands\ContextLoggingCommand;
use Illuminated\Console\Tests\App\Console\Commands\DatabaseNotificationsCallbackCommand;
use Illuminated\Console\Tests\App\Console\Commands\DatabaseNotificationsCommand;
use Illuminated\Console\Tests\App\Console\Commands\DatabaseNotificationsDisabledCommand;
use Illuminated\Console\Tests\App\Console\Commands\EmailNotificationsCommand;
use Illuminated\Console\Tests\App\Console\Commands\EmailNotificationsDeduplicationCommand;
use Illuminated\Console\Tests\App\Console\Commands\EmailNotificationsInvalidRecipientsCommand;
use Illuminated\Console\Tests\App\Console\Commands\GenericCommand;
use Illuminated\Console\Tests\App\Console\Commands\NamespacedCommand;
use Illuminated\Console\Tests\App\Console\Commands\SeparatorLoggingCommand;

class Kernel extends \Orchestra\Testbench\Foundation\Console\Kernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        GenericCommand::class,
        NamespacedCommand::class,
        ContextLoggingCommand::class,
        SeparatorLoggingCommand::class,
        EmailNotificationsCommand::class,
        EmailNotificationsDeduplicationCommand::class,
        EmailNotificationsInvalidRecipientsCommand::class,
        DatabaseNotificationsCommand::class,
        DatabaseNotificationsCallbackCommand::class,
        DatabaseNotificationsDisabledCommand::class,
    ];
}
