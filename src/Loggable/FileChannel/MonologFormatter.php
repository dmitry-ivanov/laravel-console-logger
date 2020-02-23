<?php

namespace Illuminated\Console\Loggable\FileChannel;

use Monolog\Formatter\LineFormatter;

class MonologFormatter extends LineFormatter
{
    /**
     * Create a new instance of the formatter.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct("[%datetime%]: [%level_name%]: %message%\n%context%\n", 'Y-m-d H:i:s', true, true);
    }

    /**
     * Formats a log record.
     *
     * @param array $record
     * @return mixed
     */
    public function format(array $record): string
    {
        if ($record['message'] == '%separator%') {
            return str_repeat("\n", 11);
        }

        $output = parent::format($record);
        return rtrim($output) . "\n";
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

    /**
     * Normalize the given data.
     *
     * @param mixed $data
     * @param int $depth
     * @return mixed
     */
    protected function normalize($data, $depth = 0)
    {
        if (is_iterable($data)) {
            return collect($data)->map(function ($item) {
                return $this->normalize($item);
            })->toArray();
        }

        return parent::normalize($data, $depth);
    }
}
