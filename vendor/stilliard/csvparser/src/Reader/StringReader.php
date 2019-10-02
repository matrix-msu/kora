<?php

namespace CsvParser\Reader;

class StringReader implements ReaderInterface
{
    public static function read(\CsvParser\Parser $parser, $string)
    {
        $data = array();

        // shorten some vars for use later
        $d = $parser->fieldDelimiter;
        $e = $parser->fieldEnclosure;
        $l = $parser->lineDelimiter;

        // get headings and body (if \n line feeds, also support reading on carriage returns)
        list($headings, $body) = $l=="\n" ? preg_split('/[\n\r]/', $string, 2) : explode($l, $string, 2);

        // format array of headings/keys
        $headings = str_getcsv($headings, $d, $e);

        // Split row lines
        // to do this we replace new lines with a key and then explode on them
        // regex to match new lines, but not inside quotes, based on: https://stackoverflow.com/questions/632475/regex-to-pick-commas-outside-of-quotes/25544437#25544437
        $rKey = '*%~LINE_BREAK~%*';
        $qE = preg_quote($e);
        $qL = $l=="\n" ? '\n|\r' : preg_quote($l);
        $body = preg_replace('/'.$qE.'[^'.$qE.']*'.$qE.'(*SKIP)(*F)|'.$qL.'/', $rKey, $body);
        $lines = explode($rKey, $body);

        // any lines found? loop them
        if ( ! empty($lines)) {
            $lines = array_values(array_filter($lines)); // filter out blank lines & re-index
            foreach ($lines as $i => $line) {
                $fields = str_getcsv($line, $d, $e);
                $data[$i] = array();
                // loop the headings to map to columns
                foreach ($headings as $j => $heading) {
                    $field = isset($fields[$j]) ? $fields[$j] : '';
                    $data[$i][$heading] = $field;
                }
            }
        }

        return new \CsvParser\Csv($data);
    }
}
