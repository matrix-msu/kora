<?php

use CsvParser\Parser;
use CsvParser\Writer\ArrayWriter;

class ArrayWriterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp() : void
    {
        $this->parser = new Parser();
    }

    public function testWrite()
    {
        $input = array(array( 'a' => 1, 'b' => 2, 'c' => 44 ));
        $expected = $input;
        $actual = ArrayWriter::write($this->parser, $this->parser->fromArray($input));
        $this->assertSame($expected, $actual);
    }
}

