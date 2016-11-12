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
    public function it_writes_to_log_file_information_header_each_iteration()
    {
        $class = GenericCommand::class;
        $host = gethostname();
        $ip = gethostbyname($host);

        Artisan::call('generic');

        $this->assertLogFileContains("generic/{$this->date}.log", [
            "[%datetime%]: [INFO]: Command `{$class}` initialized.",
            "[%datetime%]: [INFO]: Host: `{$host}` (`{$ip}`).",
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

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function it_writes_to_log_file_information_footer_each_iteration()
    {
        $logger = Mockery::mock(Psr\Log\LoggerInterface::class);
        $logger->shouldReceive('info')->with('/Execution time\: .*? sec\./')->once();
        $logger->shouldReceive('info')->with('/Memory peak usage\: .*?\./')->once();
        $logger->shouldReceive('info')->with('%separator%')->once();
        $logger->shouldReceive('getHandlers')->withNoArgs()->once()->andReturn([]);

        $handler = new Illuminated\Console\ExceptionHandler($this->app);
        $handler->initialize($logger);
        $handler->onShutdown();
    }
}
