<?php

namespace Illuminated\Console\Loggable\Notifications\EmailChannel;

use Monolog\Formatter\HtmlFormatter;

class MonologHtmlFormatter extends HtmlFormatter
{
    /**
     * Create a new instance of the formatter.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Y-m-d H:i:s');
    }

    /**
     * Formats a log record.
     *
     * @param array $record
     * @return string
     */
    public function format(array $record): string
    {
        $output = '<!DOCTYPE html>';
        $output .= '<html lang="en">';

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

    /**
     * Get color for the given level.
     *
     * @param int $level
     * @return string
     */
    public function getLevelColor(int $level)
    {
        return $this->logLevels[$level];
    }

    /**
     * Compose style for the given record.
     *
     * @param array $record
     * @return string
     */
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
                        margin: 0;
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

    /**
     * Compose title for the given record.
     *
     * @param array $record
     * @return string
     */
    protected function composeTitle(array $record)
    {
        $levelName = e($record['level_name']);
        $title = "<h2 class='title {$levelName}'>{$levelName}</h2>";

        if (app()->isProduction()) {
            return $title;
        }

        $environment = app()->environment();
        $environment = e(str_upper($environment));
        $title .= '<style>.title { padding-bottom: 0 !important; } .subtitle { padding-top: 0 !important; }</style>';
        $title .= "<h3 class='subtitle {$levelName}'>This notification has been sent from the `{$environment}` environment!</h3>";

        return $title;
    }

    /**
     * Compose details for the given record.
     *
     * @param array $record
     * @return string
     */
    protected function composeDetails(array $record)
    {
        $details = '<table cellspacing="1" width="100%">';

        $details .= $this->composeDetailsRow('Message', (string) $record['message']);
        if (!empty($record['context'])) {
            $context = $this->convertToString($record['context']);
            $details .= $this->composeDetailsRow('Context', $context);
        }
        $details .= $this->composeDetailsRow('Time', $record['datetime']->format($this->dateFormat));

        $details .= '</table>';

        return $details;
    }

    /**
     * Compose the details row.
     *
     * @param string $header
     * @param string $body
     * @return string
     */
    protected function composeDetailsRow(string $header, string $body = ' ')
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

    /**
     * Convert the given data to string.
     *
     * @param mixed $data
     * @return string
     */
    protected function convertToString($data): string
    {
        if (is_array($data)) {
            return get_dump($data);
        }

        return parent::convertToString($data);
    }
}
