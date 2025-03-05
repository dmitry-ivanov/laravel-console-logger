<?php

namespace Illuminated\Console\Tests\Exceptions;

use Exception;
use Illuminated\Console\Exceptions\ExceptionHandler;
use Illuminated\Console\Exceptions\RuntimeException;
use Illuminated\Console\Tests\TestCase;
use Mockery;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\Test;

class ExceptionHandlerTest extends TestCase
{
    #[Test]
    public function it_logs_an_error_for_all_occurred_application_notices_warnings_errors_and_exceptions(): void
    {
        $logger = spy(Logger::class);

        $handler = app(ExceptionHandler::class);
        $handler->setLogger($logger);
        $handler->report(new Exception('Test exception', 111));

        $logger->shouldHaveReceived('error', [
            'Test exception',
            Mockery::subset([
                'code' => 111,
                'message' => 'Test exception',
                'file' => __FILE__,
            ]),
        ]);
    }

    #[Test]
    public function it_supports_sentry(): void
    {
        app()->instance('sentry', $sentry = spy());
        $exception = new Exception('Test exception', 111);

        $handler = app(ExceptionHandler::class);
        $handler->setLogger(spy(Logger::class));
        $handler->report($exception);

        $sentry->shouldHaveReceived('captureException', [$exception]);
    }

    #[Test]
    public function it_supports_custom_runtime_exception_which_has_ability_to_set_optional_context(): void
    {
        $logger = spy(Logger::class);

        $handler = app(ExceptionHandler::class);
        $handler->setLogger($logger);
        $handler->report(new RuntimeException('Test exception with context', [
            'foo' => 'bar',
            'baz' => 123,
            'faz' => true,
            'daz' => null,
        ], 111));

        $logger->shouldHaveReceived('error', [
            'Test exception with context',
            Mockery::subset([
                'code' => 111,
                'message' => 'Test exception with context',
                'file' => __FILE__,
                'context' => [
                    'foo' => 'bar',
                    'baz' => 123,
                    'faz' => true,
                    'daz' => null,
                ],
            ]),
        ]);
    }
}
