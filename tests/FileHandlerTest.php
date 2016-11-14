<?php

use Carbon\Carbon;
use Monolog\Handler\RotatingFileHandler;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class FileHandlerTest extends TestCase
{
    /** @test */
    public function it_creates_log_file_according_to_the_command_name_and_current_date()
    {
        Artisan::call('generic');

        $this->assertLogFileExists("generic/{$this->date}.log");
    }

    /** @test */
    public function it_creates_log_file_in_subfolder_if_command_is_namespaced()
    {
        Artisan::call('foo:barbaz');

        $this->assertLogFileExists("foo/barbaz/{$this->date}.log");
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
    public function it_does_not_write_additional_mysql_information_headers_for_non_mysql_connections()
    {
        Artisan::call('generic');

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
        $logger = Mockery::mock(Psr\Log\LoggerInterface::class);
        $logger->shouldReceive('info')->with('/Execution time\: .*? sec\./')->once();
        $logger->shouldReceive('info')->with('/Memory peak usage\: .*?\./')->once();
        $logger->shouldReceive('info')->with('%separator%')->once();
        $logger->shouldReceive('getHandlers')->withNoArgs()->once()->andReturn([
            new RotatingFileHandler('foo'),
            new RotatingFileHandler('bar'),
            new RotatingFileHandler('baz'),
        ]);

        $handler = new Illuminated\Console\ExceptionHandler($this->app);
        $handler->initialize($logger);
        $handler->onShutdown();
    }

    /** @test */
    public function it_provides_automatic_files_rotation_and_only_30_latest_files_are_stored()
    {
        $path = storage_path('logs/generic');
        $this->createBunchOfOldLogsInCount45($path);
        $this->assertCount(45, File::files($path));

        $command = new GenericCommand;
        $command->setLaravel($this->app);
        $command->run(new ArrayInput([]), new BufferedOutput);
        $command->emulateFileHandlerClose();

        $this->assertCount(30, File::files($path));
    }

    /** @test */
    public function it_supports_psr3_methods_for_logging()
    {
        Artisan::call('generic');

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
    public function it_supports_separator_keyword_in_psr3_methods_which_is_converted_to_11_blank_lines()
    {
        $command = new GenericCommand;
        $command->setLaravel($this->app);
        $command->run(new ArrayInput([]), new BufferedOutput);
        $command->logSeparator();

        $this->assertLogFileContains("generic/{$this->date}.log", str_repeat("\n", 11));
    }

    private function createBunchOfOldLogsInCount45($path)
    {
        if (!File::isDirectory($path)) {
            File::makeDirectory($path);
        }

        $date = Carbon::parse('2016-01-01');
        for ($i = 0; $i < 45; $i++) {
            File::put("{$path}/{$date->toDateString()}.log", 'foo');
            $date->addDay();
        }
    }
}
