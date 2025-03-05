<?php

namespace Illuminated\Console\Tests;

use Illuminated\Console\Exceptions\ExceptionHandler;
use Illuminated\Console\Tests\App\Console\Commands\GenericCommand;
use Mockery;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;

class LoggableTraitTest extends TestCase
{
    #[Test]
    public function it_writes_to_log_file_information_header_each_iteration(): void
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

    #[Test]
    public function it_does_not_write_mysql_specific_information_for_non_mysql_connections(): void
    {
        $this->artisan('generic');

        $this->dontSeeInLogFile("generic/{$this->date}.log", [
            'Database host:',
            'Database date:',
        ]);
    }

    #[Test] #[RunInSeparateProcess] #[PreserveGlobalState(false)]
    public function it_writes_to_log_file_information_footer_each_iteration_and_close_all_handlers_on_shutdown(): void
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

    #[Test]
    public function it_supports_psr3_methods_for_logging(): void
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

    #[Test]
    public function psr3_methods_are_supporting_context_and_it_is_logged_as_readable_dump(): void
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
