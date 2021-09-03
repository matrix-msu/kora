# Csv Parser
Quickly take in and output csv formats.

[![Build Status](https://travis-ci.org/stilliard/CsvParser.png?branch=master)](https://travis-ci.org/stilliard/CsvParser)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/stilliard/CsvParser/badges/quality-score.png?s=3f821d3231d78e86c41c9cd9213c68f164bb53d6)](https://scrutinizer-ci.com/g/stilliard/CsvParser/)
[![Code Coverage](https://scrutinizer-ci.com/g/stilliard/CsvParser/badges/coverage.png?s=dbc9d91b767b84a1a649b5695b8a3cdce690684a)](https://scrutinizer-ci.com/g/stilliard/CsvParser/)
[![Latest Stable Version](https://poser.pugx.org/stilliard/csvparser/v/stable.png)](https://packagist.org/packages/stilliard/csvparser) [![Total Downloads](https://poser.pugx.org/stilliard/csvparser/downloads.png)](https://packagist.org/packages/stilliard/csvparser) [![Latest Unstable Version](https://poser.pugx.org/stilliard/csvparser/v/unstable.png)](https://packagist.org/packages/stilliard/csvparser) [![License](https://poser.pugx.org/stilliard/csvparser/license.png)](https://packagist.org/packages/stilliard/csvparser)
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2Fstilliard%2FCsvParser.svg?type=shield)](https://app.fossa.io/projects/git%2Bgithub.com%2Fstilliard%2FCsvParser?ref=badge_shield)

## Install
```bash
composer require stilliard/csvparser 1.1.6
```

## Example usage:
```php

//
// Simple array to string usage
//
$array = [['id'=>1, 'name'=>'Bob'],['id'=>2, 'name'=>'Bill']];
$parser = new \CsvParser\Parser();
$csv = $parser->fromArray($array);
var_dump($parser->toString($csv));
```

```php
//
// Full power examples:
//

// setup initial parser
$parser = new \CsvParser\Parser(',', '"', "\n");

// change settings after init
// set column delimiter
$parser->fieldDelimiter = ';';
// set text enclosure
$parser->fieldEnclosure = "'";
// set line delimiter
$parser->lineDelimiter = "\n";

// Input (returns instance of \CsvParser\Csv)
$csv = $parser->fromArray([['id'=>1, 'name'=>'Bob'],['id'=>2, 'name'=>'Bill']]);
$csv = $parser->fromString("id,name\n1,Bob\n2,Bill");
$csv = $parser->fromFile('demo.csv');

// get row count
var_dump($csv->getRowCount());

// get the first row as array from the csv
var_dump($csv->first());

// get the column headings / keys
var_dump($csv->getKeys());

// want to force a column sort / index?
$csv->reKey(['id', 'name', 'email']);

// append/prepend rows
$csv->appendRow(['id'=>3, 'name'=>'Ben']);
$csv->prependRow(['id'=>4, 'name'=>'Barry']);

// map function over column
$csv->mapColumn('name', 'trim');
$csv->mapColumn('name', function ($name) {
    return trim($name);
});

// map function over rows
$csv->mapRows(function ($row) {
    $row['codename'] = base64_encode($row['id']);
    return $row;
});

// add a column
$csv->addColumn('codename', 'default value');

// remove a column
$csv->removeColumn('codename');

// filter down rows
$csv->filterRows(function ($row) {
    return $row['id'] != '#'; // remove rows where the id column just has a hash inside
});

// remove row by index
$csv->removeRowByIndex(4);
// or remove row(s) by column value, such as id 22
$csv->removeRow('id', 22);
// or remove row(s) by multiple creiteria, such as when id 22 AND when name is 'some name'
$csv->removeRows(['id'=>22, 'name'=>'some name']);

// Column reordering
$csv->reorderColumn('colname', 0); // move to position 0 (the start)
// or multiple
$csv->reorderColumns(['colname1'=>0, 'colname2'=>4]);

// Row reordering
// to move the row with id of 22 to the start
$csv->reorderRow('id', 22, 0);
// or move id 22 to the start, and id 5 after it
$csv->reorderRows('id', [22 => 0, 5 => 1]);

// Sort rows by a column
$csv->reorderRowsByColumn('id', 'desc');
// or even multiples:
$csv->reorderRowsByColumns(['name', 'id' => 'desc']);

// Output
var_dump($parser->toArray($csv));
var_dump($parser->toString($csv));
var_dump($parser->toFile($csv, 'demo.csv')); // file was created?

// Need to chunk into multiple chunks/files?
$chunks = $parser->toChunks($csv, 1000);
foreach ($chunks as $i => $chunk) {
    $parser->toFile($chunk, "output-{$i}.csv");
}

// Remove duplicates
$csv->removeDuplicates('email');

// Remove blanks
$csv->removeBlanks('email');

```

## License
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2Fstilliard%2FCsvParser.svg?type=large)](https://app.fossa.io/projects/git%2Bgithub.com%2Fstilliard%2FCsvParser?ref=badge_large)
