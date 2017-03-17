<?php

use App\BaseField;

class BaseFieldTest extends TestCase
{
    /**
     * Ensure the mapped types are all database names.
     */
    public function test_mappedTypes() {
        foreach(BaseField::$MAPPED_FIELD_TYPES as $key => $db_name) {
            $this->assertContains($db_name, BaseField::$TABLE_NAMES);
        }
    }
}