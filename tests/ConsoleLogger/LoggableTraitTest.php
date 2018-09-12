<?php

namespace Illuminated\Console\ConsoleLogger\Tests;

use Mockery;
use GenericCommand;
use Psr\Log\LoggerInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Illuminated\Console\Exceptions\ExceptionHandler;

class LoggableTraitTest extends TestCase
{
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
    public function it_does_not_write_mysql_specific_information_for_non_mysql_connections()
    {
        $this->artisan('generic');

        $this->dontSeeInLogFile("generic/{$this->date}.log", [
            'Database host:',
            'Database date:',
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

    /** @test */
    public function it_supports_psr3_methods_for_logging()
    {
        $this->artisan('generic');

        $this->seeInLogFile("generic/{$this->date}.log", [
            '[%datetime%]: [DEBUG]: Debug!',
            '[%datetime%]: [INFO]: Info!',
            '[%datetime%]: [NOTICE]: Notice!',
            '[%datetime%]: [WARNING]: Warning!',
            '[%datetime%]: [ERROR]: Error!',
            '[%datetime%]: [CRITICAL]: Critical!',
            '[%datetime%]: [ALERT]: Alert!',
            '[%datetime%]: [EMERGENCY]: Emergency!',
        ]);
    }

    /** @test */
    public function psr3_methods_are_supporting_context_and_it_is_logged_as_readable_dump()
    {
        $this->artisan('context-logging-command');

        $this->seeInLogFile("context-logging-command/{$this->date}.log", [
            'Testing context!',
            'Some log with data.',
            get_dump([
                'foo' => 'bar',
                'baz' => 111,
                'faz' => true,
                3 => null,
            ]),
        ]);
    }
}
