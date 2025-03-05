<?php

namespace Illuminated\Console\Tests\Loggable\Notifications\EmailChannel;

use DateTimeImmutable;
use Illuminated\Console\Loggable\Notifications\EmailChannel\MonologHtmlFormatter;
use Illuminated\Console\Tests\TestCase;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\Test;

class MonologHtmlFormatterTest extends TestCase
{
    #[Test]
    public function it_properly_formats_debug_records(): void
    {
        $record = $this->generateRecord('Debug!', Level::Debug);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    #[Test]
    public function it_properly_formats_info_records(): void
    {
        $record = $this->generateRecord('Info!', Level::Info);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    #[Test]
    public function it_properly_formats_notice_records(): void
    {
        $record = $this->generateRecord('Notice!', Level::Notice);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    #[Test]
    public function it_properly_formats_warning_records(): void
    {
        $record = $this->generateRecord('Warning!', Level::Warning);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    #[Test]
    public function it_properly_formats_error_records(): void
    {
        $record = $this->generateRecord('Error!', Level::Error);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    #[Test]
    public function it_properly_formats_critical_records(): void
    {
        $record = $this->generateRecord('Critical!', Level::Critical);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    #[Test]
    public function it_properly_formats_alert_records(): void
    {
        $record = $this->generateRecord('Alert!', Level::Alert);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    #[Test]
    public function it_properly_formats_emergency_records(): void
    {
        $record = $this->generateRecord('Emergency!', Level::Emergency);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    #[Test]
    public function it_properly_formats_records_with_array_context(): void
    {
        $record = $this->generateRecord('Record with array context!', Level::Warning, [
            'foo' => 'bar',
            'baz' => 123,
            'faz' => true,
            'daz' => null,
        ]);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    #[Test]
    public function it_has_no_environment_subtitle_for_production(): void
    {
        $this->emulateProduction();
        $record = $this->generateRecord('Notice!', Level::Notice);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    /**
     * Generate the record.
     */
    protected function generateRecord(string $message, Level $level, array $context = []): LogRecord
    {
        return new LogRecord(new DateTimeImmutable('2016-11-11 11:12:13'), 'ICLogger', $level, $message, $context, []);
    }

    /**
     * Assert that formatter generates expected output.
     */
    protected function assertFormatterGeneratesExpectedOutput(LogRecord $record): void
    {
        $expected = $this->composeExpectedOutput($record);
        $actual = (new MonologHtmlFormatter)->format($record);

        $this->assertEquals(
            $this->normalizeOutput($expected),
            $this->normalizeOutput($actual),
            'Generated html formatter output is not expected.'
        );
    }

    /**
     * Normalize the given output.
     */
    private function normalizeOutput(string $output): string
    {
        return preg_replace('/\s+/m', '', $output);
    }

    /**
     * Compose expected output by the given record.
     */
    private function composeExpectedOutput(LogRecord $record): string
    {
        $levelName = $record->level->getName();
        $color = (new MonologHtmlFormatter)->getLevelColor($record->level);

        $subtitle =
            "<style>.title { padding-bottom: 0 !important; } .subtitle { padding-top: 0 !important; }</style>
            <h3 class='subtitle {$levelName}'>This notification has been sent from the `TESTING` environment!</h3>";
        if ($this->app->environment('production')) {
            $subtitle = '';
        }

        $context = '';
        if (!empty($record->context)) {
            $dump = get_dump($record->context);
            $dump = e($dump);
            $dump = str_replace(' ', '&nbsp;', $dump);
            $dump = nl2br($dump);

            $context = "<tr class='details-row'>
                <th class='details-row-header'>Context:</th>
                <td class='details-row-body'>{$dump}</td>
            </tr>";
        }

        /** @noinspection HtmlRequiredTitleElement */
        return "<!DOCTYPE html>
            <html lang=\"en\">
                <head>
                    <meta charset=\"utf-8\">
                    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
                    <style>
                        body {
                            font-family: 'Lato', sans-serif;
                            font-size: 16px;
                        }
                        .title, .subtitle {
                            color: #ffffff;
                            margin: 0;
                            padding: 15px;
                        }
                        .title.{$levelName}, .subtitle.{$levelName} {
                            background: {$color};
                        }
                        .details-row {
                            text-align: left;
                            font-size: 16px;
                        }
                        .details-row-header {
                            background: #cccccc;
                            width: 150px;
                            padding: 10px;
                        }
                        .details-row-body {
                            background: #eeeeee;
                            white-space: nowrap;
                            width: 100%;
                            padding: 10px;
                        }
                    </style>
                </head>
                <body>
                    <h2 class='title {$levelName}'>{$levelName}</h2>
                    {$subtitle}
                    <table cellspacing=\"1\" width=\"100%\">
                        <tr class='details-row'>
                            <th class='details-row-header'>Message:</th>
                            <td class='details-row-body'>{$record->message}</td>
                        </tr>
                        {$context}
                        <tr class='details-row'>
                            <th class='details-row-header'>Time:</th>
                            <td class='details-row-body'>{$record->datetime->format('Y-m-d H:i:s')}</td>
                        </tr>
                    </table>
                </body>
            </html>";
    }
}
