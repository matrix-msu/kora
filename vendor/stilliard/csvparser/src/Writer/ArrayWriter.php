<?php

namespace CsvParser\Writer;

class ArrayWriter implements WriterInterface
{
    public static function write(\CsvParser\Parser $parser, \CsvParser\Csv $csv)
    {
        return $csv->getData();
    }
}
