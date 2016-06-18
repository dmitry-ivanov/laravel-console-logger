<?php

namespace Illuminated\Console\Log;

use Monolog\Formatter\LineFormatter;

class Formatter extends LineFormatter
{
    public function __construct()
    {
        parent::__construct("[%datetime%]: [%level_name%]: %message%\n%context%\n", null, true, true);
    }

    public function format(array $record)
    {
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
}
