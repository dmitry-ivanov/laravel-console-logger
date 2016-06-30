<?php

namespace Illuminated\Console\Log;

use Monolog\Formatter\HtmlFormatter as MonologHtmlFormatter;

class HtmlFormatter extends MonologHtmlFormatter
{
    public function format(array $record)
    {
        $output = '<!DOCTYPE html>';
        $output .= '<html>';

        $output .= '<head>';
        $output .= '<meta charset="utf-8">';
        $output .= $this->composeStyle($record);
        $output .= '</head>';

        $output .= '<body>';
        $output .= $this->composeTitle($record);
        $output .= $this->composeDetails($record);
        $output .= '</body>';

        $output .= '</html>';

        return $output;
    }

    protected function composeStyle(array $record)
    {
        $level = $record['level'];

        return "<link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
                <style>
                    body {
                        font-family: 'Lato', sans-serif;
                        font-size: 16px;
                    }
                    .title, .subtitle {
                        background: {$this->logLevels[$level]};
                        color: #ffffff;
                        margin: 0px;
                        padding: 15px;
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
            $context = $this->convertToString($record['context']);
            $details .= $this->composeRow('Context', $context);
        }
        $details .= $this->composeRow('Time', $record['datetime']->format($this->dateFormat));
        $details .= $this->composeRow('Environment', app()->environment());

        $details .= '</table>';

        return $details;
    }

    protected function composeRow($header, $body = ' ')
    {
        $header = e($header);
        $body = e($body);

        if ($header == 'Context') {
            $body = "<pre>{$body}</pre>";
        }

        return "<tr class='details-row'>
                    <th class='details-row-header'>{$header}:</th>
                    <td class='details-row-body'>{$body}</td>
                </tr>";
    }

    protected function convertToString($data)
    {
        if (is_array($data)) {
            return get_dump($data);
        }

        return parent::convertToString($data);
    }
}
