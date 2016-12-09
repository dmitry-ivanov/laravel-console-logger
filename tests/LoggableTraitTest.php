<?php

use Illuminated\Console\Exceptions\ExceptionHandler;
use Illuminated\Testing\Asserts\LogFileAsserts;
use Monolog\Handler\RotatingFileHandler;
use Psr\Log\LoggerInterface;

class LoggableTraitTest extends TestCase
{
    use LogFileAsserts;

    /** @test */
    public function it_writes_to_log_file_information_header_each_iteration()
    {
        $class = GenericCommand::class;
        $host = gethostname();
        $ip = gethostbyname($host);

        $this->artisan('generic');

        $this->assertLogFileContains("generic/{$this->date}.log", [
            "[%datetime%]: [INFO]: Command `{$class}` initialized.",
            "[%datetime%]: [INFO]: Host: `{$host}` (`{$ip}`).",
        ]);
    }

    /** @test */
    public function it_does_not_write_mysql_specific_information_for_non_mysql_connections()
    {
        $this->artisan('generic');

        $this->assertLogFileNotContains("generic/{$this->date}.log", [
            'Database host:',
            'Database date:',
        ]);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function it_writes_to_log_file_information_footer_each_iteration()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->with('/Execution time\: .*? sec\./')->once();
        $logger->shouldReceive('info')->with('/Memory peak usage\: .*?\./')->once();
        $logger->shouldReceive('info')->with('%separator%')->once();
        $logger->shouldReceive('getHandlers')->withNoArgs()->once()->andReturn([new RotatingFileHandler('foo')]);

        $handler = new ExceptionHandler($this->app);
        $handler->initialize($logger);
        $handler->onShutdown();
    }

    /** @test */
    public function it_supports_psr3_methods_for_logging()
    {
        $this->artisan('generic');

        $this->assertLogFileContains("generic/{$this->date}.log", [
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

        $this->assertLogFileContains("context-logging-command/{$this->date}.log", [
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
