<?php

use App\Field;
use App\Search;
use Illuminate\Support\Collection;

/**
 * Class FieldTest
 * @group field
 */
class FieldTest extends TestCase
{
    /**
     * Some lorem ipsum text.
     */
    const LOREM = <<<TEXT
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras at quam eleifend, euismod libero sit amet, consequat tellus. Curabitur facilisis placerat orci, at dapibus metus dictum a. Aenean non malesuada libero. Etiam sit amet justo sapien. Sed aliquet erat et est accumsan feugiat. Donec a dapibus urna, ac mollis leo. Curabitur eleifend semper molestie. Curabitur id eleifend ex, quis egestas neque. Quisque finibus a nisl vel porta. Mauris cursus risus a sapien eleifend mattis. Quisque pretium risus ut felis consectetur, vitae lobortis est sollicitudin. Ut porta ultricies magna sit amet luctus. Fusce ipsum enim, dignissim ultricies nibh ac, sagittis imperdiet est. Nam ornare suscipit interdum. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.
TEXT;

    /** Data for a text field.
     * @var string
     */
    const TEXT_FIELD_DATA = <<<TEXT
Daled lastingly nonmutability transfuse comprehensiveness muntin alhambresque wardship axiopoenus unrationable undoped nonjudiciable elaterin precontrolling. Gleesomely coercer amusia lord uncontaminative beck nonconfirmatory bruiter galvanometric criminologically resentment brooklawn winded malory. Gusty volta catharistic ch''in textuaries precertified recodification rubric preexact dupondii kuibyshev epitrachelia jalor pendragon. Nottinghamshire preparental unshabbily overplenitude phytosociologist nonoffensive ruddleman kidnapper irrigating laddie brand pestle assuming remodulated. Draggy hardie nonaerating senegambia iconomatic subnucleuses recruitment ouija rearousing hebraized unobviated muclucing homologize underachieved.";
TEXT;

    /** Data for a rich text field.
     * @var string
     */
    const RICH_TEXT_FIELD_DATA = <<<TEXT
<ul>
	<li><em><strong>Bacon</strong> ipsum dolor amet </em><s>aliqua </s><em>aute shankle dolore <strong>turducken</strong>. </em></li>
	<li><em>Porchetta doner ea hamburger tri-tip in <strong>chuck </strong>picanha, occaecat turkey. </em></li>
	<li><em><strong>Ground </strong>round <strong>frankfurter</strong> sed, brisket ham corned beef excepteur cillum <strong>salami</strong> ex. </em></li>
</ul>

<blockquote>
<pre style="border: 1px solid rgb(204, 204, 204); padding: 5px 10px; background: rgb(238, 238, 238);">
Bresaola ribeye ullamco doner anim consectetur.
Strip steak commodo pork chop proident eiusmod.
In proident sausage velit, tri-tip cillum beef ribs lorem tenderloin kielbasa ex aute.</pre>
</blockquote>

<ol>
	<li>
	<h1><s><em>Turducken porchetta sunt irure. </em></s></h1>
	</li>
	<li>
	<h1><s><em>Voluptate sausage nulla, pancetta occaecat leberkas cupim jerky pork chop pariatur officia prosciutto lorem filet mignon aute.</em></s></h1>
	</li>
	<li>
	<h1><s><em>Incididunt lorem sed nisi, turkey chicken dolor elit laboris ham deserunt shoulder capicola. </em></s></h1>
	</li>
	<li>
	<h1><s><em>Venison shankle est filet mignon. </em></s></h1>
	</li>
	<li>
	<h1><s><em>Quis consectetur ex filet mignon, capicola leberkas ribeye nostrud beef ribs.</em></s></h1>
	</li>
</ol>
<p><b>large</b> fish</p>
<h1><strong>bright</strong> <i>lights</i> above</h1>
TEXT;

    /** Data for a multi-select field.
     * Also used for generated field.
     * @var string
     */
    const MULTI_SELECT_FIELD_DATA = "Attack[!]Strength[!]Defence[!]Ranged";

    /** Data for a schedule field.
     * @var string
     */
    const SCHEDULE_FIELD_DATA = "Manila Major: 06/02/2016 - 06/12/2016[!]Hannah's Birthday: 10/09/2016 - 10/09/2016[!]My Birthday: 01/25/2017 - 01/25/2017";

