<?php

namespace Illuminated\Console\Loggable\Notifications\DatabaseChannel;

use Monolog\Logger;

trait DatabaseChannel
{
    /**
     * Defines whether to use database notifications or not.
     */
    protected function useDatabaseNotifications(): bool
    {
        return false;
    }

    /**
     * Get the database channel handler.
     *
     * @noinspection PhpUnused
     */
    protected function getDatabaseChannelHandler(): MonologDatabaseHandler|false
    {
        if (!$this->useDatabaseNotifications()) {
            return false;
        }

        $table = $this->getDatabaseNotificationsTable();
        $callback = $this->getDatabaseNotificationsCallback();
        $level = $this->getDatabaseNotificationsLevel();

        return new MonologDatabaseHandler($table, $callback, $level);
    }

    /**
     * Get the database notifications level.
     */
    protected function getDatabaseNotificationsLevel(): int
    {
        return Logger::NOTICE;
    }

    /**
     * Get the database notifications table name.
     */
    protected function getDatabaseNotificationsTable(): string
    {
        return 'iclogger_notifications';
    }

    /**
     * Get the database notifications callback.
     */
    protected function getDatabaseNotificationsCallback(): ?callable
    {
        return null;
    }
}
