<?php

use App\Field;
use App\Revision;
use App\RichTextField;
use App\Http\Controllers\RevisionController;

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

    public function test_getAdvancedSearchQuery() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_RICH_TEXT, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $rich_field = new RichTextField();
        $rich_field->rid = $record->rid;
        $rich_field->flid = $field->flid;
        $rich_field->rawtext = self::SIMPLE_RICH;
        $rich_field->save(); // Saves the searchable rawtext as well.

        $dummy_query = [$field->flid . "_input" => "Pellentesque vehicula"];

        $query = RichTextField::getAdvancedSearchQuery($field->flid, $dummy_query);
        $rid = $query->first()->rid;

        $this->assertEquals($rid, $record->rid);
    }

    public function test_rollback() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_RICH_TEXT, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $rich_field = new RichTextField();
        $rich_field->rid = $record->rid;
        $rich_field->flid = $field->flid;
        $rich_field->rawtext = self::SIMPLE_RICH;
        $rich_field->save();

        $revision = RevisionController::storeRevision($record->rid, Revision::CREATE);

        $rich_field->rawtext = self::COMPLEX_RICH;
        $rich_field->save();

        $rich_field = RichTextField::rollback($revision, $field);
        $this->assertEquals($rich_field->rawtext, self::SIMPLE_RICH);
    }
}