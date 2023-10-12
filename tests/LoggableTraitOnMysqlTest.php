<?php

namespace Illuminated\Console\Tests;

use Illuminated\Console\Exceptions\ExceptionHandler;
use Illuminated\Console\Tests\App\Console\Commands\GenericCommand;
use Mockery;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class LoggableTraitOnMysqlTest extends TestCase
{
    /**
     * Set up database.
     */
    protected function setUpDatabase(): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.username' => 'root',
            'database.connections.mysql.password' => 'root',
            'database.connections.mysql.database' => '',
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
        $logger = spy(Logger::class);
        $logger->expects('getHandlers')->andReturn([
            $processingHandler1 = spy(AbstractProcessingHandler::class),
            $processingHandler2 = spy(AbstractProcessingHandler::class),
        ]);

        $handler = app(ExceptionHandler::class);
        $handler->initialize($logger);
        $handler->onShutdown();

        $logger->shouldHaveReceived('info', [Mockery::pattern('/Execution time\: .*? sec\./')]);
        $logger->shouldHaveReceived('info', [Mockery::pattern('/Memory peak usage\: .*?\./')]);
        $logger->shouldHaveReceived('info', ['%separator%']);
        $processingHandler1->shouldHaveReceived('close');
        $processingHandler2->shouldHaveReceived('close');
    }
}
