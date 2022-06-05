<?php

namespace Illuminated\Console\Tests\Loggable\Notifications\EmailChannel;

use DateTime;
use Illuminated\Console\Loggable\Notifications\EmailChannel\MonologHtmlFormatter;
use Illuminated\Console\Tests\TestCase;
use Monolog\Logger;

class MonologHtmlFormatterTest extends TestCase
{
    /** @test */
    public function it_properly_formats_debug_records()
    {
        $record = $this->generateRecord('Debug!', Logger::DEBUG);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    /** @test */
    public function it_properly_formats_info_records()
    {
        $record = $this->generateRecord('Info!', Logger::INFO);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    /** @test */
    public function it_properly_formats_notice_records()
    {
        $record = $this->generateRecord('Notice!', Logger::NOTICE);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    /** @test */
    public function it_properly_formats_warning_records()
    {
        $record = $this->generateRecord('Warning!', Logger::WARNING);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    /** @test */
    public function it_properly_formats_error_records()
    {
        $record = $this->generateRecord('Error!', Logger::ERROR);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    /** @test */
    public function it_properly_formats_critical_records()
    {
        $record = $this->generateRecord('Critical!', Logger::CRITICAL);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    /** @test */
    public function it_properly_formats_alert_records()
    {
        $record = $this->generateRecord('Alert!', Logger::ALERT);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    /** @test */
    public function it_properly_formats_emergency_records()
    {
        $record = $this->generateRecord('Emergency!', Logger::EMERGENCY);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    /** @test */
    public function it_properly_formats_records_with_array_context()
    {
        $record = $this->generateRecord('Record with array context!', Logger::WARNING, [
            'foo' => 'bar',
            'baz' => 123,
            'faz' => true,
            'daz' => null,
        ]);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    /** @test */
    public function it_properly_formats_records_with_non_array_context()
    {
        $record = $this->generateRecord('Record with non array context!', Logger::WARNING, 'Non array context');

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    /** @test */
    public function it_has_no_environment_subtitle_for_production()
    {
        $this->emulateProduction();
        $record = $this->generateRecord('Notice!', Logger::NOTICE);

        $this->assertFormatterGeneratesExpectedOutput($record);
    }

    /**
     * Generate the record.
     */
    protected function generateRecord(string $message, int $level, array|string $context = []): array
    {
        return [
            'message' => $message,
            'context' => $context,
            'level' => $level,
            'level_name' => Logger::getLevelName($level),
            'channel' => 'ICLogger',
            'datetime' => new DateTime('2016-11-11 11:12:13'),
            'extra' => [],
        ];
    }

    /**
     * Assert that formatter generates expected output.
     */
    protected function assertFormatterGeneratesExpectedOutput(array $record): void
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
    private function composeExpectedOutput(array $record): string
    {
        $color = (new MonologHtmlFormatter)->getLevelColor($record['level']);

        $subtitle =
            "<style>.title { padding-bottom: 0 !important; } .subtitle { padding-top: 0 !important; }</style>
            <h3 class='subtitle {$record['level_name']}'>This notification has been sent from the `TESTING` environment!</h3>";
        if ($this->app->environment('production')) {
            $subtitle = '';
        }

        $context = '';
        if (!empty($record['context'])) {
            $dump = is_array($record['context']) ? get_dump($record['context']) : $record['context'];
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
                        .title.{$record['level_name']}, .subtitle.{$record['level_name']} {
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
                    <h2 class='title {$record['level_name']}'>{$record['level_name']}</h2>
                    {$subtitle}
                    <table cellspacing=\"1\" width=\"100%\">
                        <tr class='details-row'>
                            <th class='details-row-header'>Message:</th>
                            <td class='details-row-body'>{$record['message']}</td>
                        </tr>
                        {$context}
                        <tr class='details-row'>
                            <th class='details-row-header'>Time:</th>
                            <td class='details-row-body'>{$record['datetime']->format('Y-m-d H:i:s')}</td>
                        </tr>
                    </table>
                </body>
            </html>";
    }
}
