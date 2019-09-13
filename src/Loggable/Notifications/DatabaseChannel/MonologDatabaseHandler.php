<?php

namespace Illuminated\Console\Loggable\Notifications\DatabaseChannel;

use Carbon\Carbon;
use Monolog\Logger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Monolog\Handler\AbstractProcessingHandler;

class MonologDatabaseHandler extends AbstractProcessingHandler
{
    private $table;
    private $callback;

    public function __construct($table = 'iclogger_notifications', callable $callback = null, $level = Logger::DEBUG, $bubble = true)
    {
        $this->table = $table;
        $this->callback = $callback;

        $this->guaranteeTableExists();

        parent::__construct($level, $bubble);
    }

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

    protected function write(array $record): void
    {
        if (!empty($this->callback)) {
            call_user_func($this->callback, $record);
            return;
        }

        $now = Carbon::now();
        $fields = '(`level`, `level_name`, `message`, `context`, `created_at`, `updated_at`)';
        DB::insert("insert into `{$this->table}` {$fields} values (?, ?, ?, ?, ?, ?)", [
            $record['level'],
            $record['level_name'],
            $record['message'],
            get_dump($record['context']),
            $now,
            $now,
        ]);
    }
}
