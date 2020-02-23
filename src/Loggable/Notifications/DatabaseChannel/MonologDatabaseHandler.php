<?php

namespace Illuminated\Console\Loggable\Notifications\DatabaseChannel;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class MonologDatabaseHandler extends AbstractProcessingHandler
{
    /**
     * The table name.
     *
     * @var string
     */
    private $table;

    /**
     * The callback.
     *
     * @var callable
     */
    private $callback;

    /**
     * Create a new instance of the handler.
     *
     * @param string $table
     * @param callable|null $callback
     * @param int $level
     * @param bool $bubble
     * @return void
     */
    public function __construct(string $table = 'iclogger_notifications', callable $callback = null, int $level = Logger::DEBUG, bool $bubble = true)
    {
        $this->table = $table;
        $this->callback = $callback;

        $this->guaranteeTableExists();

        parent::__construct($level, $bubble);
    }

    /**
     * Guarantee that the database table for notifications exists.
     *
     * @return void
     */
    protected function guaranteeTableExists()
    {
        if (Schema::hasTable($this->table)) {
            return;
        }

        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('level')->unsigned()->index();
            $table->string('level_name')->index();
            $table->string('message');
            $table->text('context')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });
    }

    /**
     * Write the record down to the database.
     *
     * @param array $record
     * @return void
     */
    protected function write(array $record): void
    {
        if (!empty($this->callback)) {
            call_user_func($this->callback, $record);
            return;
        }

        // We're using Carbon here, because not all database drivers have `now()` function (i.e., `sqlite`)
        $now = Carbon::now();

        DB::table($this->table)->insert([
            'level' => $record['level'],
            'level_name' => $record['level_name'],
            'message' => $record['message'],
            'context' => get_dump($record['context']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
