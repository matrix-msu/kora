<?php

use App\NumberField as NumberField;

/**
 * Class NumberFieldTest
 * @group field
 */
class NumberFieldTest extends TestCase
{
    /**
     * Test the keyword search functionality for a number field.
     * @group search
     */
    public function test_keywordSearch() {
        $field = new NumberField();
        $field->number = "1"; // Numbers stored as strings in the database.

        // Simple case, search only for the exact number.
        $args = ['1'];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));

        // Nothing should go wrong with mixed input.
        $args = ['3', '3.12342', -13001.2304, '1000', 1];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));

        $field->number = "-234.123";

        $args = [-234.123];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));

        $args = ['-123', '234.123', '-234'];
        $this->assertFalse($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        $args = ['not a number!', 'also not a number!'];
        $this->assertFalse($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        $field->number = '0';

        $this->assertFalse($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        $args = ['0'];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));

        $args = [0];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));

        $args = ['0.0'];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));
    }
}