<?php

use App\Field as Field;
use App\Search as Search;
use Illuminate\Support\Collection;

class SearchTest extends TestCase
{
    /**
     * Test the show ignored arguments method.
     */
    public function test_showIgnoredArguments() {
        $string = "onomatopoeia"; // Something that is obviously not a stop word.
        $this->assertEmpty(Search::showIgnoredArguments($string)); // Nothing ignored by the search.

        $string = "and"; // Something that is obviously a stop word.
        $this->assertContains("and", Search::showIgnoredArguments($string)); // "and" should be ignored.

        $string = "onomatopoeia eldritch oowee"; // Multiple non-stop words.
        $this->assertEmpty(Search::showIgnoredArguments($string));

        $string = "and or is the was"; // Multiple stop words.
        $this->assertEquals(explode(" ", $string), Search::showIgnoredArguments($string)); // Everything ignored.

        $string = "and eldritch or onomatopoeia"; // Mixed input.

        $this->assertContains("and", Search::showIgnoredArguments($string));
        $this->assertContains("or", Search::showIgnoredArguments($string));
    }

    /**
     * Test the process argument static method.
     */
    public function test_processArgument() {
        $method = Search::SEARCH_OR;
        $argument = "hello";

        $this->assertEquals("hello*", Search::processArgument($argument, $method));

        $method = Search::SEARCH_AND;

        $this->assertEquals("hello*", Search::processArgument($argument, $method));

        $method = Search::SEARCH_EXACT;

        $this->assertEquals('"hello"', Search::processArgument($argument, $method));

        $method = Search::SEARCH_OR;
        $argument = "hello world";

        $this->assertEquals("hello* world*", Search::processArgument($argument, $method));

        $method = Search::SEARCH_AND;

        $this->assertEquals("hello* world*", Search::processArgument($argument, $method));

        $method = Search::SEARCH_EXACT;

        $this->assertEquals('"hello world"', Search::processArgument($argument, $method));
    }

//    /**
//     * Test the form keyword search method.
//     */
//    public function test_formKeywordSearch() {
//        //
//        // This method seems a little tough to test.
//        //
//        // We have to instantiate a search object, then pass the method a collection of typed fields
//        // and it should return the records that are associated with those fields based on the search method.
//        // We'll use text fields to keep things simple at first.
//        //
//        $project = self::dummyProject();
//        $form = self::dummyForm($project->pid);
//
//        $field1 = self::dummyField(Field::_TEXT, $project->pid, $form->fid);
//        $field2 = self::dummyField(Field::_TEXT, $project->pid, $form->fid);
//        $field3 = self::dummyField(Field::_TEXT, $project->pid, $form->fid);
//
//        $record1 = self::dummyRecord($project->pid, $form->fid);
//        $record2 = self::dummyRecord($project->pid, $form->fid);
//
//        $text_field1 = new App\TextField();
//        $text_field1->rid = $record1->rid;
//        $text_field1->flid = $field1->flid;
//        $text_field1->text = "bourgeois dinners audemars tickers";
//        $text_field1->save();
//
//        $text_field2 = new App\TextField();
//        $text_field2->rid = $record1->rid;
//        $text_field2->flid = $field2->flid;
//        $text_field2->text = "nine light years away just outside the Kepler solar system";
//        $text_field2->save();
//
//        $text_field3 = new App\TextField();
//        $text_field3->rid = $record1->rid;
//        $text_field3->flid = $field3->flid;
//        $text_field3->text = "the 2016 full loaded Dodge Durangus";
//        $text_field3->save();
//
//        $text_field4 = new App\TextField();
//        $text_field4->rid = $record2->rid;
//        $text_field4->flid = $field1->flid;
//        $text_field4->text = "recipe for disaster";
//        $text_field4->save();
//
//        $text_field5 = new App\TextField();
//        $text_field5->rid = $record2->rid;
//        $text_field5->flid = $field2->flid;
//        $text_field5->text = "asus g75vw";
//        $text_field5->save();
//
//        $text_field6 = new App\TextField();
//        $text_field6->rid = $record2->rid;
//        $text_field6->flid = $field3->flid;
//        $text_field6->text = "thunderstruck highway to hell let there be rock";
//        $text_field6->save();
//
//        // Test the OR search method.
//
//        $search = new Search($project->pid,
//            $form->fid,
//            "durangus",
//            Search::SEARCH_OR);
//
//        $results = $search->formKeywordSearch();
//        $result = $results->pop();
//
//        $this->assertEquals($result->rid, $record1->rid); // The proper record was found.
//
//        $search = new Search($project->pid,
//            $form->fid,
//            "disaster",
//            Search::SEARCH_OR);
//
//        $results = $search->formKeywordSearch();
//        $result = $results->pop();
//
//        $this->assertEquals($result->rid, $record2->rid);
//
//        $search = new Search($project->pid,
//            $form->fid,
//            "disaster something else",
//            Search::SEARCH_OR);
//
//        $results = $search->formKeywordSearch();
//        $result = $results->pop();
//
//        $this->assertEquals($result->rid, $record2->rid);
//
//        $search = new Search($project->pid,
//            $form->fid,
//            "nothing!",
//            Search::SEARCH_OR);
//
//        $results = $search->formKeywordSearch();
//
//        $this->assertEmpty($results);
//
//        // Test the AND search method.
//
//        $search = new Search($project->pid,
//            $form->fid,
//            "recipe asus thunderstruck",
//            Search::SEARCH_AND);
//
//        $results = $search->formKeywordSearch();
//        $result = $results->pop();
//
//        $this->assertEquals($result->rid, $record2->rid);
//
//        $search = new Search($project->pid,
//            $form->fid,
//            "recipe asus necronomicon",
//            Search::SEARCH_AND);
//
//        $results = $search->formKeywordSearch();
//
//        $this->assertEmpty($results); // No record has text fields will all three of the arguments between them.
//
//        $search = new Search($project->pid,
//            $form->fid,
//            "recipe dinner",
//            Search::SEARCH_AND);
//
//        $results = $search->formKeywordSearch();
//
//        $this->assertEmpty($results);
//
//        $search = new Search($project->pid,
//            $form->fid,
//            "nothing!",
//            Search::SEARCH_AND);
//
//        $results = $search->formKeywordSearch();
//
//        $this->assertEmpty($results);
//
//        // Test the EXACT search method.
//
//        $search = new Search($project->pid,
//            $form->fid,
//            "Kepler solar system",
//            Search::SEARCH_EXACT);
//
//        $results = $search->formKeywordSearch();
//        $result = $results->pop();
//
//        $this->assertEquals($record1->rid, $result->rid);
//
//        $search = new Search($project->pid,
//            $form->fid,
//            "dinner Kepler Durangus",
//            Search::SEARCH_EXACT);
//
//        $results = $search->formKeywordSearch();
//
//        $this->assertEmpty($results); // Record 1 has all these arguments but not in a continuous string in one field.
//
//        $search = new Search($project->pid,
//            $form->fid,
//            "nine Kepler system",
//            Search::SEARCH_EXACT);
//
//        $results = $search->formKeywordSearch();
//
//        $this->assertEmpty($results); // Record 1 has all these arguments in one field but not in a continuous string.
//
//        // Test searching for undesired information.
//
//        // Example, the info string for a document field has [Size] in it, we wouldn't want to return such a
//        // result if the user searches for "size".
//
//        $field4 = self::dummyField(Field::_DOCUMENTS, $project->pid, $form->fid);
//
//        $doc_field = new App\DocumentsField();
//        $doc_field->rid = $record1->rid;
//        $doc_field->flid = $field4->flid;
//        $doc_field->documents = "[Name]stealme.txt[Name][Size]40[Size][Type]text/plain[Type][!][Name]style.css[Name][Size]1544[Size][Type]text/css[Type][!][Name]lose.html[Name][Size]823[Size][Type]text/html[Type]";
//        $doc_field->save();
//
//        $search = new Search($project->pid,
//            $form->fid,
//            "stealme",
//            Search::SEARCH_OR);
//
//        $results = $search->formKeywordSearch();
//        $result = $results->pop();
//
//        $this->assertEquals($result->rid, $record1->rid);
//
//        $search = new Search($project->pid,
//            $form->fid,
//            "Type Size Name",
//            Search::SEARCH_OR);
//
//        $results = $search->formKeywordSearch();
//
//        $this->assertEmpty($results); // The arguments are in the info string, but the record isn't returned.
//
//        $field5 = self::dummyField(Field::_GEOLOCATOR, $project->pid, $form->fid);
//
//        $geo_field = new App\GeolocatorField();
//        $geo_field->rid = $record1->rid;
//        $geo_field->flid = $field5->flid;
//        $geo_field->locations = "[Desc]London, England[Desc][LatLon]12,122[LatLon][UTM]51P:391135.82662984,1326751.1707041[UTM][Address]  Helsinki Southern Finland[Address][!][Desc]Paris, France[Desc][LatLon]123,321[LatLon][UTM]24Z:500000,13678543.965109[UTM][Address] Vytauto A.  Panevezys County[Address][!][Desc]Cape Town, South Africa[Desc][LatLon]-70,30[LatLon][UTM]36D:385526.28525838,2231309.8903039[UTM][Address]  Caloocan [Address][!][Desc]New York City, United States of America[Desc][LatLon]1,1[LatLon][UTM]31N:277438.2635278,110597.9725227[UTM][Address]   Indiana[Address]";
//        $geo_field->save();
//
//        $search = new Search($project->pid,
//            $form->fid,
//            "London, England",
//            Search::SEARCH_OR);
//
//        $results = $search->formKeywordSearch();
//        $result = $results->pop();
//
//        $this->assertEquals($result->rid, $record1->rid);
//
//        $search = new Search($project->pid,
//            $form->fid,
//            "Helsinki Southern Finland",
//            Search::SEARCH_OR);
//
//        $results = $search->formKeywordSearch();
//        $result = $results->pop();
//
//        $this->assertEquals($result->rid, $record1->rid);
//
//        $search = new Search($project->pid,
//            $form->fid,
//            "Desc Address",
//            Search::SEARCH_OR);
//
//        $results = $search->formKeywordSearch();
//
//        $this->assertEmpty($results);
//    }

