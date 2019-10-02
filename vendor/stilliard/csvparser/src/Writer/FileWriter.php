<?php

namespace CsvParser\Writer;
use CsvParser\Exception;

class FileWriter implements WriterInterface
{
    public static function write(\CsvParser\Parser $parser, \CsvParser\Csv $csv, $filename='')
    {
        if ( ! $filename) {
            throw new Exception('Please provide a file name to write to');
        }
        $file = $parser->toString($csv);
        return file_put_contents($filename, $file);
    }
}
