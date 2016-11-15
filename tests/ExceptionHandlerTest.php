<?php

use Illuminated\Console\ExceptionHandler;
use Illuminated\Console\RuntimeException;
use Psr\Log\LoggerInterface;

class ExceptionHandlerTest extends TestCase
{
    /** @test */
    public function it_logs_an_error_for_all_occurred_application_exceptions()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('error')->with('Test exception', Mockery::subset([
            'code' => 111,
            'message' => 'Test exception',
            'file' => __FILE__,
        ]))->once();

        $handler = new ExceptionHandler($this->app);
        $handler->setLogger($logger);
        $handler->report(new Exception('Test exception', 111));
    }

    /** @test */
    public function it_supports_custom_runtime_exception_which_has_optional_context()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('error')->with('Test exception with context', Mockery::subset([
            'code' => 111,
            'message' => 'Test exception with context',
            'file' => __FILE__,
            'context' => [
                'foo' => 'bar',
                'baz' => 123,
                'faz' => true,
                'daz' => null,
            ]
        ]))->once();

        $handler = new ExceptionHandler($this->app);
        $handler->setLogger($logger);
        $handler->report(new RuntimeException('Test exception with context', [
            'foo' => 'bar',
            'baz' => 123,
            'faz' => true,
            'daz' => null,
        ], 111));
    }
}
