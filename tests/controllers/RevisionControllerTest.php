<?php

use App\Field;
use App\TextField;
use App\ListField;
use App\NumberField;
use App\RichTextField;
use App\Http\Controllers\RevisionController;

class RevisionControllerTest extends TestCase
{
    public function test_buildDataArray() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_TEXT, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        //
        // Text Field.
        //
        $text = "Randy Bo Bandy";

        $text_field = new TextField();
        $text_field->rid = $record->rid;
        $text_field->flid = $field->flid;
        $text_field->fid = $form->fid;
        $text_field->text = $text;
        $text_field->save();

        $data = json_decode(RevisionController::buildDataArray($record), true);

        $this->assertEquals($data[Field::_TEXT][$field->flid], $text);

        //
        // Rich Text Field.
        //
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_RICH_TEXT, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $rich_text = "This some rich text!";

        $rich_field = new RichTextField();
        $rich_field->rid = $record->rid;
        $rich_field->flid = $field->flid;
        $rich_field->fid = $form->fid;
        $rich_field->rawtext = $rich_text;
        $rich_field->save();

        $data = json_decode(RevisionController::buildDataArray($record), true);

        $this->assertEquals($data[Field::_RICH_TEXT][$field->flid], $rich_text);

        //
        // Number Field.
        //
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_NUMBER, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $field->options = "[!Unit!]m[!Unit!]";
        $field->save();

        $number = 6749.2;

        $number_field = new NumberField();
        $number_field->rid = $record->rid;
        $number_field->fid = $form->fid;
        $number_field->flid = $field->flid;
        $number_field->number = $number;
        $number_field->save();

        $data = json_decode(RevisionController::buildDataArray($record), true);

        $this->assertEquals($data[Field::_NUMBER][$field->flid]['number'], $number, '', 0.001);

        //
        // List Field.
        //
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_LIST, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $option = "Potato";

        $list_field = new ListField();
        $list_field->rid = $record->rid;
        $list_field->fid = $form->fid;
        $list_field->flid = $field->flid;
        $list_field->option = $option;
        $list_field->save();

        $data = json_decode(RevisionController::buildDataArray($record), true);

        $this->assertEquals($data[Field::_LIST][$field->flid], $option);

        //
        // Multi Select List Field.
        //
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_MULTI_SELECT_LIST, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $options = "Many[!]Options";

        $msl_field = new \App\MultiSelectListField();
        $msl_field->rid = $record->rid;
        $msl_field->fid = $form->fid;
        $msl_field->flid = $field->flid;
        $msl_field->options = $options;
        $msl_field->save();

        $data = json_decode(RevisionController::buildDataArray($record), true);

        $this->assertEquals($data[Field::_MULTI_SELECT_LIST][$field->flid], $options);

        //
        // Date Field.
        //
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_DATE, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $field->options = "[!Circa!]Yes[!Circa!][!Era!]Yes[!Era!][!Format!]YYYY/MM/DD[!Format!]";
        $field->save();

        $day = 1;
        $month = 2;
        $year = 206;

        $circa = 1;
        $era = "CE";

        $date_field = new \App\DateField();
        $date_field->rid = $record->rid;
        $date_field->flid = $field->flid;
        $date_field->fid = $form->fid;
        $date_field->day = $day;
        $date_field->month = $month;
        $date_field->year = $year;
        $date_field->circa = $circa;
        $date_field->era = $era;
        $date_field->save();

        $data = json_decode(RevisionController::buildDataArray($record), true);

        $this->assertEquals($data[Field::_DATE][$field->flid]['day'], $day);

        //
        // Schedule Field.
        //
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_SCHEDULE, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $schedule_field = new \App\ScheduleField();
        $schedule_field->rid = $record->rid;
        $schedule_field->flid = $field->flid;
        $schedule_field->fid = $form->fid;
        $schedule_field->save();

        $events = ["Chance: 11/15/2016 - 11/15/2016",
            "The Rapper: 11/16/2016 - 11/16/2016",
            "Crust: 11/17/2016 - 11/17/2016"];

        $schedule_field->addEvents($events);

        $data = json_decode(RevisionController::buildDataArray($record), true);

        $this->assertEquals($data[Field::_SCHEDULE][$field->flid], $events);

        //
        // Documents Field.
        //
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_DOCUMENTS, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $documents = "these are some fine documents!";

