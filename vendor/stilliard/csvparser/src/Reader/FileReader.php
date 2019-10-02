<?php

namespace CsvParser\Reader;

class FileReader implements ReaderInterface
{
    public static function read(\CsvParser\Parser $parser, $file)
    {
        $contents = file_get_contents($file);
        return $parser->fromString($contents);
    }
}
