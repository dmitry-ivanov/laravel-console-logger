<?php

namespace Illuminated\Console\Loggable\Notifications\EmailChannel;

use Monolog\Formatter\HtmlFormatter;

class MonologHtmlFormatter extends HtmlFormatter
{
    public function __construct()
    {
        parent::__construct('Y-m-d H:i:s');
    }

    public function format(array $record): string
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

    public function getLevelColor($level)
    {
        return $this->logLevels[$level];
    }

    protected function composeStyle(array $record)
    {
        $level = $record['level'];
        $levelName = $record['level_name'];
        $levelColor = $this->getLevelColor($level);

        return "<link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
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
                    .title.{$levelName}, .subtitle.{$levelName} {
                        background: {$levelColor};
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
        $title = "<h2 class='title {$levelName}'>{$levelName}</h2>";

        $environment = app()->environment();
        if ($environment == 'production') {
            return $title;
        }

        $environment = e(str_upper($environment));
        $title .= '<style>.title { padding-bottom: 0px !important; } .subtitle { padding-top: 0px !important; }</style>';
        $title .= "<h3 class='subtitle {$levelName}'>This notification was sent from `{$environment}` environment!</h3>";

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

        $details .= '</table>';

        return $details;
    }

    protected function composeRow($header, $body = ' ')
    {
        $header = e($header);
        $body = e($body);

        if ($header == 'Context') {
            $body = str_replace(' ', '&nbsp;', $body);
            $body = nl2br($body);
        }

        return "<tr class='details-row'>
                    <th class='details-row-header'>{$header}:</th>
                    <td class='details-row-body'>{$body}</td>
                </tr>";
    }

    protected function convertToString($data): string
    {
        if (is_array($data)) {
            return get_dump($data);
        }

        return parent::convertToString($data);
    }
}
