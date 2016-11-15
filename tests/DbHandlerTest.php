<?php

use Monolog\Logger;

class DbHandlerTest extends TestCase
{
    /** @test */
    public function it_stores_notifications_to_database_if_enabled_and_according_to_notifications_level()
    {
        Artisan::call('command-with-notification-db-storing');

        $this->notSeeInDatabaseMany('iclogger_notifications', [
            ['level' => Logger::DEBUG],
            ['level' => Logger::INFO],
        ]);
        $this->seeInDatabaseMany('iclogger_notifications', [
            [
                'level' => Logger::NOTICE,
                'level_name' => Logger::getLevelName(Logger::NOTICE),
                'message' => 'Notice!',
                'context' => get_dump(['foo' => 'bar']),
            ], [
                'level' => Logger::WARNING,
                'level_name' => Logger::getLevelName(Logger::WARNING),
                'message' => 'Warning!',
                'context' => get_dump(['foo' => 'bar']),
            ], [
                'level' => Logger::ERROR,
                'level_name' => Logger::getLevelName(Logger::ERROR),
                'message' => 'Error!',
                'context' => get_dump(['foo' => 'bar']),
            ], [
                'level' => Logger::CRITICAL,
                'level_name' => Logger::getLevelName(Logger::CRITICAL),
                'message' => 'Critical!',
                'context' => get_dump(['foo' => 'bar']),
            ], [
                'level' => Logger::ALERT,
                'level_name' => Logger::getLevelName(Logger::ALERT),
                'message' => 'Alert!',
                'context' => get_dump(['foo' => 'bar']),
            ], [
                'level' => Logger::EMERGENCY,
                'level_name' => Logger::getLevelName(Logger::EMERGENCY),
                'message' => 'Emergency!',
                'context' => get_dump(['foo' => 'bar']),
            ],
        ]);
    }

    protected function seeInDatabaseMany($table, $rows)
    {
        foreach ($rows as $row) {
            $this->seeInDatabase($table, $row);
        }
    }

    protected function notSeeInDatabaseMany($table, $rows)
    {
        foreach ($rows as $row) {
            $this->notSeeInDatabase($table, $row);
        }
    }
}
