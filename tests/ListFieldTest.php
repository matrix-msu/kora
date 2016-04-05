<?php

use App\ListField as ListField;

/**
 * Class ListFieldTest
 * @group field
 */
class ListFieldTest extends TestCase
{
    /**
     * Test the keyword search for a list field.
     * @group search
     */
    public function test_keywordSearch() {
        $field = new ListField();
        $field->option = 'Apple';

        $args = ['Apple'];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));

        $args = ['le'];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        $args = ['apple', 'potato', 'mango'];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));

        $field->option = "něvrzkotě";

        $args = ["něvrzkotě"];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));

        $field->option = "";

        $args = [" ", null, 0]; //None of these should work.
        $this->assertFalse($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        $args = [""]; // This shouldn't work either. Empty searches shouldn't be meaningful.
        $this->assertFalse($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

    }
}