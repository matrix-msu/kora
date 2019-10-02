<?php

namespace CsvParser\Reader;

class ArrayReader implements ReaderInterface
{
    public static function read(\CsvParser\Parser $parser, $array)
    {
        return new \CsvParser\Csv($array);
    }
}
