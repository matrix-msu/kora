<?php

use App\Field;

/**
 * Class FieldTest
 * @group field
 */
class FieldTest extends TestCase
{
    /**
     * Test Field::getTypedField.
     */
    public function test_getTypedField() {
        $project = self::dummyProject();
        $this->assertInstanceOf('App\Project', $project);

        $form = self::dummyForm($project->pid);
        $this->assertInstanceOf('App\Form', $form);

        // Start by testing Text field.
        $field = self::dummyField(Field::_TEXT, $project->pid, $form->fid);
        $this->assertInstanceOf('App\Field', $field);

        $record = self::dummyRecord($project->pid, $form->fid);
        $this->assertInstanceOf('App\Record', $record);

        //
        // Test getting each typed field.
        //
        $text_field = new \App\TextField();
        $text_field->rid = $record->rid;
        $text_field->flid = $field->flid;
        $text_field->text = "";
        $text_field->save();

        $this->assertInstanceOf("App\\BaseField", $field->getTypedField($record->rid)); // Polymorphism!
        $this->assertInstanceOf("App\\TextField", $field->getTypedField($record->rid));

        // Test on a Rich Text Field.
        $field->type = Field::_RICH_TEXT;
        $field->save();

        $rt_field = new \App\RichTextField();
        $rt_field->rid = $record->rid;
        $rt_field->flid = $field->flid;
        $rt_field->rawtext = "";
        $rt_field->save();

        $this->assertInstanceOf("App\\BaseField", $field->getTypedField($record->rid));
        $this->assertInstanceOf("App\\RichTextField", $field->getTypedField($record->rid));

        // Test on a number field.
        $field->type = Field::_NUMBER;
        $field->save();

        $num_field = new \App\NumberField();
        $num_field->rid = $record->rid;
        $num_field->flid = $field->flid;
        $num_field->number = "";
        $num_field->save();

        $this->assertInstanceOf("App\\BaseField", $field->getTypedField($record->rid));
        $this->assertInstanceOf("App\\NumberField", $field->getTypedField($record->rid));

        // Test on a list field.
        $field->type = Field::_LIST;
        $field->save();

        $list_field = new \App\ListField();
        $list_field->rid = $record->rid;
        $list_field->flid = $field->flid;
        $list_field->option = "";
        $list_field->save();

        $this->assertInstanceOf("App\\BaseField", $field->getTypedField($record->rid));
        $this->assertInstanceOf("App\\ListField", $field->getTypedField($record->rid));

        // Test on a multi-select list.
        $field->type = Field::_MULTI_SELECT_LIST;
        $field->save();

        $msl_field = new \App\MultiSelectListField();
        $msl_field->rid = $record->rid;
        $msl_field->flid = $field->flid;
        $msl_field->options = "";
        $msl_field->save();

        $this->assertInstanceOf("App\\BaseField", $field->getTypedField($record->rid));
        $this->assertInstanceOf("App\\MultiSelectListField", $field->getTypedField($record->rid));

        // Test on a generated list.
        $field->type = Field::_GENERATED_LIST;
        $field->save();

        $gen_field = new \App\GeneratedListField();
        $gen_field->rid = $record->rid;
        $gen_field->flid = $field->flid;
        $gen_field->options = "";
        $gen_field->save();

        $this->assertInstanceOf("App\\BaseField", $field->getTypedField($record->rid));
        $this->assertInstanceOf("App\\GeneratedListField", $field->getTypedField($record->rid));

        // Test on a date field.
        $field->type = Field::_DATE;
        $field->save();

        $date_field = new \App\DateField();
        $date_field->rid = $record->rid;
        $date_field->flid = $field->flid;
        $date_field->month = "";
        $date_field->day = "";
        $date_field->year = "";
        $date_field->era = "";
        $date_field->circa = "";
        $date_field->save();

        $this->assertInstanceOf("App\\BaseField", $field->getTypedField($record->rid));
        $this->assertInstanceOf("App\\DateField", $field->getTypedField($record->rid));

        // Test on a schedule field.
        $field->type = Field::_SCHEDULE;
        $field->save();

        $sched_field = new \App\ScheduleField();
        $sched_field->rid = $record->rid;
        $sched_field->flid = $field->flid;
        $sched_field->events = "";
        $sched_field->save();

        $this->assertInstanceOf("App\\BaseField", $field->getTypedField($record->rid));
        $this->assertInstanceOf("App\\ScheduleField", $field->getTypedField($record->rid));

        // Test on a geolocator field.
        $field->type = Field::_GEOLOCATOR;
        $field->save();

        $geo_field = new \App\GeolocatorField();
        $geo_field->rid = $record->rid;
        $geo_field->flid = $field->flid;
        $geo_field->locations = "";
        $geo_field->save();

        $this->assertInstanceOf("App\\BaseField", $field->getTypedField($record->rid));
        $this->assertInstanceOf("App\\GeolocatorField", $field->getTypedField($record->rid));

        // Test on a documents field.
        $field->type = Field::_DOCUMENTS;
        $field->save();

        $doc_field = new \App\DocumentsField();
        $doc_field->rid = $record->rid;
        $doc_field->flid = $field->flid;
        $doc_field->documents = "";
        $doc_field->save();

        $this->assertInstanceOf("App\\BaseField", $field->getTypedField($record->rid));
        $this->assertInstanceOf("App\\DocumentsField", $field->getTypedField($record->rid));

        // Test on a gallery.
        $field->type = Field::_GALLERY;
        $field->save();

        $gal_field = new \App\GalleryField();
        $gal_field->rid = $record->rid;
        $gal_field->flid = $field->flid;
        $gal_field->images = "";
        $gal_field->save();

        $this->assertInstanceOf("App\\BaseField", $field->getTypedField($record->rid));
        $this->assertInstanceOf("App\\GalleryField", $field->getTypedField($record->rid));

        // Test on a 3D Model field.
        $field->type = Field::_3D_MODEL;
        $field->save();

        $mod_field = new \App\ModelField();
        $mod_field->rid = $record->rid;
        $mod_field->flid = $field->flid;
        $mod_field->model = "";
        $mod_field->save();

        $this->assertInstanceOf("App\\BaseField", $field->getTypedField($record->rid));
        $this->assertInstanceOf("App\\ModelField", $field->getTypedField($record->rid));


        // Test on a playlist field.
        $field->type = Field::_PLAYLIST;
        $field->save();

        $play_field = new \App\PlaylistField();
        $play_field->rid = $record->rid;
        $play_field->flid = $field->flid;
        $play_field->audio = "";
        $play_field->save();

        $this->assertInstanceOf("App\\BaseField", $field->getTypedField($record->rid));
        $this->assertInstanceOf("App\\PlaylistField", $field->getTypedField($record->rid));

        // Test on a video field.
        $field->type = Field::_VIDEO;
        $field->save();

        $vid_field = new \App\VideoField();
        $vid_field->rid = $record->rid;
        $vid_field->flid = $field->flid;
        $vid_field->video = "";
        $vid_field->save();

        $this->assertInstanceOf("App\\BaseField", $field->getTypedField($record->rid));
        $this->assertInstanceOf("App\\VideoField", $field->getTypedField($record->rid));

        // Test on a combo list.
        $field->type = Field::_COMBO_LIST;
        $field->save();

        $cmb_field = new \App\ComboListField();
        $cmb_field->rid = $record->rid;
        $cmb_field->flid = $field->flid;
        $cmb_field->options = "";
        $cmb_field->ftype1 = "";
        $cmb_field->ftype2 = "";
        $cmb_field->save();

        $this->assertInstanceOf("App\\BaseField", $field->getTypedField($record->rid));
        $this->assertInstanceOf("App\\ComboListField", $field->getTypedField($record->rid));
    }
}