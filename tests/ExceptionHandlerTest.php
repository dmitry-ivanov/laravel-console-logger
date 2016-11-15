<?php

use Illuminated\Console\ExceptionHandler;
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
}
