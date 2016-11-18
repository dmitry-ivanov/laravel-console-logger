<?php

namespace Illuminated\Console\Loggable\Notifications\DatabaseChannel;

use Monolog\Logger;

trait DatabaseChannel
{
    protected function getDatabaseNotificationsLevel()
    {
        return Logger::NOTICE;
    }

    protected function getDatabaseChannelHandler()
    {
        if (!$this->enableNotificationDbStoring()) {
            return false;
        }

        $table = $this->getNotificationDbTable();
        $callback = $this->getNotificationDbCallback();
        $level = $this->getDatabaseNotificationsLevel();

        return (new MonologDatabaseHandler($table, $callback, $level));
    }

    protected function enableNotificationDbStoring()
    {
        return false;
    }

    protected function getNotificationDbTable()
    {
        return 'iclogger_notifications';
    }

    protected function getNotificationDbCallback()
    {
        return null;
    }
}
