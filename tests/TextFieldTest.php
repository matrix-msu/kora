<?php

use App\TextField as TextField;
use App\Record as Record;

class TextFieldTest extends TestCase {

    /**
     * Some text constants to keep the tests cleaner.
     */
    const SIMPLE_TEXT = <<<TEXT
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam efficitur felis vel felis congue rhoncus. Aliquam mattis
iaculis metus, non tristique risus maximus a. Integer vel nibh ac nibh lobortis cursus vitae nec est. Suspendisse velit
sem, rutrum vestibulum pellentesque sit amet, tempor id tellus. Sed dictum porta nisi. Fusce vel sapien malesuada, viverra
sem et, consequat sapien. Cras ut gravida odio, vel fringilla leo. Integer interdum odio nibh, ut pharetra lectus accumsan
id. Morbi et quam ex. Proin posuere tellus sit amet ligula mattis, in vestibulum libero volutpat. Integer nec sapien lectus.
Nam sed velit metus. Praesent eu lacus id lorem commodo accumsan. Vestibulum pretium, augue ut ultrices accumsan, dui mi
tincidunt purus, vel condimentum libero nisl in justo.
TEXT;


	/**
	 * Test the search method for a text field.
	 */
	public function test_keyword_search()
	{
        // Whip up a record and a text field with some text.
        Record::create(['rid' => '1', 'pid' => '1', 'fid' => '1', 'owner' => '1', 'kid' => '1-1-1']);
        $field = TextField::create(['rid' => '1', 'flid' => '1', 'text' => self::SIMPLE_TEXT]);

        // Most basic case.
        $args = ['Lorem'];
        $this->assertTrue($field->keyword_search($args, false));

        // Test partial and complete search types.
        $args = ['Lor'];
        $this->assertTrue($field->keyword_search($args, true));
        $this->assertFalse($field->keyword_search($args, false));

        // Test multiple basic cases, some matching some not.
        $args = ['Potatoe', 'Apple', 'ipsum'];
        $this->assertTrue($field->keyword_search($args, false));
	}

}