    /** Data for a geolocator field.
     * @var string
     */
    const GEOLOCATOR_FIELD_DATA = "[Desc]London, England[Desc][LatLon]12,122[LatLon][UTM]51P:391135.82662984,1326751.1707041[UTM][Address]Helsinki Southern Finland[Address][!][Desc]Paris, France[Desc][LatLon]123,321[LatLon][UTM]24Z:500000,13678543.965109[UTM][Address] Vytauto A.  Panevezys County[Address][!][Desc]Cape Town, South Africa[Desc][LatLon]-70,30[LatLon][UTM]36D:385526.28525838,2231309.8903039[UTM][Address]  Caloocan [Address][!][Desc]New York City, United States of America[Desc][LatLon]1,1[LatLon][UTM]31N:277438.2635278,110597.9725227[UTM][Address]   Indiana[Address]";

    /** Data for a documents field.
     * @var string
     */
    const DOCUMENTS_FIELD_DATA = "[Name]stealme.txt[Name][Size]40[Size][Type]text/plain[Type][!][Name]style.css[Name][Size]1544[Size][Type]text/css[Type][!][Name]lose.html[Name][Size]823[Size][Type]text/html[Type]";

    /** Data for a gallery field.
     * @var string
     */
    const GALLERY_FIELD_DATA = "[Name]Chrysanthemum.jpg[Name][Size]879394[Size][Type]image/jpeg[Type][!][Name]Desert.jpg[Name][Size]845941[Size][Type]image/jpeg[Type][!][Name]Hydrangeas.jpg[Name][Size]595284[Size][Type]image/jpeg[Type][!][Name]Jellyfish.jpg[Name][Size]775702[Size][Type]image/jpeg[Type][!][Name]Koala.jpg[Name][Size]780831[Size][Type]image/jpeg[Type][!][Name]Lighthouse.jpg[Name][Size]561276[Size][Type]image/jpeg[Type][!][Name]Penguins.jpg[Name][Size]777835[Size][Type]image/jpeg[Type][!][Name]Tulips.jpg[Name][Size]620888[Size][Type]image/jpeg[Type]";

    /** Data for a model field.
     * @var string
     */
    const MODEL_FIELD_DATA = "[Name]airboat.obj[Name][Size]308163[Size][Type]application/x-tgif[Type]";

    /** Data for a playlist field.
     * @var string
     */
    const PLAYLIST_FIELD_DATA = "[Name]Kalimba.mp3[Name][Size]8414449[Size][Type]audio/mpeg[Type][!][Name]Maid with the Flaxen Hair.mp3[Name][Size]4113874[Size][Type]audio/mpeg[Type][!][Name]Sleep Away.mp3[Name][Size]4842585[Size][Type]audio/mpeg[Type]";

    /** Data for a video field.
     * @var string
     */
    const VIDEO_FIELD_DATA = "[Name]sample_number_one.mp4[Name][Size]1055736[Size][Type]video/mp4[Type][!][Name]sample_number_two.mp4[Name][Size]21069678[Size][Type]video/mp4[Type]";

    /** Data for a combo field.
     * @var string
     */
    const COMBO_LIST_FIELD_DATA = "[!f1!]1[!f1!][!f2!]Dragon Warhammer[!f2!][!val!][!f1!]2[!f1!][!f2!]Dragon Pickaxe[!f2!][!val!][!f1!]3[!f1!][!f2!]Dragon Axe[!f2!][!val!][!f1!]4[!f1!][!f2!]Dragon Staff[!f2!]";

