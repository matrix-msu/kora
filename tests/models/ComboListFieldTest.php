<?php

use App\ComboListField as ComboListField;
use App\Field as Field;
use App\Project as Project;
use App\Form as Form;

/**
 * Class ComboListFieldTest
 * @group field
 */
class ComboListFieldTest extends TestCase
{
    /**
     * The combo list field options for a combo field with a text field and a number field.
     * @type string
     */
    const TEXT_NUM = <<<TEXT
[!f1!]Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam cursus risus sed rutrum eleifend. Donec accumsan hendrerit lectus, a semper nunc finibus a. Cras sit amet fringilla est. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nulla augue ex, venenatis at vulputate non, iaculis a nisi. Aliquam blandit efficitur dolor, volutpat sagittis lorem tristique sit amet. Etiam vehicula, augue at porttitor elementum, ligula nunc tincidunt orci, eget fringilla ipsum nunc ac urna. Morbi tempor laoreet leo et pellentesque. Ut fringilla massa fermentum, lacinia ipsum quis, laoreet lacus. Vivamus eu bibendum metus.[!f1!][!f2!]9[!f2!][!val!][!f1!]Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus rhoncus nunc vel sem vulputate dignissim. Morbi tincidunt orci est. Fusce tristique, mauris et scelerisque sodales, tortor elit laoreet neque, id semper elit purus at nunc. Nam luctus commodo tellus eu euismod. Nunc semper eros sit amet massa fermentum egestas. Donec.[!f1!][!f2!]3[!f2!][!val!][!f1!]Just a little text here :)[!f1!][!f2!]1[!f2!]
TEXT;
    const TEXT_NUM_OPTIONS = <<<TEXT
[!Field1!][Type]Text[Type][Name]CmbText[Name][Options][!Regex!][!Regex!][!MultiLine!]0[!MultiLine!][Options][!Field1!][!Field2!][Type]Number[Type][Name]CmbNumber[Name][Options][!Max!]10[!Max!][!Min!]1[!Min!][!Increment!]1[!Increment!][!Unit!][!Unit!][Options][!Field2!]
TEXT;

    /**
     * The combo list field options for a combo field with a list field and a multi-select list field.
     * @type string
     */
    const LIST_MSL = <<<TEXT
[!f1!]Chicken[!f1!][!f2!]Elm[!]Birch[!]Ash[!f2!][!val!][!f1!]Dolphin[!f1!][!f2!]Maple[!]Cedar[!f2!][!val!][!f1!]Horse[!f1!][!f2!]Oak[!]Cedar[!]Ash[!f2!]
TEXT;
    const LIST_MSL_OPTIONS = <<<TEXT
[!Field1!][Type]List[Type][Name]CmbList[Name][Options][!Options!]Cow[!]Chicken[!]Horse[!]Dolphin[!Options!][Options][!Field1!][!Field2!][Type]Multi-Select List[Type][Name]CmbMSL[Name][Options][!Options!]Elm[!]Oak[!]Birch[!]Maple[!]Cedar[!]Ash[!Options!][Options][!Field2!]
TEXT;

    /**
     * The combo list field options for a combo field with a generated list and multi-select list field.
     * @type string
     */
    const MSL_GEN = <<<TEXT
[!f1!]Default(MSL)[!f1!][!f2!]Default(Gen)[!f2!][!val!][!f1!]Fletching[!]Attack[!f1!][!f2!]Merlin's Crystal[!]Chef's Assistant[!f2!][!val!][!f1!]Fletching[!]Fishing[!]Hunter[!f1!][!f2!]Regicide[!f2!]
TEXT;
    const MSL_GEN_OPTIONS = <<<TEXT
