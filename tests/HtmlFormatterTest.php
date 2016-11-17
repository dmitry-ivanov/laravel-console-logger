<?php

use Illuminated\Console\Log\HtmlFormatter;
use Monolog\Logger;

class HtmlFormatterTest extends TestCase
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

    protected function generateRecord($message, $level, array $context = [])
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

    protected function assertFormatterGeneratesExpectedOutput(array $record)
    {
        $expected = $this->composeExpectedOutput($record);
        $actual = (new HtmlFormatter)->format($record);

        $this->assertEquals(
            $this->normalizeOutput($expected),
            $this->normalizeOutput($actual),
            'Generated html formatter output is not expected.'
        );
    }

    private function normalizeOutput($output)
    {
        return preg_replace('!\s+!smi', '', $output);
    }

    private function composeExpectedOutput(array $record)
    {
        $color = (new HtmlFormatter)->getLevelColor($record['level']);

        return "<!DOCTYPE html>
            <html>
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
                            margin: 0px;
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
                    <style>.title { padding-bottom: 0px !important; } .subtitle { padding-top: 0px !important; }</style>
                    <h3 class='subtitle {$record['level_name']}'>This notification was sent from `TESTING` environment!</h3>
                    <table cellspacing=\"1\" width=\"100%\">
                        <tr class='details-row'>
                            <th class='details-row-header'>Message:</th>
                            <td class='details-row-body'>{$record['message']}</td>
                        </tr>
                        <tr class='details-row'>
                            <th class='details-row-header'>Time:</th>
                            <td class='details-row-body'>{$record['datetime']->format('Y-m-d H:i:s')}</td>
                        </tr>
                    </table>
                </body>
            </html>";
    }
}
