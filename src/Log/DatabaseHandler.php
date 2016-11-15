<?php

namespace Illuminated\Console\Log;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class DatabaseHandler extends AbstractProcessingHandler
{
    private $table;
    private $callback;

    public function __construct($table = 'iclogger_notifications', callable $callback = null, $level = Logger::DEBUG, $bubble = true)
    {
        $this->table = $table;
        $this->callback = $callback;
        $this->initialize();

        parent::__construct($level, $bubble);
    }

    protected function initialize()
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

    protected function write(array $record)
    {
        if (!empty($this->callback)) {
            return call_user_func($this->callback, $record);
        }

        $fields = '(`level`, `level_name`, `message`, `context`, `created_at`)';
        DB::insert("insert into `{$this->table}` {$fields} values (?, ?, ?, ?, ?)", [
            $record['level'],
            $record['level_name'],
            $record['message'],
            get_dump($record['context']),
            Carbon::now(),
        ]);
    }
}