[!Field1!][Type]Multi-Select List[Type][Name]Combo Multi-Select List[Name][Options][!Options!]Fletching[!]Attack[!]Fishing[!]Hunter[!]Default(MSL)[!Options!][Options][!Field1!][!Field2!][Type]Generated List[Type][Name]Combo Generated List[Name][Options][!Options!]Merlin's Crystal[!]Recipe for Distaster[!]Zogre Flesh Eaters[!]Legend's Quest[!]Default(Gen)[!Options!][!Regex!][!Regex!][Options][!Field2!]
TEXT;

    /**
     * Test keyword search.
     * @group search
     */
    public function test_keywordSearch() {
        $project = self::dummyProject();
        $this->assertInstanceOf('App\Project', $project);

        $form = self::dummyForm($project->pid);
        $this->assertInstanceOf('App\Form', $form);

        $field = self::dummyField("Combo List", $project->pid, $form->fid);
        $this->assertInstanceOf('App\Field', $field);

        $record = self::dummyRecord($project->pid, $form->fid);
        $this->assertInstanceOf('App\Record', $record);

        //
        // Test all the fields that can be under a combo list.
        // Namely, text, number, list, multi-select list, and generated list.
        //

        //
        // Text, Number combination list.
        //
        $field->options = self::TEXT_NUM_OPTIONS;
        $field->save();

        $cmb_field = new \App\ComboListField();
        $cmb_field->rid = $record->rid;
        $cmb_field->flid = $field->flid;
        $cmb_field->options = self::TEXT_NUM;
        $cmb_field->ftype1 = "";
        $cmb_field->ftype2 = "";
        $cmb_field->save();

        $args = ['LoReM'];
        $this->assertTrue($cmb_field->keywordSearch($args, true));
        $this->assertTrue($cmb_field->keywordSearch($args, false));

        $args = ['9'];
        $this->assertTrue($cmb_field->keywordSearch($args, true));
        $this->assertTrue($cmb_field->keywordSearch($args, false));

        $args = [2, null, -1, 0, ""];
        $this->assertFalse($cmb_field->keywordSearch($args, true));
        $this->assertFalse($cmb_field->keywordSearch($args, false));

        $args = ["fring", "lao", "biben"]; // Partials
        $this->assertTrue($cmb_field->keywordSearch($args, true));
        $this->assertFalse($cmb_field->keywordSearch($args, false));

        //
        // List, Multi-select List
        //
        $field->options = self::LIST_MSL_OPTIONS;
        $field->save();

        $cmb_field->options = self::LIST_MSL;
        $cmb_field->save();

        $args = ['ChIcKeN'];
        $this->assertTrue($cmb_field->keywordSearch($args, true));
        $this->assertTrue($cmb_field->keywordSearch($args, false));

        $args = ['maple'];
        $this->assertTrue($cmb_field->keywordSearch($args, true));
        $this->assertTrue($cmb_field->keywordSearch($args, false));

        $args = ['elm'];
        $this->assertTrue($cmb_field->keywordSearch($args, true));
        $this->assertTrue($cmb_field->keywordSearch($args, false));

        $args = ['cow']; // Option that can be selected in the list, but is not in any records.
        $this->assertFalse($cmb_field->keywordSearch($args, true));
        $this->assertFalse($cmb_field->keywordSearch($args, false));

        $args = ['icken', 'phin', 'aple', 'edar']; // Partials
        $this->assertTrue($cmb_field->keywordSearch($args, true));
        $this->assertFalse($cmb_field->keywordSearch($args, false));

        $args = ['[!]', null, 0, -1, 32418234.098];
        $this->assertFalse($cmb_field->keywordSearch($args, true));
        $this->assertFalse($cmb_field->keywordSearch($args, false));

        //
        // Multi-select List, Generated List
        //
        $field->options = self::MSL_GEN_OPTIONS;
        $field->save();

        $cmb_field->options = self::MSL_GEN;
        $cmb_field->save();

        $args = ['default(msl)'];
        $this->assertTrue($cmb_field->keywordSearch($args, true));
        $this->assertTrue($cmb_field->keywordSearch($args, false));

        $args = ['default(gen)'];
        $this->assertTrue($cmb_field->keywordSearch($args, true));
        $this->assertTrue($cmb_field->keywordSearch($args, false));
    }
}