        $doc_field = new \App\DocumentsField();
        $doc_field->rid = $record->rid;
        $doc_field->fid = $form->fid;
        $doc_field->flid = $field->flid;
        $doc_field->documents = $documents;
        $doc_field->save();

        $data = json_decode(RevisionController::buildDataArray($record), true);

        $this->assertEquals($data[Field::_DOCUMENTS][$field->flid], $documents);

        //
        // Gallery Field.
        //
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_GALLERY, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $gallery = "these are some fine images!";

        $gal_field = new \App\GalleryField();
        $gal_field->rid = $record->rid;
        $gal_field->fid = $form->fid;
        $gal_field->flid = $field->flid;
        $gal_field->images = $gallery;
        $gal_field->save();

        $data = json_decode(RevisionController::buildDataArray($record), true);

        $this->assertEquals($data[Field::_GALLERY][$field->flid], $gallery);

        //
        // Model Field.
        //
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_3D_MODEL, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $model = "this is one fine model!";

        $model_field = new \App\ModelField();
        $model_field->rid = $record->rid;
        $model_field->fid = $form->fid;
        $model_field->flid = $field->flid;
        $model_field->model = $model;
        $model_field->save();

        $data = json_decode(RevisionController::buildDataArray($record), true);

        $this->assertEquals($data[Field::_3D_MODEL][$field->flid], $model);

        //
        // Playlist Field.
        //
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_PLAYLIST, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $playlist = "these are some hot tracks!";

        $playlist_field = new \App\PlaylistField();
        $playlist_field->rid = $record->rid;
        $playlist_field->fid = $form->fid;
        $playlist_field->flid = $field->flid;
        $playlist_field->audio = $playlist;
        $playlist_field->save();

        $data = json_decode(RevisionController::buildDataArray($record), true);

        $this->assertEquals($data[Field::_PLAYLIST][$field->flid], $playlist);

        //
        // Video Field.
        //
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_VIDEO, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $video = "some fine videos!";

        $video_field = new \App\VideoField();
        $video_field->rid = $record->rid;
        $video_field->fid = $form->fid;
        $video_field->flid = $field->flid;
        $video_field->video = $video;
        $video_field->save();

        $data = json_decode(RevisionController::buildDataArray($record), true);

        $this->assertEquals($data[Field::_VIDEO][$field->flid], $video);

        //
        // Associator Field.
        //
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_ASSOCIATOR, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $records = "some records!";

        $assoc_field = new \App\AssociatorField();
        $assoc_field->rid = $record->rid;
        $assoc_field->fid = $form->fid;
        $assoc_field->flid = $field->flid;
        $assoc_field->records = $records;
        $assoc_field->save();

        $data = json_decode(RevisionController::buildDataArray($record), true);

        $this->assertEquals($data[Field::_ASSOCIATOR][$field->flid], $records);

        //
        // Combo List Field.
        //
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_COMBO_LIST, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $combos = [
            "[!f1!]1[!f1!][!f2!]Apple[!]Google[!f2!]",
            "[!f1!]2[!f1!][!f2!]Microsoft[!]Amazon[!f2!]",
            "[!f1!]3[!f1!][!f2!]Google[!]Sentient[!f2!]"
        ];

        $combo_field = new \App\ComboListField();
        $combo_field->rid = $record->rid;
        $combo_field->fid = $form->fid;
        $combo_field->flid = $field->flid;
        $combo_field->save();

        $combo_field->addData($combos, Field::_NUMBER, Field::_GENERATED_LIST);

        $data = json_decode(RevisionController::buildDataArray($record), true);

        $this->assertEquals($data[Field::_COMBO_LIST][$field->flid], $combos);
    }

    /**
     * Test the wipe rollbacks static method.
     */
    public function test_wipeRollbacks() {
        $revision = new App\Revision();
        $revision->fid = 1;
        $revision->rollback = 1;
        $revision->save();

        $untouched = new App\Revision();
        $untouched->fid = 2;
        $untouched->rollback = 1;
        $untouched->save();

        $id = $revision->id;

        RevisionController::wipeRollbacks(1);

        $revision = App\Revision::where("id", "=", $id)->first();
        $this->assertFalse(!!$revision->rollback);

        $untouched = App\Revision::where("id", "=", $untouched->id)->first();
        $this->assertTrue(!!$untouched->rollback);
    }
}