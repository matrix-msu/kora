<?php

namespace CsvParser\Writer;

interface WriterInterface
{
    public static function write(\CsvParser\Parser $parser, \CsvParser\Csv $csv);
}
