<?php

namespace Illuminated\Console\Tests\Exceptions;

use Mockery;
use Exception;
use Psr\Log\LoggerInterface;
use Illuminated\Console\Tests\TestCase;
use Illuminated\Console\Exceptions\ExceptionHandler;
use Illuminated\Console\Exceptions\RuntimeException;

class ExceptionHandlerTest extends TestCase
{
    /** @test */
    public function it_logs_an_error_for_all_occurred_application_notices_warnings_errors_and_exceptions()
    {
        $logger = spy(LoggerInterface::class);

        $handler = app(ExceptionHandler::class);
        $handler->setLogger($logger);
        $handler->report(new Exception('Test exception', 111));

        $logger->shouldHaveReceived()->error('Test exception', Mockery::subset([
            'code' => 111,
            'message' => 'Test exception',
            'file' => __FILE__,
        ]));
    }

    /** @test */
    public function it_supports_custom_runtime_exception_which_has_ability_to_set_optional_context()
    {
        $logger = spy(LoggerInterface::class);

        $handler = app(ExceptionHandler::class);
        $handler->setLogger($logger);
        $handler->report(new RuntimeException('Test exception with context', [
            'foo' => 'bar',
            'baz' => 123,
            'faz' => true,
            'daz' => null,
        ], 111));

        $logger->shouldHaveReceived()->error('Test exception with context', Mockery::subset([
            'code' => 111,
            'message' => 'Test exception with context',
            'file' => __FILE__,
            'context' => [
                'foo' => 'bar',
                'baz' => 123,
                'faz' => true,
                'daz' => null,
            ],
        ]));
    }
}
