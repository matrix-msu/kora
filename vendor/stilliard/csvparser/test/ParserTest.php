<?php

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function testFromStringToArraySimple()
    {
        $string = "id,name\n1,Bob\n2,Bill";
        $parser = new \CsvParser\Parser();
        $csv = $parser->fromString($string);
        $actual = $parser->toArray($csv);

        $expected = array(array('id'=>1, 'name'=>'Bob'),array('id'=>2, 'name'=>'Bill'));
        $this->assertEquals($expected, $actual);
    }

    public function testFromArrayToStringSimple()
    {
        $array = array(array('id'=>1, 'name'=>'Bob'),array('id'=>2, 'name'=>'Bill'));
        $parser = new \CsvParser\Parser();
        $csv = $parser->fromArray($array);
        $actual = $parser->toString($csv);

        $expected = '"id","name"
"1","Bob"
"2","Bill"';
        $this->assertEquals($expected, $actual);
    }

    // example from http://en.wikipedia.org/wiki/Comma-separated_values
    public function testFromStringToArraySubQuotes()
    {
        $string = 'Year,Make,Model,Description,Price
1997,Ford,E350,"ac, abs, moon",3000.00
1999,Chevy,"Venture ""Extended Edition""","",4900.00
1999,Chevy,"Venture ""Extended Edition, Very Large""",,5000.00
1996,Jeep,Grand Cherokee,"MUST SELL!
air, moon roof, loaded",4799.00';
        $parser = new \CsvParser\Parser();
        $csv = $parser->fromString($string);
        $actual = $parser->toArray($csv);

        $expected = array(
            array(
                'Year' => '1997',
                'Make' => 'Ford',
                'Model' => 'E350',
                'Description' => 'ac, abs, moon',
                'Price' => '3000.00',
            ),
            array(
                'Year' => '1999',
                'Make' => 'Chevy',
                'Model' => 'Venture "Extended Edition"',
                'Description' => '',
                'Price' => '4900.00',
            ),
            array(
                'Year' => '1999',
                'Make' => 'Chevy',
                'Model' => 'Venture "Extended Edition, Very Large"',
                'Description' => '',
                'Price' => '5000.00',
            ),
            array(
                'Year' => '1996',
                'Make' => 'Jeep',
                'Model' => 'Grand Cherokee',
                'Description' => 'MUST SELL!
air, moon roof, loaded',
                'Price' => '4799.00',
            ),
        );
        $this->assertEquals($expected, $actual);

        //
        // trying a different text delim
        //
        $string = "Year\tMake\tModel\tDescription\tPrice
1997\tFord\tE350\t'ac, abs, moon'\t3000.00
1999\tChevy\t'Venture ''Extended Edition'''\t''\t4900.00
1999\tChevy\t'Venture ''Extended Edition, Very Large'''\t\t5000.00
1996\tJeep\tGrand Cherokee\t'MUST SELL!
air, moon roof, loaded'\t4799.00";
        $parser = new \CsvParser\Parser("\t", "'");
        $csv = $parser->fromString($string);
        $actual = $parser->toArray($csv);

        $expected = array(
            array(
                'Year' => '1997',
                'Make' => 'Ford',
                'Model' => 'E350',
                'Description' => 'ac, abs, moon',
                'Price' => '3000.00',
            ),
            array(
                'Year' => '1999',
                'Make' => 'Chevy',
                'Model' => "Venture 'Extended Edition'",
                'Description' => '',
                'Price' => '4900.00',
            ),
            array(
                'Year' => '1999',
                'Make' => 'Chevy',
                'Model' => "Venture 'Extended Edition, Very Large'",
                'Description' => '',
                'Price' => '5000.00',
            ),
            array(
                'Year' => '1996',
                'Make' => 'Jeep',
                'Model' => 'Grand Cherokee',
                'Description' => 'MUST SELL!
air, moon roof, loaded',
                'Price' => '4799.00',
            ),
        );
        $this->assertEquals($expected, $actual);

        // sub check with carriage returns instead of line feeds (mac excel support)
        $string = "id,name\r1,Bob\r2,Bill";
        $parser = new \CsvParser\Parser();
        $csv = $parser->fromString($string);
        $actual = $parser->toArray($csv);
        $expected = array(
            array(
                'id' => 1,
                'name' => 'Bob',
            ),
            array(
                'id' => 2,
                'name' => 'Bill',
            ),
        );
        $this->assertEquals($expected, $actual);
    }

    // example from http://en.wikipedia.org/wiki/Comma-separated_values
    public function testFromArrayToStringSubQuotes()
    {
        $array = array(
            array(
                'Year' => '1997',
                'Make' => 'Ford',
                'Model' => 'E350',
                'Description' => 'ac, abs, moon',
                'Price' => '3000.00',
            ),
            array(
                'Year' => '1999',
                'Make' => 'Chevy',
                'Model' => 'Venture "Extended Edition"',
                'Description' => '',
                'Price' => '4900.00',
            ),
            array(
                'Year' => '1999',
                'Make' => 'Chevy',
                'Model' => 'Venture "Extended Edition, Very Large"',
                'Description' => '',
                'Price' => '5000.00',
            ),
            array(
                'Year' => '1996',
                'Make' => 'Jeep',
                'Model' => 'Grand Cherokee',
                'Description' => 'MUST SELL!
air, moon roof, loaded',
                'Price' => '4799.00',
            ),
        );

        $parser = new \CsvParser\Parser();
        $csv = $parser->fromArray($array);
        $actual = $parser->toString($csv);

        //
        // trying a different text delim
        //
        $expected = '"Year","Make","Model","Description","Price"
"1997","Ford","E350","ac, abs, moon","3000.00"
"1999","Chevy","Venture ""Extended Edition""","","4900.00"
"1999","Chevy","Venture ""Extended Edition, Very Large""","","5000.00"
"1996","Jeep","Grand Cherokee","MUST SELL!
air, moon roof, loaded","4799.00"';

        $array = array(
            array(
                'Year' => '1997',
                'Make' => 'Ford',
                'Model' => 'E350',
                'Description' => 'ac, abs, moon',
                'Price' => '3000.00',
            ),
            array(
                'Year' => '1999',
                'Make' => 'Chevy',
                'Model' => "Venture 'Extended Edition'",
                'Description' => '',
                'Price' => '4900.00',
            ),
            array(
                'Year' => '1999',
                'Make' => 'Chevy',
                'Model' => "Venture 'Extended Edition, Very Large'",
                'Description' => '',
                'Price' => '5000.00',
            ),
            array(
                'Year' => '1996',
                'Make' => 'Jeep',
                'Model' => 'Grand Cherokee',
                'Description' => 'MUST SELL!
air, moon roof, loaded',
                'Price' => '4799.00',
            ),
        );

        $this->assertEquals($expected, $actual);

        $parser = new \CsvParser\Parser("\t", "'");
        $csv = $parser->fromArray($array);
        $actual = $parser->toString($csv);

        $expected = "'Year'\t'Make'\t'Model'\t'Description'\t'Price'
'1997'\t'Ford'\t'E350'\t'ac, abs, moon'\t'3000.00'
'1999'\t'Chevy'\t'Venture ''Extended Edition'''\t''\t'4900.00'
'1999'\t'Chevy'\t'Venture ''Extended Edition, Very Large'''\t''\t'5000.00'
'1996'\t'Jeep'\t'Grand Cherokee'\t'MUST SELL!
air, moon roof, loaded'\t'4799.00'";

        $this->assertEquals($expected, $actual);
    }
}
