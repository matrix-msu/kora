<?php
use App\Record;
use Illuminate\Support\Facades\DB;

/**
 * Class RecordTest
 */
class RecordTest extends TestCase
{
    /**
     * Test the delete method.
     *
     * When a record is deleted all typed fields associated with it should be deleted.
     */
    public function test_delete() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $rid = $record->rid;

        //
        // Create one of each BaseField and make sure it gets deleted.
        //

        $text_field = new \App\TextField();
        $text_field->rid = $rid;
        $text_field->flid = 0;
        $text_field->text = "asdf";
        $text_field->save();

        $rich_text_field = new \App\RichTextField();
        $rich_text_field->rid = $rid;
        $rich_text_field->flid = 0;
        $rich_text_field->rawtext = "asdf";
        $rich_text_field->save();

        $number_field = new \App\NumberField();
        $number_field->rid = $rid;
        $number_field->flid = 0;
        $number_field->number = 0;
        $number_field->save();

        $list_field = new App\ListField();
        $list_field->rid = $rid;
        $list_field->flid = 0;
        $list_field->option = "asdf";
        $list_field->save();

        $msl_field = new \App\MultiSelectListField();
        $msl_field->rid = $rid;
        $msl_field->flid = 0;
        $msl_field->options = "asdf";
        $msl_field->save();

        $date_field = new \App\DateField();
        $date_field->rid = $rid;
        $date_field->flid = 0;
        $date_field->month = 0;
        $date_field->day = 0;
        $date_field->month = 0;
        $date_field->year = 0;
        $date_field->era = "era";
        $date_field->circa = 0;
        $date_field->save();

        $schedule_field = new \App\ScheduleField();
        $schedule_field->rid = $rid;
        $schedule_field->flid = 0;
        $schedule_field->save();

        $geolocator_field = new \App\GeolocatorField();
        $geolocator_field->rid = $rid;
        $geolocator_field->flid = 0;
        $geolocator_field->save();

        $documents_field = new \App\DocumentsField();
        $documents_field->rid = $rid;
        $documents_field->flid = 0;
        $documents_field->documents = "asdf";
        $documents_field->save();

        $gallery_field = new \App\GalleryField();
        $gallery_field->rid = $rid;
        $gallery_field->flid = 0;
        $gallery_field->images = "asdf";
        $gallery_field->save();

        $model_field = new \App\ModelField();
        $model_field->rid = $rid;
        $model_field->flid = 0;
        $model_field->model = "asdf";
        $model_field->save();

        $playlist_field = new \App\PlaylistField();
        $playlist_field->rid = $rid;
        $playlist_field->flid = 0;
        $playlist_field->audio = "asdf";
        $playlist_field->save();

        $video_field = new \App\VideoField();
        $video_field->rid = $rid;
        $video_field->flid = 0;
        $video_field->video = "asdf";
        $video_field->save();

        $combo_list_field = new \App\ComboListField();
        $combo_list_field->rid = $rid;
        $combo_list_field->flid = 0;
        $combo_list_field->save();

        $associator = new \App\AssociatorField();
        $associator->rid = $rid;
        $associator->flid = 0;
        $associator->records = "asdf";
        $associator->save();

        $record->delete();

        foreach(\App\Field::$ENUM_TYPED_FIELDS as $field_type) {
            $db_name = \App\BaseField::$MAPPED_FIELD_TYPES[$field_type];

            $this->assertEmpty(DB::table($db_name)->where("rid", "=", $rid)->get());
        }
    }

    /**
     * Test the is KID pattern method.
     */
    public function test_isKIDPattern() {
        $string = "1312-123-0";
        $this->assertTrue(Record::isKIDPattern($string));

        $string = "1-2-3";
        $this->assertTrue(Record::isKIDPattern($string));

        $string = "not even close";
        $this->assertFalse(Record::isKIDPattern($string));

        $string = "12-close!-400";
        $this->assertFalse(Record::isKIDPattern($string));
    }
}