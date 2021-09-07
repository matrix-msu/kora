<?php

class CsvTest extends PHPUnit_Framework_TestCase
{
    public function setUp() : void
    {
        $this->string = "id,name\n1,Bob\n2,Bill";
        $this->parser = new \CsvParser\Parser();
        $this->csv = $this->parser->fromString($this->string);
    }

    public function testAppendRow()
    {
        $actual = $this->parser->toArray($this->csv);
        $expected = array(array('id'=>1, 'name'=>'Bob'),array('id'=>2, 'name'=>'Bill'));
        $this->assertEquals($expected, $actual);

        $this->csv->appendRow(array('id'=>3, 'name'=>'Ben'));
        $actual = $this->parser->toArray($this->csv);
        $expected = array(array('id'=>3, 'name'=>'Ben'),array('id'=>1, 'name'=>'Bob'),array('id'=>2, 'name'=>'Bill'));
        $this->assertEquals($expected, $actual);
    }

    public function testPrependRow()
    {
        $this->string = "id,name\n1,Bob\n2,Bill";
        $this->parser = new \CsvParser\Parser();
        $this->csv = $this->parser->fromString($this->string);

        $this->csv->prependRow(array('id'=>3, 'name'=>'Ben'));
        $actual = $this->parser->toArray($this->csv);
        $expected = array(array('id'=>1, 'name'=>'Bob'),array('id'=>2, 'name'=>'Bill'),array('id'=>3, 'name'=>'Ben'));
        $this->assertEquals($expected, $actual);
    }

    public function testGetRowCount()
    {
        $this->string = "id,name\n1,Bob\n2,Bill";
        $this->parser = new \CsvParser\Parser();
        $this->csv = $this->parser->fromString($this->string);

        $this->assertEquals(2, $this->csv->getRowCount());

        $this->csv->appendRow(array('id'=>3, 'name'=>'Ben'));
        $this->assertEquals(3, $this->csv->getRowCount());

        $this->csv->prependRow(array('id'=>77, 'name'=>'Benji'));
        $this->assertEquals(4, $this->csv->getRowCount());
    }

    public function testColumnExists()
    {
        $this->string = "id,name\n1,Bob\n2,Bill";
        $this->parser = new \CsvParser\Parser();
        $this->csv = $this->parser->fromString($this->string);

        $this->assertTrue($this->csv->columnExists('name'));
        $this->assertFalse($this->csv->columnExists('somethingelse'));
    }

    public function testMapColumn()
    {
        $this->csv->mapColumn('name', function ($val) {
            return 'Sir ' . $val;
        });
        $actual = $this->parser->toArray($this->csv);
        $expected = array(array('id'=>1, 'name'=>'Sir Bob'),array('id'=>2, 'name'=>'Sir Bill'));
        $this->assertEquals($expected, $actual);
    }

    public function testMapRows()
    {
        $this->csv->mapRows(function ($row) {
            $row['codename'] = base64_encode($row['id']);
            return $row;
        });
        $actual = $this->parser->toArray($this->csv);
        $expected = array(array('id'=>1, 'name'=>'Bob','codename'=>'MQ=='),array('id'=>2, 'name'=>'Bill','codename'=>'Mg=='));
        $this->assertEquals($expected, $actual);
    }

    public function testFilterRows()
    {
        $this->csv->filterRows(function ($row) {
            return $row['id'] != 2;
        });
        $actual = $this->parser->toArray($this->csv);
        $expected = array(array('id'=>1, 'name'=>'Bob'));
        $this->assertEquals($expected, $actual);
    }

    public function testRemoveDuplicates()
    {
        $string = "id,name\n1,Bob\n2,Bill\n3,Bob\n4,James";
        $parser = new \CsvParser\Parser();
        $csv = $parser->fromString($string);
        
        $csv->removeDuplicates('name');
        
        $actual = $parser->toArray($csv);
        $expected = array(0=>array('id'=>1, 'name'=>'Bob'), 1=>array('id'=>2, 'name'=>'Bill'), 3=>array('id'=>4, 'name'=>'James'));
        $this->assertEquals($expected, $actual);
    }

    public function testRemoveBlanks()
    {
        $string = "id,name\n1,Bob\n2,Bill\n3,\n4,James";
        $parser = new \CsvParser\Parser();
        $csv = $parser->fromString($string);

        $csv->removeBlanks('name');

        $actual = $parser->toArray($csv);
        $expected = array(0=>array('id'=>1, 'name'=>'Bob'), 1=>array('id'=>2, 'name'=>'Bill'), 3=>array('id'=>4, 'name'=>'James'));
        $this->assertEquals($expected, $actual);
    }

    public function testFirst()
    {
        $string = "id,name\n1,Bob\n2,Bill\n3,\n4,James";
        $parser = new \CsvParser\Parser();
        $csv = $parser->fromString($string);
        $expected = ['id' => 1, 'name' => 'Bob'];
        $this->assertEquals($expected, $csv->first());
    }

    public function testGetKeys()
    {
        $string = "id,name\n1,Bob\n2,Bill\n3,\n4,James";
        $parser = new \CsvParser\Parser();
        $csv = $parser->fromString($string);
        $expected = ['id', 'name'];
        $this->assertEquals($expected, $csv->getKeys());
    }

    public function testReKey()
    {
        $string = "id,name\n1,Bob\n2,Bill\n3,\n4,James";
        $parser = new \CsvParser\Parser();
        $csv = $parser->fromString($string);
        $csv->reKey(['name', 'id']);
        $this->assertEquals("\"name\",\"id\"\n\"Bob\",\"1\"\n\"Bill\",\"2\"\n\"\",\"3\"\n\"James\",\"4\"", $parser->toString($csv));
    }
}
