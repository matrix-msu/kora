<?php

namespace CsvParser\Reader;

interface ReaderInterface
{
    public static function read(\CsvParser\Parser $parser, $val);
}
