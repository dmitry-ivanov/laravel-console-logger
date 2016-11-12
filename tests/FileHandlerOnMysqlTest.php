<?php

class FileHandlerOnMysqlTest extends TestCase
{
    protected function setUpDatabase()
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => '',
            'database.connections.mysql.username' => 'travis',
        ]);
    }

    /** @test */
    public function it_writes_to_log_file_mysql_specific_information_header()
    {
        $dbIp = (string) db_mysql_variable('wsrep_node_address');
        $dbHost = (string) db_mysql_variable('hostname');
        $dbPort = (string) db_mysql_variable('port');
        $now = db_mysql_now();

        Artisan::call('generic');

        $this->assertLogFileContains("generic/{$this->date}.log", [
            "[%datetime%]: [INFO]: Database host: `{$dbHost}`, port: `{$dbPort}`, ip: `{$dbIp}`.",
            "[%datetime%]: [INFO]: Database date: `{$now}`",
        ]);
    }
}
