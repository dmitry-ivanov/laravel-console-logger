<?php

namespace Illuminated\Console\Tests\Loggable\Notifications\DatabaseChannel;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminated\Console\Tests\TestCase;
use Monolog\Level;

class DatabaseChannelTest extends TestCase
{
    /** @test */
    public function it_is_not_storing_notifications_to_database_if_it_is_disabled()
    {
        $this->artisan('database-notifications-disabled-command');

        $this->assertDatabaseMissingTable('iclogger_notifications');
    }

    /** @test */
    public function it_stores_notifications_to_database_if_it_is_enabled_and_also_according_to_notifications_level()
    {
        $this->artisan('database-notifications-command');

        $this->assertDatabaseMissingMany('iclogger_notifications', [
            ['level' => Level::Debug],
            ['level' => Level::Info],
        ]);
        $this->assertDatabaseHasMany('iclogger_notifications', [
            [
                'level' => Level::Notice,
                'level_name' => Level::Notice->getName(),
                'message' => 'Notice!',
                'context' => get_dump(['foo' => 'bar']),
            ], [
                'level' => Level::Warning,
                'level_name' => Level::Warning->getName(),
                'message' => 'Warning!',
                'context' => get_dump(['foo' => 'bar']),
            ], [
                'level' => Level::Error,
                'level_name' => Level::Error->getName(),
                'message' => 'Error!',
                'context' => get_dump(['foo' => 'bar']),
            ], [
                'level' => Level::Critical,
                'level_name' => Level::Critical->getName(),
                'message' => 'Critical!',
                'context' => get_dump(['foo' => 'bar']),
            ], [
                'level' => Level::Alert,
                'level_name' => Level::Alert->getName(),
                'message' => 'Alert!',
                'context' => get_dump(['foo' => 'bar']),
            ], [
                'level' => Level::Emergency,
                'level_name' => Level::Emergency->getName(),
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

        $this->assertDatabaseMissingMany('custom_notifications', [
            ['level' => Level::Debug],
            ['level' => Level::Info],
        ]);
        $this->assertDatabaseHasMany('custom_notifications', [
            [
                'level' => Level::Notice,
                'level_name' => Level::Notice->getName(),
                'message' => 'Notice!',
                'context' => get_dump(['foo' => 'bar']),
                'custom-field-1' => 'some-additional-data',
                'custom-field-2' => 'more-additional-data',
                'custom-field-foo' => 'bar',
            ], [
                'level' => Level::Warning,
                'level_name' => Level::Warning->getName(),
                'message' => 'Warning!',
                'context' => get_dump(['foo' => 'bar']),
                'custom-field-1' => 'some-additional-data',
                'custom-field-2' => 'more-additional-data',
                'custom-field-foo' => 'bar',
            ], [
                'level' => Level::Error,
                'level_name' => Level::Error->getName(),
                'message' => 'Error!',
                'context' => get_dump(['foo' => 'bar']),
                'custom-field-1' => 'some-additional-data',
                'custom-field-2' => 'more-additional-data',
                'custom-field-foo' => 'bar',
            ], [
                'level' => Level::Critical,
                'level_name' => Level::Critical->getName(),
                'message' => 'Critical!',
                'context' => get_dump(['foo' => 'bar']),
                'custom-field-1' => 'some-additional-data',
                'custom-field-2' => 'more-additional-data',
                'custom-field-foo' => 'bar',
            ], [
                'level' => Level::Alert,
                'level_name' => Level::Alert->getName(),
                'message' => 'Alert!',
                'context' => get_dump(['foo' => 'bar']),
                'custom-field-1' => 'some-additional-data',
                'custom-field-2' => 'more-additional-data',
                'custom-field-foo' => 'bar',
            ], [
                'level' => Level::Emergency,
                'level_name' => Level::Emergency->getName(),
                'message' => 'Emergency!',
                'context' => get_dump(['foo' => 'bar']),
                'custom-field-1' => 'some-additional-data',
                'custom-field-2' => 'more-additional-data',
                'custom-field-foo' => 'bar',
            ],
        ]);
    }
}
