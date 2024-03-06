<?php

namespace Illuminated\Console\Loggable\FileChannel;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

class MonologFormatter extends LineFormatter
{
    /**
     * Create a new instance of the formatter.
     */
    public function __construct()
    {
        parent::__construct("[%datetime%]: [%level_name%]: %message%\n%context%\n", 'Y-m-d H:i:s', true, true);
    }

    /**
     * Formats a log record.
     */
    public function format(LogRecord $record): string
    {
        if ($record->message == '%separator%') {
            return str_repeat("\n", 11);
        }

        $output = parent::format($record);
        return rtrim($output) . "\n";
    }

    /**
     * Convert the given data to string.
     */
    protected function convertToString(mixed $data): string
    {
        if (is_array($data)) {
            return get_dump($data);
        }

        return parent::convertToString($data);
    }

    /**
     * Normalize the given data.
     */
    protected function normalize(mixed $data, int $depth = 0): mixed
    {
        if (is_iterable($data)) {
            return collect($data)->map(function ($item) {
                return $this->normalize($item);
            })->toArray();
        }

        return parent::normalize($data, $depth);
    }
}