    /**
     * Test the new form keyword search method.
     */
    public function test_formKeywordSearch2() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);

        $field1 = self::dummyField(Field::_TEXT, $project->pid, $form->fid);
        $field2 = self::dummyField(Field::_TEXT, $project->pid, $form->fid);
        $field3 = self::dummyField(Field::_TEXT, $project->pid, $form->fid);

        $record1 = self::dummyRecord($project->pid, $form->fid);
        $record2 = self::dummyRecord($project->pid, $form->fid);

        $text_field1 = new App\TextField();
        $text_field1->rid = $record1->rid;
        $text_field1->flid = $field1->flid;
        $text_field1->text = "bourgeois dinners audemars tickers";
        $text_field1->save();

        $text_field2 = new App\TextField();
        $text_field2->rid = $record1->rid;
        $text_field2->flid = $field2->flid;
        $text_field2->text = "nine light years away just outside the Kepler solar system";
        $text_field2->save();

        $text_field3 = new App\TextField();
        $text_field3->rid = $record1->rid;
        $text_field3->flid = $field3->flid;
        $text_field3->text = "the 2016 full loaded Dodge Durangus";
        $text_field3->save();

        $text_field4 = new App\TextField();
        $text_field4->rid = $record2->rid;
        $text_field4->flid = $field1->flid;
        $text_field4->text = "recipe for disaster";
        $text_field4->save();

        $text_field5 = new App\TextField();
        $text_field5->rid = $record2->rid;
        $text_field5->flid = $field2->flid;
        $text_field5->text = "asus g75vw";
        $text_field5->save();

        $text_field6 = new App\TextField();
        $text_field6->rid = $record2->rid;
        $text_field6->flid = $field3->flid;
        $text_field6->text = "thunderstruck highway to hell let there be rock";
        $text_field6->save();


    }
}