<?php

use App\ListField as ListField;
use App\Search as Search;

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

        // Arguments are now processed at a higher level than typed fields (so they only happen once).
        $args[0] = Search::convertCloseChars($args[0]);

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