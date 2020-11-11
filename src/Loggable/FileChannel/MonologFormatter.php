<?php

namespace Illuminated\Console\Loggable\FileChannel;

use Monolog\Formatter\LineFormatter;
use Traversable;

class MonologFormatter extends LineFormatter
{
    public function __construct()
    {
        parent::__construct("[%datetime%]: [%level_name%]: %message%\n%context%\n", null, true, true);
    }

    public function format(array $record)
    {
        if ($record['message'] == '%separator%') {
            return str_repeat("\n", 11);
        }

        $output = parent::format($record);
        return rtrim($output) . "\n";
    }

    protected function convertToString($data)
    {
        if (is_array($data)) {
            return get_dump($data);
        }

        return parent::convertToString($data);
    }

    protected function normalize($data, $depth = 0)
    {
        if (is_array($data) || ($data instanceof Traversable)) {
            $normalized = [];
            foreach ($data as $key => $value) {
                $normalized[$key] = $this->normalize($value);
            }
            return $normalized;
        }

        return parent::normalize($data, $depth);
    }
}
