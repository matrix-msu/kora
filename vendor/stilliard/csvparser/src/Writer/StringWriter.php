<?php

namespace CsvParser\Writer;

class StringWriter implements WriterInterface
{
    public static function write(\CsvParser\Parser $parser, \CsvParser\Csv $csv)
    {
        $data = $csv->getData();

        if ($data && !empty($data)) {
            $output = array(
                implode($parser->fieldDelimiter, array_map(function ($value) use ($parser) {
                    return $parser->fieldEnclosure . str_replace($parser->fieldEnclosure, $parser->fieldEnclosure.$parser->fieldEnclosure, $value) . $parser->fieldEnclosure;
                }, array_keys(current($data))))
            );

            foreach ($data as $line) {
                $output[] = implode($parser->fieldDelimiter, array_map(function ($value) use ($parser) {
                    return $parser->fieldEnclosure . str_replace($parser->fieldEnclosure, $parser->fieldEnclosure.$parser->fieldEnclosure, $value) . $parser->fieldEnclosure;
                }, $line));
            }

            return implode($parser->lineDelimiter, $output);
        } else {
            return '';
        }
    }
}
