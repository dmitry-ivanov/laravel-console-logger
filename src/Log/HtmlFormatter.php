<?php

namespace Illuminated\Console\Log;

use Monolog\Formatter\HtmlFormatter as MonologHtmlFormatter;

class HtmlFormatter extends MonologHtmlFormatter
{
    public function format(array $record)
    {
        $output = $this->composeStyle($record['level']);
        $output .= $this->composeTitle($record['level_name']);
        $output .= $this->composeDetails($record);

        return $output;
    }

    protected function composeStyle($level)
    {
        return "<style>
                    .title, .subtitle {
                        background: {$this->logLevels[$level]};
                        color: #ffffff;
                        margin: 0px;
                        padding: 15px;
                    }
                </style>";
    }

    protected function composeTitle($value)
    {
        $title = "<h2 class='title'>{e($value)}</h2>";

        $environment = app()->environment();
        if ($environment == 'production') {
            return $title;
        }

        $title .= '<style>.title { padding-bottom: 0px !important; } .subtitle { padding-top: 0px !important; }</style>';
        $title .= "<h3 class='subtitle'>This notification was sent from `{e($environment)}` environment!</h3>";

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

    protected function composeRow($th, $td = ' ', $escapeTd = true)
    {
        $th = e($th);
        if ($escapeTd) {
            $td = '<pre>' . e($td) . '</pre>';
        }

        return "<tr style='padding:4px; spacing:0; text-align:left;'>\n
                    <th style='background:#cccccc' width='100px'>{$th}:</th>\n
                    <td style='padding:4px; spacing:0; text-align:left; background:#eeeeee'>{$td}</td>\n
                </tr>";
    }
}
