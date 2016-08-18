<?php

use App\RichTextField as RichTextField;

/**
 * Class RichTextFieldTest
 * @group field
 */
class RichTextFieldTest extends TestCase
{
	/**
     * Some lorem ipsum rich text with some random rich text formatting (taking from our actual editor for consistency).
     */
	const SIMPLE_RICH = <<<TEXT
<h2 style="font-style:italic">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</h2>
<ol>
	<li><strong>Morbi nec felis placerat, ultrices dui et, aliquam ligula.</strong></li>
	<li><em>Praesent in semper sapien.</em></li>
	<li><s>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</s></li>
</ol>
<blockquote>
<ul>
	<li>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</li>
	<li><s><strong>Nam at magna purus</strong></s>.</li>
	<li>Vestibulum sodales justo ac tincidunt fermentum.</li>
</ul>
</blockquote>
<div style="background:#eee; border:1px solid #ccc; padding:5px 10px"><tt>Nam pellentesque velit magna, vel faucibus lorem semper quis. Pellentesque vehicula tellus nunc, vitae tincidunt odio porta nec. In nec ullamcorper neque. Nam quis purus nulla. Cras et nisl orci.</tt></div>
TEXT;

    /**
     * Some Czech rich text to test special characters.
     */
    const COMPLEX_RICH = <<<TEXT
<h1>Mu&scaron;tby něvrzkotě ně vramy mř&iacute;m&iacute; a bě&scaron; nitl&iacute;? Fr&eacute;ř ňoni zkedě z tini nitrudr sepodi o b&aacute;f&eacute; pěkmě?</h1>
<ol>
	<li><strong><em>I nině vuni &uacute;niněchů&nbsp;vlor, tiň &scaron;tabli hrou&scaron;hrni ce&scaron;le grůcoj tlis bev puni tl&yacute;&scaron;i pr&eacute; &scaron;le. </em></strong></li>
	<li><strong><em>Midi a ti, vevly&scaron;t a jousk&yacute; hlyniv &scaron;ech člyb&nbsp;pty&scaron;kožra krocavě s nit&yacute;. </em></strong></li>
</ol>
<ul>
	<li><em><s>Pipefr&yacute; dipyb mufry? </s></em></li>
	<li><em><s>Pivředizk&aacute; niťou &scaron;oč pte dinir&eacute; osař. </s></em></li>
</ul>
<div style="background:#eee;border:1px solid #ccc;padding:5px 10px;"><cite>Zloužlo vrozatich ry&scaron;u ni&scaron;louj &scaron;le v ťaskz&aacute; očla.<br />
V ni&scaron;kou k di cruzrordli lanni, ktuviz pěv z pepy tlůtě&scaron; o ktub pěťlkedi.</cite></div>
TEXT;

    /**
     * Test the keyword search method for a rich text field.
     * @group search
     */
    public function test_keywordSearch() {
        $field = new RichTextField();
        $field->rawtext = self::SIMPLE_RICH;
        $field->save();

        // Basic case, any text should obviously be found.
        $args = ['nato', 'penatib']; // Partial values
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        $args = ['Lorem', 'justo', 'sodales', 'justo']; // Complete values
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));

        // The search should not find any HTML tags.
        $args = ['<h2', 'style="font-style:italic"', "<div", "#eee;"];
        $this->assertFalse($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        //
        // Test special character searches.
        //
        $field->rawtext = self::COMPLEX_RICH;
        $field->save();

        // Most basic special character case.
        /** Special character processing was moved up so it only happens once in a search. */
        $args = ['něvrzkotě'];
        $args[0] = \App\Search::convertCloseChars($args[0]);

        $this->assertTrue($field->keywordSearch($args, false));
        $this->assertTrue($field->keywordSearch($args, true));

        $args = ['zkotě'];
        $args[0] = \App\Search::convertCloseChars($args[0]);
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        $args = ['něvrzkotě', 'nině', ''];
        $args[0] = \App\Search::convertCloseChars($args[0]);
        $args[1] = \App\Search::convertCloseChars($args[1]);
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));
    }
}