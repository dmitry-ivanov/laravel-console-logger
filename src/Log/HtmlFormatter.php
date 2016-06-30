<?php

namespace Illuminated\Console\Log;

use Monolog\Formatter\HtmlFormatter as MonologHtmlFormatter;

class HtmlFormatter extends MonologHtmlFormatter
{
    public function format(array $record)
    {
        $output = $this->composeStyle($record['level']);

        $title = $record['level_name'];
        $output .= $this->composeTitle($title);

        $environment = app()->environment();
        if ($environment != 'production') {
            $subtitle = "This notification was sent from `{$environment}` environment!";
            $output .= $this->composeSubtitle($subtitle);
        }

        $output .= '<table cellspacing="1" width="100%" class="monolog-output">';
        $output .= $this->composeRow('Message', (string) $record['message']);
        $output .= $this->composeRow('Time', $record['datetime']->format($this->dateFormat));
        $output .= $this->composeRow('Environment', $environment);
        if ($record['context']) {
            $embeddedTable = '<table cellspacing="1" width="100%">';
            foreach ($record['context'] as $key => $value) {
                $embeddedTable .= $this->composeRow($key, $this->convertToString($value));
            }
            $embeddedTable .= '</table>';
            $output .= $this->composeRow('Context', $embeddedTable, false);
        }

        return $output.'</table>';
    }

    protected function composeStyle($level)
    {
        return "<style>
                    h2.monolog-output, h3.monolog-output {
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
        return "<h2 class='monolog-output'>{$title}</h2>";
    }

    protected function composeSubtitle($subtitle)
    {
        $subtitle = e($subtitle);
        return "<style>
                    h2.monolog-output {
                        padding-bottom: 0px !important;
                    }
                    h3.monolog-output {
                        padding-top: 0px !important;
                    }
                </style>
                <h3 class='monolog-output'>{$subtitle}</h3>";
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
