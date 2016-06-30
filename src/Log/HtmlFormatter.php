<?php

namespace Illuminated\Console\Log;

use Monolog\Formatter\HtmlFormatter as MonologHtmlFormatter;

class HtmlFormatter extends MonologHtmlFormatter
{
    public function format(array $record)
    {
        $output = $this->composeStyle($record);
        $output .= $this->composeTitle($record);
        $output .= $this->composeDetails($record);

        return $output;
    }

    protected function composeStyle(array $record)
    {
        $level = $record['level'];

        return "<style>
                    .title, .subtitle {
                        background: {$this->logLevels[$level]};
                        color: #ffffff;
                        margin: 0px;
                        padding: 15px;
                    }
                    .details-row {
                        text-align: left;
                    }
                    .details-row-header {
                        background: #cccccc;
                        width: 150px;
                        padding: 10px;
                    }
                    .details-row-body {
                        background: #eeeeee;
                        padding: 10px;
                    }
                </style>";
    }

    protected function composeTitle(array $record)
    {
        $levelName = e($record['level_name']);
        $title = "<h2 class='title'>{$levelName}</h2>";

        $environment = app()->environment();
        if ($environment == 'production') {
            return $title;
        }

        $environment = e($environment);
        $title .= '<style>.title { padding-bottom: 0px !important; } .subtitle { padding-top: 0px !important; }</style>';
        $title .= "<h3 class='subtitle'>This notification was sent from `{$environment}` environment!</h3>";

        return $title;
    }

    protected function composeDetails(array $record)
    {
        $details = '<table cellspacing="1" width="100%">';
        $details .= $this->composeRow('Message', (string) $record['message']);

        if (!empty($record['context'])) {
            $embeddedTable = '<table cellspacing="1" width="100%">';
            foreach ($record['context'] as $key => $value) {
                $embeddedTable .= $this->composeRow($key, $this->convertToString($value));
            }
            $embeddedTable .= '</table>';
            $details .= $this->composeRow('Context', $embeddedTable, false);
        }

        $datetime = $record['datetime'];
        $time = $datetime->format($this->dateFormat);
        $timezone = $datetime->getTimezone()->getName();
        $details .= $this->composeRow('Time', "{$time} ({$timezone})");
        $details .= $this->composeRow('Environment', app()->environment());
        $details .= '</table>';

        return $details;
    }

    protected function composeRow($header, $body = ' ', $escapeBody = true)
    {
        $header = e($header);
        if ($escapeBody) {
            $body = e($body);
        }

        return "<tr class='details-row'>
                    <th class='details-row-header'>{$header}:</th>
                    <td class='details-row-body'>{$body}</td>
                </tr>";
    }
}
