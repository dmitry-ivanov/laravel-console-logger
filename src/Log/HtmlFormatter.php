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
                    h1.monolog-output, h3.monolog-output {
                        margin: 0px;
                        background: {$this->logLevels[$level]};
                        color: #ffffff;
                        padding: 11px;
                    }
                </style>";
    }

    protected function composeTitle($title)
    {
        $title = htmlspecialchars($title, ENT_NOQUOTES, 'UTF-8');
        return "<h1 class='monolog-output'>{$title}</h1>";
    }

    protected function composeSubtitle($subtitle)
    {
        $subtitle = htmlspecialchars($subtitle, ENT_NOQUOTES, 'UTF-8');
        return "<style>
                    h1.monolog-output {
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
        $th = htmlspecialchars($th, ENT_NOQUOTES, 'UTF-8');
        if ($escapeTd) {
            $td = '<pre>' . htmlspecialchars($td, ENT_NOQUOTES, 'UTF-8') . '</pre>';
        }

        return "<tr style='padding:4px; spacing:0; text-align:left;'>\n
                    <th style='background:#cccccc' width='100px'>{$th}:</th>\n
                    <td style='padding:4px; spacing:0; text-align:left; background:#eeeeee'>" . $td . "</td>\n
                </tr>";
    }
}
