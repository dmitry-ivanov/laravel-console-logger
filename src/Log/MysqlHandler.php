<?php

namespace Illuminated\Console\Log;

use Illuminate\Support\Facades\DB;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class MysqlHandler extends AbstractProcessingHandler
{
    private $table;

    public function __construct($table = 'iclogger_notifications', $level = Logger::DEBUG, $bubble = true)
    {
        $this->table = $table;
        $this->initialize();

        parent::__construct($level, $bubble);
    }

    protected function initialize()
    {
        DB::statement("create table if not exists `{$this->table}` (
            `id` int(11) unsigned not null auto_increment primary key,
            `level` int(11) unsigned not null,
            `level_name` varchar(255) collate utf8_unicode_ci not null,
            `message` varchar(255) collate utf8_unicode_ci not null,
            `context` text collate utf8_unicode_ci,
            `created_at` timestamp null default null,
            key (`level`),
            key (`level_name`),
            key (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    }

    protected function write(array $record)
    {
        $fields = '(`level`, `level_name`, `message`, `context`, `created_at`)';
        DB::insert("insert into `{$this->table}` {$fields} values (?, ?, ?, ?, now())", [
            $record['level'],
            $record['level_name'],
            $record['message'],
            get_dump($record['context']),
        ]);
    }
}