    /**
     * Test the delete method.
     *
     * When a field is deleted the following should be deleted:
     *      -Metadata associated with the project.
     *      -All BaseFields associated with the field.
     *          I.e., if a field is type "_TEXT" all TextFields with the Field's flid will be deleted.
     */
    public function test_delete() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);

        $field = self::dummyField(Field::_TEXT, $project->pid, $form->fid);
        $text_field = new \App\TextField();
        $text_field->rid = 0;
        $text_field->flid = $field->flid;
        $text_field->text = "asdf";
        $text_field->save();

        $flid = $field->flid;

        $field->delete();
        $this->assertEmpty(\App\TextField::where("flid", "=", $flid)->get());

        $field = self::dummyField(Field::_RICH_TEXT, $project->pid, $form->fid);
        $rich_text_field = new \App\RichTextField();
        $rich_text_field->rid = 0;
        $rich_text_field->flid = $field->flid;
        $rich_text_field->rawtext = "asdf";
        $rich_text_field->save();

        $flid = $field->flid;

        $field->delete();
        $this->assertEmpty(\App\RichTextField::where("flid", "=", $flid)->get());

        $field = self::dummyField(Field::_NUMBER, $project->pid, $form->fid);
        $number_field = new \App\NumberField();
        $number_field->rid = 0;
        $number_field->flid = $field->flid;
        $number_field->number = 0;
        $number_field->save();

        $flid = $field->flid;

        $field->delete();
        $this->assertEmpty(\App\NumberField::where("flid", "=", $flid)->get());

        $field = self::dummyField(Field::_LIST, $project->pid, $form->fid);
        $list_field = new App\ListField();
        $list_field->rid = 0;
        $list_field->flid = $field->flid;
        $list_field->option = "asdf";
        $list_field->save();

        $flid = $field->flid;

        $field->delete();
        $this->assertEmpty(\App\ListField::where("flid", "=", $flid)->get());

        $field = self::dummyField(Field::_MULTI_SELECT_LIST, $project->pid, $form->fid);
        $msl_field = new \App\MultiSelectListField();
        $msl_field->rid = 0;
        $msl_field->flid = $field->flid;
        $msl_field->options = "asdf";
        $msl_field->save();

        $flid = $field->flid;

        $field->delete();
        $this->assertEmpty(\App\MultiSelectListField::where("flid", "=", $flid)->get());

        $field = self::dummyField(Field::_DATE, $project->pid, $form->fid);
        $date_field = new \App\DateField();
        $date_field->rid = 0;
        $date_field->flid = $field->flid;
        $date_field->month = 0;
        $date_field->day = 0;
        $date_field->month = 0;
        $date_field->year = 0;
        $date_field->era = "era";
        $date_field->circa = 0;
        $date_field->save();

        $flid = $field->flid;

        $field->delete();
        $this->assertEmpty(\App\DateField::where("flid", "=", $flid)->get());

        $field = self::dummyField(Field::_SCHEDULE, $project->pid, $form->fid);
        $schedule_field = new \App\ScheduleField();
        $schedule_field->rid = 0;
        $schedule_field->flid = $field->flid;
        $schedule_field->save();

        $flid = $field->flid;

        $field->delete();
        $this->assertEmpty(\App\ScheduleField::where("flid", "=", $flid)->get());

        $field = self::dummyField(Field::_GEOLOCATOR, $project->pid, $form->fid);
        $geolocator_field = new \App\GeolocatorField();
        $geolocator_field->rid = 0;
        $geolocator_field->flid = $field->flid;
        $geolocator_field->save();

        $flid = $field->flid;

        $field->delete();
        $this->assertEmpty(\App\GeolocatorField::where("flid", "=", $flid)->get());

        $field = self::dummyField(Field::_DOCUMENTS, $project->pid, $form->fid);
        $documents_field = new \App\DocumentsField();
        $documents_field->rid = 0;
        $documents_field->flid = $field->flid;
        $documents_field->documents = "asdf";
        $documents_field->save();

        $flid = $field->flid;

        $field->delete();
        $this->assertEmpty(App\DocumentsField::where("flid", "=", $flid)->get());

        $field = self::dummyField(Field::_GALLERY, $project->pid, $form->fid);
        $gallery_field = new \App\GalleryField();
        $gallery_field->rid = 0;
        $gallery_field->flid = $field->flid;
        $gallery_field->images = "asdf";
        $gallery_field->save();

        $flid = $field->flid;

        $field->delete();
        $this->assertEmpty(App\DocumentsField::where("flid", "=", $flid)->get());

        $field = self::dummyField(Field::_3D_MODEL, $project->pid, $form->fid);
        $model_field = new \App\ModelField();
        $model_field->rid = 0;
        $model_field->flid = $field->flid;
        $model_field->model = "asdf";
        $model_field->save();

        $flid = $field->flid;

        $field->delete();
        $this->assertEmpty(App\ModelField::where("flid", "=", $flid)->get());

        $field = self::dummyField(Field::_PLAYLIST, $project->pid, $form->fid);
        $playlist_field = new \App\PlaylistField();
        $playlist_field->rid = 0;
        $playlist_field->flid = $field->flid;
        $playlist_field->audio = "asdf";
        $playlist_field->save();

        $flid = $field->flid;

        $field->delete();
        $this->assertEmpty(App\PlaylistField::where("flid", "=", $flid)->get());

        $field = self::dummyField(Field::_VIDEO, $project->pid, $form->fid);
        $video_field = new \App\VideoField();
        $video_field->rid = 0;
        $video_field->flid = $field->flid;
        $video_field->video = "asdf";
        $video_field->save();

        $flid = $field->flid;

        $field->delete();
        $this->assertEmpty(App\VideoField::where("flid", "=", $flid)->get());

        $field = self::dummyField(Field::_COMBO_LIST, $project->pid, $form->fid);
        $combo_list_field = new \App\ComboListField();
        $combo_list_field->rid = 0;
        $combo_list_field->flid = $field->flid;
        $combo_list_field->save();

        $flid = $field->flid;

        $field->delete();
        $this->assertEmpty(App\ComboListField::where("flid", "=", $flid)->get());

        $field = self::dummyField(Field::_ASSOCIATOR, $project->pid, $form->fid);
        $associator = new \App\AssociatorField();
        $associator->rid = 0;
        $associator->flid = $field->flid;
        $associator->records = "asdf";
        $associator->save();

        $flid = $field->flid;

        $field->delete();
        $this->assertEmpty(App\AssociatorField::where("flid", "=", $flid)->get());
    }

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
        $date_field->month = "1";
        $date_field->day = "1";
        $date_field->year = "1";
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
        $sched_field->save();

        $this->assertInstanceOf("App\\BaseField", $field->getTypedField($record->rid));
        $this->assertInstanceOf("App\\ScheduleField", $field->getTypedField($record->rid));

        // Test on a geolocator field.
        $field->type = Field::_GEOLOCATOR;
        $field->save();

        $geo_field = new \App\GeolocatorField();
        $geo_field->rid = $record->rid;
        $geo_field->flid = $field->flid;
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
        $cmb_field->save();

        $this->assertInstanceOf("App\\BaseField", $field->getTypedField($record->rid));
        $this->assertInstanceOf("App\\ComboListField", $field->getTypedField($record->rid));
    }

    /**
     * Test the text field portion of Field::keywordSearchTyped
     */
    public function test_keywordSearchTyped_textField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_TEXT, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $text_field = new \App\TextField();
        $text_field->rid = $record->rid;
        $text_field->fid = $form->fid;
        $text_field->flid = $field->flid;
        $text_field->text = self::TEXT_FIELD_DATA;
        $text_field->save();

        $arg = Search::processArgument("mucluc", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("mucluc transfuse lord gusty", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("nonmutability transfuse comprehensiveness", Search::SEARCH_EXACT);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("resent", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("resent", Search::SEARCH_EXACT);
        $this->assertEmpty($field->keywordSearchTyped($arg, Search::SEARCH_OR)->get());

        $arg = Search::processArgument("transfuse comprehensiveness muntin", Search::SEARCH_EXACT);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);
    }

    /**
     * Test the rich text field portion of Field::keywordSearchTyped.
     */
    public function test_keywordSearchTyped_richTextField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_RICH_TEXT, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $rt_field = new App\RichTextField();
        $rt_field->rid = $record->rid;
        $rt_field->flid = $field->flid;
        $rt_field->fid = $form->fid;
        $rt_field->rawtext = self::RICH_TEXT_FIELD_DATA;
        $rt_field->save();

        $arg = Search::processArgument("venison", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("vension leberkas ribs", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("large fish", Search::SEARCH_EXACT);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("bright lights above", Search::SEARCH_EXACT);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);
    }

    /**
     * Test the number field portion of Field::keywordSearchTyped.
     */
    public function test_keywordSearchTyped_numberField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_NUMBER, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $num_field = new \App\NumberField();
        $num_field->rid = $record->rid;
        $num_field->flid = $field->flid;
        $num_field->fid = $form->fid;
        $num_field->number = 0;
        $num_field->save();

        // Whole numbers
        $this->assertEquals($record->rid, $field->keywordSearchTyped(0, Search::SEARCH_OR)->get()[0]->rid);
        $this->assertEquals($record->rid, $field->keywordSearchTyped(0.0000000000000000000000000000000, Search::SEARCH_OR)->get()[0]->rid);

        $num_field->number = -1;
        $num_field->save();

        $this->assertEquals($record->rid, $field->keywordSearchTyped(-1, Search::SEARCH_OR)->get()[0]->rid);
        $this->assertEquals($record->rid, $field->keywordSearchTyped(-1.0000000000000000000000000000000, Search::SEARCH_OR)->get()[0]->rid);

        $num_field->number = 1;
        $num_field->save();

        $this->assertEquals($record->rid, $field->keywordSearchTyped(1, Search::SEARCH_OR)->get()[0]->rid);
        $this->assertEquals($record->rid, $field->keywordSearchTyped(1.0000000000000000000000000000000, Search::SEARCH_OR)->get()[0]->rid);

        // Rational numbers
        $num_field->number = 1/2;
        $num_field->save();

        $this->assertEquals($record->rid, $field->keywordSearchTyped(1/2, Search::SEARCH_OR)->get()[0]->rid);
        $this->assertEquals($record->rid, $field->keywordSearchTyped('0.5', Search::SEARCH_OR)->get()[0]->rid);
        $this->assertEquals($record->rid, $field->keywordSearchTyped(0.5000000000000000000000000000000, Search::SEARCH_OR)->get()[0]->rid);

        $num_field->number = 1/3;
        $num_field->save();

        $this->assertEmpty($field->keywordSearchTyped(0.3, Search::SEARCH_OR)->get());
        $this->assertEquals($record->rid, $field->keywordSearchTyped(1/3, Search::SEARCH_OR)->get()[0]->rid);
        $this->assertEquals($record->rid, $field->keywordSearchTyped(0.3333333333333333333333333333333, Search::SEARCH_OR)->get()[0]->rid);

        // Irrationals
        $num_field->number = 3.141592653589793238462643383279; // Pi to 30 places
        $num_field->save();

        $this->assertEmpty($field->keywordSearchTyped(3.1, Search::SEARCH_OR)->get());
        $this->assertEquals($record->rid, $field->keywordSearchTyped(M_PI, Search::SEARCH_OR)->get()[0]->rid);
        $this->assertEquals($record->rid, $field->keywordSearchTyped(3.1416, Search::SEARCH_OR)->get()[0]->rid);

        $num_field->number = 2.718281828459045235360287471352; // e to 30 places
        $num_field->save();

        $this->assertEmpty($field->keywordSearchTyped(2.7, Search::SEARCH_OR)->get());
        $this->assertEquals($record->rid, $field->keywordSearchTyped(M_E, Search::SEARCH_OR)->get()[0]->rid);
    }

    /**
     * Test the list field portion of Field::keywordSearchTyped.
     */
    public function test_keywordSearchTyped_listField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_LIST, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $list_field = new \App\ListField();
        $list_field->rid = $record->rid;
        $list_field->flid = $field->flid;
        $list_field->fid = $form->fid;
        $list_field->option = "Durangus";
        $list_field->save();

        $arg = Search::processArgument("nothing", Search::SEARCH_OR);
        $this->assertEmpty($field->keywordSearchTyped($arg, Search::SEARCH_OR)->get());

        $arg = Search::processArgument("Durangus", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);
    }

    /**
     * Test the list field portion of Field::keywordSearchTyped.
     */
    public function test_keywordSearchTyped_multiSelectListField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_MULTI_SELECT_LIST, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $msl_field = new \App\MultiSelectListField();
        $msl_field->rid = $record->rid;
        $msl_field->flid = $field->flid;
        $msl_field->fid = $form->fid;
        $msl_field->options = self::MULTI_SELECT_FIELD_DATA;
        $msl_field->save();

        $arg = Search::processArgument("Attack", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("strength defence", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("strength ranged", Search::SEARCH_EXACT);
        $this->assertEmpty($field->keywordSearchTyped($arg, Search::SEARCH_OR)->get());

        $arg = Search::processArgument("attack strength defence ranged", Search::SEARCH_EXACT);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("prayer", Search::SEARCH_EXACT);
        $this->assertEmpty($field->keywordSearchTyped($arg, Search::SEARCH_OR)->get());
    }

    /**
     * Test the generated list field portion of Field::keywordSearchTyped.
     */
    public function test_keywordSearchTyped_generatedListField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_GENERATED_LIST, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        // Should work exactly the same as multi-select list field.

        $gen_field = new \App\GeneratedListField();
        $gen_field->rid = $record->rid;
        $gen_field->flid = $field->flid;
        $gen_field->fid = $form->fid;
        $gen_field->options = self::MULTI_SELECT_FIELD_DATA;
        $gen_field->save();

        $arg = Search::processArgument("Attack", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("strength defence", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("strength ranged", Search::SEARCH_EXACT);
        $this->assertEmpty($field->keywordSearchTyped($arg, Search::SEARCH_OR)->get());

        $arg = Search::processArgument("attack strength defence ranged", Search::SEARCH_EXACT);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("prayer", Search::SEARCH_EXACT);
        $this->assertEmpty($field->keywordSearchTyped($arg, Search::SEARCH_OR)->get());
    }

    /**
     * Test the date field portion of Field::keywordSearchTyped.
     */
    public function test_keywordSearchTyped_dateField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_DATE, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $field->options = "[!Circa!]No[!Circa!][!Start!]1900[!Start!][!End!]2020[!End!][!Format!]MMDDYYYY[!Format!][!Era!]Off[!Era!]";
        $field->save();

        $date_field = new \App\DateField();
        $date_field->rid = $record->rid;
        $date_field->flid = $field->flid;
        $date_field->fid = $form->fid;
        $date_field->month = 10;
        $date_field->day = 9;
        $date_field->year = 1994;
        $date_field->era = "BCE";
        $date_field->circa = 0;
        $date_field->save();

        $arg = Search::processArgument("October", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument(9, Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("9", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument(94, Search::SEARCH_OR);
        $this->assertEmpty($field->keywordSearchTyped($arg, Search::SEARCH_OR)->get());

        $arg = Search::processArgument(1994, Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("10 9 1994", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);
    }

    /**
     * Test the geolocator field portion of Field::keywordSearchTyped.
     * See geolocator field test...
     */
    public function test_keywordSearchTyped_geolocatorField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_GEOLOCATOR, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $geo_field = new \App\GeolocatorField();
        $geo_field->rid = $record->rid;
        $geo_field->flid = $field->flid;
        $geo_field->fid = $form->fid;
        $geo_field->save();

        $geo_field->addLocations(explode("[!]", self::GEOLOCATOR_FIELD_DATA));

        $arg = Search::processArgument("London", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("London, England", Search::SEARCH_EXACT);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_EXACT)->get()[0]->rid);

        // Test if the separators are ignored.
        $arg = Search::processArgument("Desc", Search::SEARCH_EXACT);
        $this->assertEmpty($field->keywordSearchTyped($arg, Search::SEARCH_EXACT)->get());

        $arg = Search::processArgument("Address", Search::SEARCH_EXACT);
        $this->assertEmpty($field->keywordSearchTyped($arg, Search::SEARCH_EXACT)->get());

        $arg = Search::processArgument("Cape Town", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("Helsinki", Search::SEARCH_EXACT);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_EXACT)->get()[0]->rid);
    }

    /**
     * Test the documents field portion of Field::keywordSearchTyped.
     */
    public function test_keywordSearchTyped_documentsField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_DOCUMENTS, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $doc_field = new \App\DocumentsField();
        $doc_field->rid = $record->rid;
        $doc_field->flid = $field->flid;
        $doc_field->fid = $form->fid;
        $doc_field->documents = self::DOCUMENTS_FIELD_DATA;
        $doc_field->save();

        $arg = Search::processArgument("steal", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("stealme", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("stealme.txt", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("stealme", Search::SEARCH_EXACT);
        $this->assertEmpty($field->keywordSearchTyped($arg, Search::SEARCH_EXACT)->get());

        $arg = Search::processArgument("stealme.txt", Search::SEARCH_EXACT);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_EXACT)->get()[0]->rid);

        $arg = Search::processArgument("lose.html", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);
    }

    /**
     * Test the gallery field portion of Field::keywordSearchTyped.
     */
    public function test_keywordSearchTyped_galleryField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_GALLERY, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $gal_field = new \App\GalleryField();
        $gal_field->rid = $record->rid;
        $gal_field->flid = $field->flid;
        $gal_field->fid = $form->fid;
        $gal_field->images = self::GALLERY_FIELD_DATA;
        $gal_field->save();

        $arg = Search::processArgument("penguins", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("penguins.jpg", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("penguins", Search::SEARCH_EXACT);
        $this->assertEmpty($field->keywordSearchTyped($arg, Search::SEARCH_EXACT)->get());

        $arg = Search::processArgument("penguins.jpg", Search::SEARCH_EXACT);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_EXACT)->get()[0]->rid);

        $arg = Search::processArgument("Jellyfish.jpg", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("Jellyfish.jpg", Search::SEARCH_EXACT);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_EXACT)->get()[0]->rid);
    }

    /**
     * Test the model field portion of Field::keywordSearchTyped.
     */
    public function test_keywordSearchTyped_modelField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_3D_MODEL, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $mod_field = new \App\ModelField();
        $mod_field->rid = $record->rid;
        $mod_field->flid = $field->flid;
        $mod_field->fid = $form->fid;
        $mod_field->model = self::MODEL_FIELD_DATA;
        $mod_field->save();

        $arg = Search::processArgument("airboat", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("airboat.obj", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("airboat.obj", Search::SEARCH_EXACT);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_EXACT)->get()[0]->rid);


        $arg = Search::processArgument("nothing at all", Search::SEARCH_OR);
        $this->assertEmpty($field->keywordSearchTyped($arg, Search::SEARCH_OR)->get());
    }

    /**
     * Test the model field portion of Field::keywordSearchTyped.
     */
    public function test_keywordSearchTyped_playlistField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_PLAYLIST, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $play_field = new \App\PlaylistField();
        $play_field->rid = $record->rid;
        $play_field->flid = $field->flid;
        $play_field->fid = $form->fid;
        $play_field->audio = self::PLAYLIST_FIELD_DATA;
        $play_field->save();

        $arg = Search::processArgument("Flaxen Hair", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("Flaxen", Search::SEARCH_OR);

        $q =  $field->keywordSearchTyped($arg, Search::SEARCH_OR);
        $this->assertEquals($record->rid, $q->get()[0]->rid);

        $arg = Search::processArgument("Flaxen Hair", Search::SEARCH_EXACT);
        $this->assertEmpty($field->keywordSearchTyped($arg, Search::SEARCH_OR)->get());

        $arg = Search::processArgument("Maid with the Flaxen Hair", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("Maid with the Flaxen Hair.mp3", Search::SEARCH_EXACT);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_EXACT)->get()[0]->rid);

        $arg = Search::processArgument("Kalimba", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("Kalimba", Search::SEARCH_EXACT);
        $this->assertEmpty($field->keywordSearchTyped($arg, Search::SEARCH_EXACT)->get());

        $arg = Search::processArgument("Kalimba.mp3", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("Kalimba.mp3", Search::SEARCH_EXACT);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_EXACT)->get()[0]->rid);
    }

    /**
     * Test the video field portion of Field::keywordSearchTyped.
     */
    public function test_keywordSearchTyped_videoField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_VIDEO, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $vid_field = new \App\VideoField();
        $vid_field->rid = $record->rid;
        $vid_field->flid = $field->flid;
        $vid_field->fid = $form->fid;
        $vid_field->video = self::VIDEO_FIELD_DATA;
        $vid_field->save();

        $arg = Search::processArgument("sample video", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("sample video", Search::SEARCH_EXACT);
        $this->assertEmpty($field->keywordSearchTyped($arg, Search::SEARCH_EXACT)->get());

        $arg = Search::processArgument("sample_number_one", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("sample_number_one.mp4", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("sample_number_one.mp4", Search::SEARCH_EXACT);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_EXACT)->get()[0]->rid);
    }

    /**
     * Test the has metadata static function.
     */
    public function test_hasMetadata() {
        /*$project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_TEXT, $project->pid, $form->fid);

        $this->assertFalse(Field::hasMetadata($field->flid));

        $meta = new \App\Metadata();
        $meta->flid = $field->flid;
        $meta->save();

        $this->assertTrue(Field::hasMetadata($field->flid));

        // Try with a different flid (should be false).
        $this->assertFalse(Field::hasMetadata($field->flid + 1));*/
    }
}