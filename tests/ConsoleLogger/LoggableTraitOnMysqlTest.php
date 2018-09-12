<?php

namespace Illuminated\Console\ConsoleLogger\Tests;

use Mockery;
use GenericCommand;
use Psr\Log\LoggerInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Illuminated\Console\Exceptions\ExceptionHandler;

class LoggableTraitOnMysqlTest extends TestCase
{
    protected function setUpDatabase()
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => '127.0.0.1',
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

        $this->artisan('generic');

        $this->seeInLogFile("generic/{$this->date}.log", [
            "[%datetime%]: [INFO]: Command `{$class}` initialized.",
            "[%datetime%]: [INFO]: Host: `{$host}` (`{$ip}`).",
        ]);
    }

    /** @test */
    public function it_writes_to_log_file_mysql_specific_information_after_header()
    {
        $dbIp = (string) db_mysql_variable('wsrep_node_address');
        $dbHost = (string) db_mysql_variable('hostname');
        $dbPort = (string) db_mysql_variable('port');

        $this->artisan('generic');

        $this->seeInLogFile("generic/{$this->date}.log", [
            "[%datetime%]: [INFO]: Database host: `{$dbHost}`, port: `{$dbPort}`, ip: `{$dbIp}`.",
            '[%datetime%]: [INFO]: Database date: `%datetime%`.',
        ]);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function it_writes_to_log_file_information_footer_each_iteration_and_close_all_handlers_on_shutdown()
    {
        $logger = spy(LoggerInterface::class);
        $logger->expects()->getHandlers()->andReturn([
            $processingHandler1 = spy(AbstractProcessingHandler::class),
            $processingHandler2 = spy(AbstractProcessingHandler::class),
        ]);

        $handler = app(ExceptionHandler::class);
        $handler->initialize($logger);
        $handler->onShutdown();

        $logger->shouldHaveReceived()->info(Mockery::pattern('/Execution time\: .*? sec\./'));
        $logger->shouldHaveReceived()->info(Mockery::pattern('/Memory peak usage\: .*?\./'));
        $logger->shouldHaveReceived()->info('%separator%');
        $processingHandler1->shouldHaveReceived()->close();
        $processingHandler2->shouldHaveReceived()->close();
    }
}
