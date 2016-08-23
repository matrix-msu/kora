<?php

use App\Http\Controllers\ExportController;

class ExportControllerTest extends TestCase
{
    /**
     * Test export with rids static function.
     */
    public function test_exportWithRids_JSON() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $rid = $record->rid;

        $text_field = new \App\TextField();
        $text_field->rid = $rid;
        $text_field->flid = 0;
        $text_field->text = FieldTest::TEXT_FIELD_DATA;
        $text_field->save();

        $rich_text_field = new \App\RichTextField();
        $rich_text_field->rid = $rid;
        $rich_text_field->flid = 0;
        $rich_text_field->rawtext = FieldTest::RICH_TEXT_FIELD_DATA;
        $rich_text_field->save();

        $number_field = new \App\NumberField();
        $number_field->rid = $rid;
        $number_field->flid = 0;
        $number_field->number = M_PI; // 3.1415926535898
        $number_field->save();

        $list_field = new App\ListField();
        $list_field->rid = $rid;
        $list_field->flid = 0;
        $list_field->option = "Durangus";
        $list_field->save();

        $msl_field = new \App\MultiSelectListField();
        $msl_field->rid = $rid;
        $msl_field->flid = 0;
        $msl_field->options = FieldTest::MULTI_SELECT_FIELD_DATA;
        $msl_field->save();

        $date_field = new \App\DateField();
        $date_field->rid = $rid;
        $date_field->flid = 0;
        $date_field->month = 10;
        $date_field->day = 9;
        $date_field->year = 1994;
        $date_field->era = "BCE";
        $date_field->circa = 0;
        $date_field->save();

        $schedule_field = new \App\ScheduleField();
        $schedule_field->rid = $rid;
        $schedule_field->flid = 0;
        $schedule_field->events = FieldTest::SCHEDULE_FIELD_DATA;
        $schedule_field->save();

        $geolocator_field = new \App\GeolocatorField();
        $geolocator_field->rid = $rid;
        $geolocator_field->flid = 0;
        $geolocator_field->locations = FieldTest::GEOLOCATOR_FIELD_DATA;
        $geolocator_field->save();

        $documents_field = new \App\DocumentsField();
        $documents_field->rid = $rid;
        $documents_field->flid = 0;
        $documents_field->documents = FieldTest::DOCUMENTS_FIELD_DATA;
        $documents_field->save();

        $gallery_field = new \App\GalleryField();
        $gallery_field->rid = $rid;
        $gallery_field->flid = 0;
        $gallery_field->images = FieldTest::GALLERY_FIELD_DATA;
        $gallery_field->save();

        $model_field = new \App\ModelField();
        $model_field->rid = $rid;
        $model_field->flid = 0;
        $model_field->model = FieldTest::MODEL_FIELD_DATA;
        $model_field->save();

        $playlist_field = new \App\PlaylistField();
        $playlist_field->rid = $rid;
        $playlist_field->flid = 0;
        $playlist_field->audio = FieldTest::PLAYLIST_FIELD_DATA;
        $playlist_field->save();

        $video_field = new \App\VideoField();
        $video_field->rid = $rid;
        $video_field->flid = 0;
        $video_field->video = FieldTest::VIDEO_FIELD_DATA;
        $video_field->save();

        $combo_list_field = new \App\ComboListField();
        $combo_list_field->rid = $rid;
        $combo_list_field->flid = 0;
        $combo_list_field->options = FieldTest::COMBO_LIST_FIELD_DATA;
        $combo_list_field->save();

        $associator = new \App\AssociatorField();
        $associator->rid = $rid;
        $associator->flid = 0;
        $associator->records = "";
        $associator->save();

        $filepath = ExportController::exportWithRids([$rid]);

        $this->assertEquals("json", pathinfo($filepath, PATHINFO_EXTENSION));

        var_dump(readfile($filepath));

        unlink($filepath);
        $this->assertFalse(file_exists($filepath));
    }
}