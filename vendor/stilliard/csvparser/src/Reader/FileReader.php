<?php

namespace CsvParser\Reader;

class FileReader implements ReaderInterface
{
    public static function read(\CsvParser\Parser $parser, $file)
    {
        $contents = file_get_contents($file);
        $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents); // remove UTF-8 BOM that excel can add
        return $parser->fromString($contents);
    }
}
