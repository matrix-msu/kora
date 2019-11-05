<?php

use CsvParser\Parser;
use CsvParser\Writer\StringWriter;

class StringWriterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp() : void
    {
        $this->parser = new Parser(',', '');
    }

    public function testWrite()
    {
        $input = array(array( 'a' => 1, 'b' => 2, 'c' => 44 ));
        $expected = "a,b,c\n1,2,44";
        $actual = StringWriter::write($this->parser, $this->parser->fromArray($input));
        $this->assertSame($expected, $actual);
    }
}

