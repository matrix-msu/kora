<?php

use App\TextField as TextField;
use App\BaseField as BaseField;

class TextFieldTest extends TestCase {

    /**
     * Simple text, classic lorem ipsum, no special characters.
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
     * Some Czech dummy text to test special characters.
     */
    const COMPLEX_TEXT = <<<TEXT
Muštby něvrzkotě ně vramy mřímí a běš nitlí? Fréř ňoni zkedě z tini nitrudr sepodi o báfé pěkmě? I nině vuni úniněchů
vlor, tiň štabli hroušhrni cešle grůcoj tlis bev puni tlýši pré šle. Midi a ti, vevlyšt a jouský hlyniv šech člyb
ptyškožra krocavě s nitý. Pipefrý dipyb mufry? Pivředizká niťou šoč pte diniré osař. Zloužlo vrozatich ryšu nišlouj šle
v ťaskzá očla. V niškou k di cruzrordli lanni, ktuviz pěv z pepy tlůtěš o ktub pěťlkedi.
TEXT;

    /**
     * Test the close character converter.
     * Just one test will suffice for this, as its the same method used for each class derived from App\BaseField.
     */
    public function test_convertCloseChars() {
        $converted = BaseField::convertCloseChars(self::COMPLEX_TEXT);

        $handConverted = <<<TEXT
Mustby nevrzkote ne vramy mrimi a bes nitli? Frer noni zkede z tini nitrudr sepodi o bafe pekme? I nine vuni uninechu
vlor, tin stabli hroushrni cesle grucoj tlis bev puni tlysi pre sle. Midi a ti, vevlyst a jousky hlyniv sech clyb
ptyskozra krocave s nity. Pipefry dipyb mufry? Pivredizka nitou soc pte dinire osar. Zlouzlo vrozatich rysu nislouj sle
v taskza ocla. V niskou k di cruzrordli lanni, ktuviz pev z pepy tlutes o ktub petlkedi.
TEXT;

        $convArr = explode(" ", $converted);
        $handArr = explode(" ", $handConverted);

        for ($i = 0; $i < count($convArr); $i++) {
            $this->assertEquals($convArr[$i], $handArr[$i]);
        }

    }

	/**
	 * Test the search method for a text field.
	 */
	public function test_keywordSearch()
	{
        $field = new TextField(['rid' => '1', 'flid' => '1', 'text' => self::SIMPLE_TEXT]);

        // Most basic case.
        $args = ['Lorem'];
        $this->assertTrue($field->keywordSearch($args, false));

        // Test partial and complete search types.
        $args = ['Lor'];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        // Test multiple basic cases, some matching some not.
        $args = ['Potato', 'Apple', 'ipsum'];
        $this->assertTrue($field->keywordSearch($args, false));

        //
        // Test special character searches.
	    //
        $field->text = self::COMPLEX_TEXT;

        // Most basic special character case.
        $args = ['něvrzkotě'];
        $this->assertTrue($field->keywordSearch($args, false));

        // Test partials.
        $args = ['zkotě'];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        // Test multiple cases.
        $args = ['apple', 'potato', 'ipsum'];
        $this->assertFalse($field->keywordSearch($args, false));
        $this->assertFalse($field->keywordSearch($args, true));

        $args = ['poe the poet', 'hawthorne', 'zkotě'];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        // Test empty cases.
        $args = [];
        $this->assertFalse($field->keywordSearch($args, false));
        $this->assertFalse($field->keywordSearch($args, true));

        $field->text = "";

        $args = ['these', 'are', 'some', 'arguements', 'mřímí'];
        $this->assertFalse($field->keywordSearch($args, false));
        $this->assertFalse($field->keywordSearch($args, true));
    }

}
