<?php

namespace CsvParser\Writer;

class ChunksWriter implements WriterInterface
{
    public static function write(\CsvParser\Parser $parser, \CsvParser\Csv $csv, $size=1000)
    {
        $data = $csv->getData();
        $chunks = array_chunk($data, $size, true);
        $end = array();
        foreach ($chunks as $chunk) {
            $end[] = new \CsvParser\Csv($chunk);
        }
        return $end;
    }
}
