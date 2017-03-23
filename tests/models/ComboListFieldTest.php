<?php

use App\ComboListField as ComboListField;
use App\Field as Field;
use App\Project as Project;
use App\Form as Form;
use App\Search as Search;
use \Illuminate\Support\Facades\DB;

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

    const NUM_GEN_OPTIONS = <<<TEXT
[!Field1!][Type]Number[Type][Name]Combo Number[Name][Options][!Max!]9999[!Max!][!Min!]1[!Min!][!Increment!]1[!Increment!][!Unit!][!Unit!][Options][!Field1!][!Field2!][Type]Generated List[Type][Name]Combo Generated[Name][Options][!Options!]Apple[!]Google[!]Microsoft[!]Amazon[!Options!][!Regex!][!Regex!][Options][!Field2!]
TEXT;


    public function test_keywordSearchTyped2() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_COMBO_LIST, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $combo_field = new App\ComboListField();
        $combo_field->fid = $field->fid;
        $combo_field->rid = $record->rid;
        $combo_field->flid = $field->flid;
        $combo_field->fid = $form->fid;
        $combo_field->save();

        $combo_field->addData([
            "[!f1!]1[!f1!][!f2!]Apple[!]Google[!f2!]",
            "[!f1!]2[!f1!][!f2!]Microsoft[!]Amazon[!f2!]",
            "[!f1!]3[!f1!][!f2!]Google[!]Sentient[!f2!]"
        ], Field::_NUMBER, Field::_GENERATED_LIST);

        $arg = Search::processArgument("Google", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped2($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument(2, Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped2($arg, Search::SEARCH_OR)->get()[0]->rid);
    }

    /**
     * Test keyword search.
     * @group search
     */
//    public function test_keywordSearch() {
//        $project = self::dummyProject();
//        $this->assertInstanceOf('App\Project', $project);
//
//        $form = self::dummyForm($project->pid);
//        $this->assertInstanceOf('App\Form', $form);
//
//        $field = self::dummyField("Combo List", $project->pid, $form->fid);
//        $this->assertInstanceOf('App\Field', $field);
//
//        $record = self::dummyRecord($project->pid, $form->fid);
//        $this->assertInstanceOf('App\Record', $record);
//
//        //
//        // Test all the fields that can be under a combo list.
//        // Namely, text, number, list, multi-select list, and generated list.
//        //
//
//        //
//        // Text, Number combination list.
//        //
//        $field->options = self::TEXT_NUM_OPTIONS;
//        $field->save();
//
//        $cmb_field = new \App\ComboListField();
//        $cmb_field->rid = $record->rid;
//        $cmb_field->flid = $field->flid;
//        $cmb_field->options = self::TEXT_NUM;
//        $cmb_field->save();
//
//        $args = ['LoReM'];
//        $this->assertTrue($cmb_field->keywordSearch($args, true));
//        $this->assertTrue($cmb_field->keywordSearch($args, false));
//
//        $args = ['9'];
//        $this->assertTrue($cmb_field->keywordSearch($args, true));
//        $this->assertTrue($cmb_field->keywordSearch($args, false));
//
//        $args = [2, null, -1, 0, ""];
//        $this->assertFalse($cmb_field->keywordSearch($args, true));
//        $this->assertFalse($cmb_field->keywordSearch($args, false));
//
//        $args = ["fring", "lao", "biben"]; // Partials
//        $this->assertTrue($cmb_field->keywordSearch($args, true));
//        $this->assertFalse($cmb_field->keywordSearch($args, false));
//
//        //
//        // List, Multi-select List
//        //
//        $field->options = self::LIST_MSL_OPTIONS;
//        $field->save();
//
//        $cmb_field->options = self::LIST_MSL;
//        $cmb_field->save();
//
//        $args = ['ChIcKeN'];
//        $this->assertTrue($cmb_field->keywordSearch($args, true));
//        $this->assertTrue($cmb_field->keywordSearch($args, false));
//
//        $args = ['maple'];
//        $this->assertTrue($cmb_field->keyworrdSearch($args, true));
//        $this->assertTrue($cmb_field->keywordSearch($args, false));
//
//        $args = ['elm'];
//        $this->assertTrue($cmb_field->keywordSearch($args, true));
//        $this->assertTrue($cmb_field->keywordSearch($args, false));
//
//        $args = ['cow']; // Option that can be selected in the list, but is not in any records.
//        $this->assertFalse($cmb_field->keywordSearch($args, true));
//        $this->assertFalse($cmb_field->keywordSearch($args, false));
//
//        $args = ['icken', 'phin', 'aple', 'edar']; // Partials
//        $this->assertTrue($cmb_field->keywordSearch($args, true));
//        $this->assertFalse($cmb_field->keywordSearch($args, false));
//
//        $args = ['[!]', null, 0, -1, 32418234.098];
//        $this->assertFalse($cmb_field->keywordSearch($args, true));
//        $this->assertFalse($cmb_field->keywordSearch($args, false));
//
//        //
//        // Multi-select List, Generated List
//        //
//        $field->options = self::MSL_GEN_OPTIONS;
//        $field->save();
//
//        $cmb_field->options = self::MSL_GEN;
//        $cmb_field->save();
//
//        $args = ['default(msl)'];
//        $this->assertTrue($cmb_field->keywordSearch($args, true));
//        $this->assertTrue($cmb_field->keywordSearch($args, false));
//
//        $args = ['default(gen)'];
//        $this->assertTrue($cmb_field->keywordSearch($args, true));
//        $this->assertTrue($cmb_field->keywordSearch($args, false));
//    }

    public function test_addData() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_COMBO_LIST, $project->pid, $form->fid);
        $r1 = self::dummyRecord($project->pid, $form->fid);

        $field->options = self::NUM_GEN_OPTIONS;
        $field->save();

        $c1 = new ComboListField();
        $c1->flid = $field->flid;
        $c1->rid = $r1->rid;
        $c1->save();

        $c1->addData([
            "[!f1!]1[!f1!][!f2!]Apple[!]Google[!f2!]",
            "[!f1!]2[!f1!][!f2!]Microsoft[!]Amazon[!f2!]",
            "[!f1!]3[!f1!][!f2!]Google[!]Sentient[!f2!]"
        ], Field::_NUMBER, Field::_GENERATED_LIST);

        $rid = DB::table("combo_support")->select("rid")
            ->where("data", "=", "Apple[!]Google")->get();

        $this->assertEquals($rid[0]->rid, $r1->rid);

        $rid = DB::table("combo_support")->select("rid")
            ->where("field_num", "=", 1)->where("number", "=", 2)->get();

        $this->assertEquals($rid[0]->rid, $r1->rid);

        $rid = DB::table("combo_support")->select("rid")
            ->whereRaw("MATCH (`data`) AGAINST (? IN BOOLEAN MODE)", ["Microsoft"])->get();

        $this->assertEquals($rid[0]->rid, $r1->rid);

        $rids = DB::table("combo_support")->select("rid")
            ->where("field_num", "=", 1)->whereBetween("number", [1,3])->get();

        $this->assertEquals(sizeof($rids), 3);
    }

    public function test_data() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_COMBO_LIST, $project->pid, $form->fid);
        $r1 = self::dummyRecord($project->pid, $form->fid);
        $r2 = self::dummyRecord($project->pid, $form->fid);

        $field->options = self::NUM_GEN_OPTIONS;
        $field->save();

        $c1 = new ComboListField();
        $c1->flid = $field->flid;
        $c1->rid = $r1->rid;
        $c1->fid = $field->fid;
        $c1->save();

        $c2 = new ComboListField();
        $c2->flid = $field->flid;
        $c2->rid = $r2->rid;
        $c2->fid = $field->fid;
        $c2->save();

        $c1->addData([
            "[!f1!]1[!f1!][!f2!]Apple[!]Google[!f2!]",
            "[!f1!]2[!f1!][!f2!]Microsoft[!]Amazon[!f2!]",
            "[!f1!]3[!f1!][!f2!]Google[!]Sentient[!f2!]"
        ], Field::_NUMBER, Field::_GENERATED_LIST);

        $c2->addData([
            "[!f1!]55[!f1!][!f2!]Techsmith[!]Google[!f2!]",
            "[!f1!]42[!f1!][!f2!]Google[!]Amazon[!f2!]",
        ], Field::_NUMBER, Field::_GENERATED_LIST);

        $data = $c1->data()->get();
        $this->assertEquals(sizeof($data), 6); // 2 * 3 rows returned.

        $data = $c2->data()->get();
        $this->assertEquals(sizeof($data), 4);
    }

    public function test_deleteData() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_COMBO_LIST, $project->pid, $form->fid);
        $r1 = self::dummyRecord($project->pid, $form->fid);

        $field->options = self::NUM_GEN_OPTIONS;
        $field->save();

        $c1 = new ComboListField();
        $c1->flid = $field->flid;
        $c1->rid = $r1->rid;
        $c1->fid = $field->fid;
        $c1->save();

        $c1->addData([
            "[!f1!]1[!f1!][!f2!]Apple[!]Google[!f2!]",
            "[!f1!]2[!f1!][!f2!]Microsoft[!]Amazon[!f2!]",
            "[!f1!]3[!f1!][!f2!]Google[!]Sentient[!f2!]"
        ], Field::_NUMBER, Field::_GENERATED_LIST);

        $this->assertEquals(3 * 2, $c1->data()->count());

        $c1->deleteData();

        $this->assertEquals(0, $c1->data()->count());
    }

    public function test_updateData() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_COMBO_LIST, $project->pid, $form->fid);
        $r1 = self::dummyRecord($project->pid, $form->fid);

        $field->options = self::NUM_GEN_OPTIONS;
        $field->save();

        $c1 = new ComboListField();
        $c1->flid = $field->flid;
        $c1->rid = $r1->rid;
        $c1->fid = $field->fid;
        $c1->save();

        $c1->addData([
            "[!f1!]1[!f1!][!f2!]Apple[!]Google[!f2!]",
            "[!f1!]2[!f1!][!f2!]Microsoft[!]Amazon[!f2!]",
            "[!f1!]3[!f1!][!f2!]Google[!]Sentient[!f2!]"
        ], Field::_NUMBER, Field::_GENERATED_LIST);

        $res = $c1->data()->get();

        $data = [];
        foreach($res as $elem) {
            $data[] = (is_null($elem->data)) ? $elem->number : $elem->data;
        }

        $elements = [1,2,3,"Apple[!]Google","Microsoft[!]Amazon", "Google[!]Sentient"];
        foreach($elements as $element) {
            $this->assertContains($element, $data);
        }

        $c1->updateData([
            "[!f1!]4[!f1!][!f2!]Apple[!]Google[!f2!]",
            "[!f1!]5[!f1!][!f2!]Microsoft[!]Uber[!f2!]",
            "[!f1!]3[!f1!][!f2!]Sentient[!f2!]"
            ], Field::_NUMBER, Field::_GENERATED_LIST);

        $res = $c1->data()->get();

        $data = [];
        foreach($res as $elem) {
            $data[] = (is_null($elem->data)) ? $elem->number : $elem->data;
        }

        $elements = [4,5,3,"Apple[!]Google","Microsoft[!]Uber", "Sentient"];
        foreach($elements as $element) {
            $this->assertContains($element, $data);
        }
    }

    public function test_getAdvancedSearchQuery() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $f1 = self::dummyField(Field::_COMBO_LIST, $project->pid, $form->fid);
        $r1 = self::dummyRecord($project->pid, $form->fid);

        //
        // Number, Generated Field.
        //
        $flid = $f1->flid;

        $f1->options = self::NUM_GEN_OPTIONS;
        $f1->save();

        $c1 = new ComboListField();
        $c1->flid = $f1->flid;
        $c1->rid = $r1->rid;
        $c1->save();

        $c1->addData([
            "[!f1!]1[!f1!][!f2!]Apple[!]Google[!f2!]",
            "[!f1!]2[!f1!][!f2!]Microsoft[!]Amazon[!f2!]",
            "[!f1!]3[!f1!][!f2!]Google[!]Sentient[!f2!]"
        ], Field::_NUMBER, Field::_GENERATED_LIST);

        $query = [ // Query: number is greater or equal to 2.
            $flid."_1_left" => "2",
            $flid."_1_right" => "",
            $flid."_1_valid" => "1",
            $flid."_2_valid" => "0"
        ];

        $q = $c1::getAdvancedSearchQuery($c1->flid, $query);
        $rid = $q->get();

        $this->assertEquals($rid[0]->rid, $r1->rid);

        $query = [ // Query: generated list contains Google.
            $flid."_1_valid" => 0,
            $flid."_2_valid" => 1,
            $flid."_2_input" => [
                0 => "Google"
            ]
        ];

        $q = $c1::getAdvancedSearchQuery($c1->flid, $query);
        $rid = $q->get();

        $this->assertEquals($rid[0]->rid, $r1->rid);

        $query = [ // Query: number equals 1 OR generated list contains Uber.
            $flid."_1_valid" => 1,
            $flid."_2_valid" => 1,
            $flid."_1_left" => 1,
            $flid."_1_right" => 1,
            $flid."_2_input" => [
                0 => "Uber"
            ],
            $flid."_operator" => "or"
        ];

        $q = $c1::getAdvancedSearchQuery($c1->flid, $query);
        $rid = $q->get();

        $this->assertEquals($rid[0]->rid, $r1->rid);

        $query = [ // Query: number equals 1 AND generated list contains Uber (should be empty).
            $flid."_1_valid" => 1,
            $flid."_2_valid" => 1,
            $flid."_1_left" => 1,
            $flid."_1_right" => 1,
            $flid."_2_input" => [
                0 => "Uber"
            ],
            $flid."_operator" => "and"
        ];

        $q = $c1::getAdvancedSearchQuery($c1->flid, $query);
        $result = $q->get();

        $this->assertEmpty($result);

        //
        // Text, Number.
        //
        $f2 = self::dummyField(Field::_COMBO_LIST, $project->pid, $form->fid);
        $r2 = self::dummyRecord($project->pid, $form->fid);

        $flid = $f2->flid;

        $f2->options = self::TEXT_NUM_OPTIONS;
        $f2->save();

        $c2 = new ComboListField();
        $c2->flid = $f2->flid;
        $c2->rid = $r2->rid;
        $c2->save();

        $c2->addData([
            "[!f1!]Raise a ruckus tonight.[!f1!][!f2!]9[!f2!]",
            "[!f1!]The quick brown fox jumped over the lazy dog.[!f1!][!f2!]100[!f2!]",
            "[!f1!]Evolution of Color Vision in Butterflies.[!f1!][!f2!]342[!f2!]"
        ], Field::_TEXT, Field::_NUMBER);

        $query = [ // Query: text contains "tonight".
            $flid."_1_valid" => 1,
            $flid."_2_valid" => 0,
            $flid."_1_input" => "tonight"
        ];

        $q = $c2::getAdvancedSearchQuery($c2->flid, $query);
        $rid = $q->get();

        $this->assertEquals($rid[0]->rid, $r2->rid);

        $query = [ // Query: number greater than 200 AND text contains "vision".
            $flid."_1_valid" => 1,
            $flid."_2_valid" => 1,
            $flid."_1_input" => "vision",
            $flid."_2_left" => 200,
            $flid."_2_right" => "",
            $flid."_operator" => "and"
        ];

        $q = $c2::getAdvancedSearchQuery($c2->flid, $query);
        $rid = $q->get();

        $this->assertEquals($rid[0]->rid, $r2->rid);

        $query = [ // Query: number less than 110 AND text contains "sphinx" (empty).
            $flid."_1_valid" => 1,
            $flid."_2_valid" => 1,
            $flid."_1_input" => "sphinx",
            $flid."_2_left" => "",
            $flid."_2_right" => 110,
            $flid."_operator" => "and"
        ];

        $q = $c2::getAdvancedSearchQuery($c2->flid, $query);
        $rid = $q->get();

        $this->assertEmpty($rid);

        $query = [ // Query: number less than 110 OR text contains "Albert".
            $flid."_1_valid" => 1,
            $flid."_2_valid" => 1,
            $flid."_1_input" => "Albert",
            $flid."_2_left" => "",
            $flid."_2_right" => 110,
            $flid."_operator" => "or"
        ];

        $q = $c2::getAdvancedSearchQuery($c2->flid, $query);
        $rid = $q->get();

        $this->assertEquals($rid[0]->rid, $r2->rid);

        //
        // Mutli Selection, Number.
        //
        $f3 = self::dummyField(Field::_COMBO_LIST, $project->pid, $form->fid);
        $r3 = self::dummyRecord($project->pid, $form->fid);

        $flid = $f3->flid;

        $f3->options = self::LIST_MSL_OPTIONS;
        $f3->save();

        $c3 = new ComboListField();
        $c3->flid = $f3->flid;
        $c3->rid = $r3->rid;
        $c3->save();

        $c3->addData([
            "[!f1!]Chicken[!f1!][!f2!]Attack[!]Strength[!]Defence[!f2!]",
            "[!f1!]Turkey[!f1!][!f2!]Slayer[!]Runecrafting[!]Farming[!f2!]",
            "[!f1!]Pork[!f1!][!f2!]Fishing[!]Woodcutting[!]Mining[!f2!]"
        ], Field::_LIST, Field::_MULTI_SELECT_LIST);


        $query = [ // Query: List is beef (empty).
            $flid."_1_valid" => 1,
            $flid."_1_input" => "beef",
            $flid."_2_valid" => 0
        ];

        $q = $c3::getAdvancedSearchQuery($flid, $query);
        $rid = $q->get();

        $this->assertEmpty($rid);

        $query = [ // Query: List is turkey.
            $flid."_1_valid" => 1,
            $flid."_1_input" => "turkey",
            $flid."_2_valid" => 0
        ];

        $q = $c3::getAdvancedSearchQuery($flid, $query);
        $rid = $q->get();

        $this->assertEquals($rid[0]->rid, $r3->rid);

        $query = [ // Query: Multi Select contains Farming.
            $flid."_1_valid" => 0,
            $flid."_2_input" => ["Farming"],
            $flid."_2_valid" => 1
        ];

        $q = $c3::getAdvancedSearchQuery($flid, $query);
        $rid = $q->get();

        $this->assertEquals($rid[0]->rid, $r3->rid);

        $query = [ // Query: List is beef OR Multi Select contains Attack or Fletching.
            $flid."_1_valid" => 1,
            $flid."_1_input" => "beef",
            $flid."_2_valid" => 1,
            $flid."_2_input" => [
                0 => "Attack",
                1 => "Fletching"
            ],
            $flid."_operator" => "or"
        ];

        $q = $c3::getAdvancedSearchQuery($flid, $query);
        $rid = $q->get();

        $this->assertEquals($rid[0]->rid, $r3->rid);

        $query = [ // Query: List is beef OR Multi Select contains Magic or Fletching (empty).
            $flid."_1_valid" => 1,
            $flid."_1_input" => "beef",
            $flid."_2_valid" => 1,
            $flid."_2_input" => [
                0 => "Magic",
                1 => "Fletching"
            ],
            $flid."_operator" => "or"
        ];

        $q = $c3::getAdvancedSearchQuery($flid, $query);
        $rid = $q->get();

        $this->assertEmpty($rid);

    }
}