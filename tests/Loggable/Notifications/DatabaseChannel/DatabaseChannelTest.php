<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Monolog\Logger;

class DatabaseChannelTest extends TestCase
{
    /** @test */
    public function it_is_not_storing_notifications_to_database_if_it_is_disabled()
    {
        $this->artisan('database-notifications-disabled-command');

        $this->assertFalse(Schema::hasTable('iclogger_notifications'));
    }

    /** @test */
    public function it_stores_notifications_to_database_if_it_is_enabled_and_also_according_to_notifications_level()
    {
        $this->artisan('database-notifications-command');

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

    /** @test */
    public function it_provides_an_ability_to_use_custom_database_table_and_callback_for_database_notifications()
    {
        Schema::create('custom_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('level')->unsigned()->index();
            $table->string('level_name')->index();
            $table->string('message');
            $table->text('context')->nullable();
            $table->string('custom-field-1');
            $table->string('custom-field-2');
            $table->string('custom-field-foo');
            $table->timestamps();
            $table->index('created_at');
        });

        $this->artisan('database-notifications-callback-command');

        $this->notSeeInDatabaseMany('custom_notifications', [
            ['level' => Logger::DEBUG],
            ['level' => Logger::INFO],
        ]);
        $this->seeInDatabaseMany('custom_notifications', [
            [
                'level' => Logger::NOTICE,
                'level_name' => Logger::getLevelName(Logger::NOTICE),
                'message' => 'Notice!',
                'context' => get_dump(['foo' => 'bar']),
                'custom-field-1' => 'some-additional-data',
                'custom-field-2' => 'more-additional-data',
                'custom-field-foo' => 'bar',
            ], [
                'level' => Logger::WARNING,
                'level_name' => Logger::getLevelName(Logger::WARNING),
                'message' => 'Warning!',
                'context' => get_dump(['foo' => 'bar']),
                'custom-field-1' => 'some-additional-data',
                'custom-field-2' => 'more-additional-data',
                'custom-field-foo' => 'bar',
            ], [
                'level' => Logger::ERROR,
                'level_name' => Logger::getLevelName(Logger::ERROR),
                'message' => 'Error!',
                'context' => get_dump(['foo' => 'bar']),
                'custom-field-1' => 'some-additional-data',
                'custom-field-2' => 'more-additional-data',
                'custom-field-foo' => 'bar',
            ], [
                'level' => Logger::CRITICAL,
                'level_name' => Logger::getLevelName(Logger::CRITICAL),
                'message' => 'Critical!',
                'context' => get_dump(['foo' => 'bar']),
                'custom-field-1' => 'some-additional-data',
                'custom-field-2' => 'more-additional-data',
                'custom-field-foo' => 'bar',
            ], [
                'level' => Logger::ALERT,
                'level_name' => Logger::getLevelName(Logger::ALERT),
                'message' => 'Alert!',
                'context' => get_dump(['foo' => 'bar']),
                'custom-field-1' => 'some-additional-data',
                'custom-field-2' => 'more-additional-data',
                'custom-field-foo' => 'bar',
            ], [
                'level' => Logger::EMERGENCY,
                'level_name' => Logger::getLevelName(Logger::EMERGENCY),
                'message' => 'Emergency!',
                'context' => get_dump(['foo' => 'bar']),
                'custom-field-1' => 'some-additional-data',
                'custom-field-2' => 'more-additional-data',
                'custom-field-foo' => 'bar',
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
