<?php

namespace Illuminated\Console\Log;

use Monolog\Formatter\HtmlFormatter as MonologHtmlFormatter;

class HtmlFormatter extends MonologHtmlFormatter
{
    public function format(array $record)
    {
        $output = $this->composeStyle($record['level']);
        $output .= $this->composeTitle($record['level_name']);

        $environment = app()->environment();
        if ($environment != 'production') {
            $output .= $this->composeSubtitle("This notification was sent from `{$environment}` environment!");
        }

        $output .= '<table cellspacing="1" width="100%" class="monolog-output">';
        $output .= $this->composeRow('Message', (string) $record['message']);

        if (!empty($record['context'])) {
            $embeddedTable = '<table cellspacing="1" width="100%">';
            foreach ($record['context'] as $key => $value) {
                $embeddedTable .= $this->composeRow($key, $this->convertToString($value));
            }
            $embeddedTable .= '</table>';
            $output .= $this->composeRow('Context', $embeddedTable, false);
        }

        $datetime = $record['datetime'];
        $time = $datetime->format($this->dateFormat);
        $timezone = $datetime->getTimezone()->getName();
        $output .= $this->composeRow('Time', "{$time} ({$timezone})");
        $output .= $this->composeRow('Environment', $environment);

        return $output.'</table>';
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

    protected function composeTitle($title)
    {
        $title = e($title);
        return "<h2 class='title'>{$title}</h2>";
    }

    protected function composeSubtitle($subtitle)
    {
        $subtitle = e($subtitle);
        return "<style>.title { padding-bottom: 0px !important; } .subtitle { padding-top: 0px !important; }</style>
                <h3 class='subtitle'>{$subtitle}</h3>";
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
