<?php

namespace Illuminated\Console\Loggable\Notifications\DatabaseChannel;

use Monolog\Logger;

trait DatabaseChannel
{
    /**
     * Defines whether to use database notifications or not.
     *
     * @return bool
     */
    protected function useDatabaseNotifications()
    {
        return false;
    }

    /**
     * Get the database channel handler.
     *
     * @return \Illuminated\Console\Loggable\Notifications\DatabaseChannel\MonologDatabaseHandler|false
     */
    protected function getDatabaseChannelHandler()
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
     *
     * @return int
     */
    protected function getDatabaseNotificationsLevel()
    {
        return Logger::NOTICE;
    }

    /**
     * Get the database notifications table name.
     *
     * @return string
     */
    protected function getDatabaseNotificationsTable()
    {
        return 'iclogger_notifications';
    }

    /**
     * Get the database notifications callback.
     *
     * @return callable|null
     */
    protected function getDatabaseNotificationsCallback()
    {
        return null;
    }
}
