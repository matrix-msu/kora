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

    /**
     * Test the text field portion of Field::keywordSearchTyped.
     */
    public function test_keywordSearchTyped_textField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_TEXT, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $text_field = new \App\TextField();
        $text_field->rid = $record->rid;
        $text_field->flid = $field->flid;
        $text_field->text = self::TEXT_FIELD_DATA;
        $text_field->save();

        $arg = "mucluc";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = new Collection();

        $results = $results->merge($field->keywordSearchTyped($arg)->get());
        $result = $results->pop();

        $this->assertInstanceOf("App\\TextField", $result);
        $this->assertContains("mucluc", $result->text);

        $arg = "mucluc transfuse lord gusty";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = new Collection();

        $results = $results->merge($field->keywordSearchTyped($arg)->get());
        $result = $results->pop();

        $this->assertInstanceOf("App\\TextField", $result);
        $this->assertContains("lord", $result->text);

        // Exact
        $arg = "nonmutability transfuse comprehensiveness";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = new Collection();

        $results = $results->merge($field->keywordSearchTyped($arg)->get());
        $result = $results->pop();

        $this->assertInstanceOf("App\\TextField", $result);
        $this->assertContains("nonmutability transfuse comprehensiveness", $result->text);

        // Try some partial matches.
        $arg = "resent"; // Will match resentment.
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = new Collection();

        $results = $results->merge($field->keywordSearchTyped($arg)->get());
        $result = $results->pop();

        $this->assertInstanceOf("App\\TextField", $result);
        $this->assertContains("transfuse", $result->text);

        // The same search will fail if we go for an exact match.
        $arg = "resent";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = new Collection();

        $results = $results->merge($field->keywordSearchTyped($arg)->get());
        $result = $results->pop();

        $this->assertNull($result);
    }

    /**
     * Test the rich text field portion of Field::keywordSearchTyped.
     *
     * The nuance with rich text is that the user should be able to search for things while neglecting the html tags.
     * So if the field has "<strong>hello</strong> world" and the user executes and exact search on "hello world" we
     * need to match that still.
     */
    public function test_keywordSearchTyped_richTextField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_RICH_TEXT, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        // Rich text uses boolean and natural language mode in its search so we have to prime the
        // natural language index with a few dummy lorem ipsum records.
        for ($i = 0; $i < 3; $i++) {
            $rt_field = new App\RichTextField();
            $rt_field->rid = $record->rid;
            $rt_field->flid = $field->flid;
            $rt_field->rawtext = self::LOREM;
            $rt_field->save();
        }

        $rt_field = new App\RichTextField();
        $rt_field->rid = $record->rid;
        $rt_field->flid = $field->flid;
        $rt_field->rawtext = self::RICH_TEXT_FIELD_DATA;
        $rt_field->save();

        $arg = "venison";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = new Collection();

        $results = $results->merge($field->keywordSearchTyped($arg)->get());
        $result = $results->pop();

        $this->assertInstanceOf("App\\RichTextField", $result);

        // Its kind of hard to actually find things with PHPUnit's functions...
        $pattern = "/(\\W|^)" . "venison" . "(\\W|$)/i";
        $match = preg_match($pattern, $result->rawtext);
        $this->assertTrue($match !== false);

        $arg = "vension leberkas ribs";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = new Collection();

        $results = $results->merge($field->keywordSearchTyped($arg)->get());
        $result = $results->pop();

        $this->assertInstanceOf("App\\RichTextField", $result);

        foreach(explode(" ", $arg) as $piece) {
            $pattern = "/(\\W|^)" . $piece . "(\\W|$)/i";
            $match = preg_match($pattern, $result->rawtext);
            $this->assertTrue($match !== false);
        }

        $arg = "large fish";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = new Collection();

        $results = $results->merge($field->keywordSearchTyped($arg)->get());
        $result = $results->pop();

        $this->assertInstanceOf("App\\RichTextField", $result);
    }

    /**
     * Test the number field portion of Field::keywordSearchTyped.
     *
     * Keyword search on a number field simply matches on equality.
     */
    public function test_keywordSearchTyped_numberField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_NUMBER, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $num_field = new \App\NumberField();
        $num_field->rid = $record->rid;
        $num_field->flid = $field->flid;
        $num_field->number = 0;
        $num_field->save();

        $results = new Collection();

        // Whole number tests

        $results = $results->merge($field->keywordSearchTyped(0)->get());
        $result = $results->pop();

        $this->assertInstanceOf("App\\NumberField", $result);
        $this->assertEquals($result->number, 0);

        $results = $results->merge($field->keywordSearchTyped(0.000000000000000000000000)->get());
        $result = $results->pop();

        $this->assertInstanceOf("App\\NumberField", $result);

        $num_field->number = -1;
        $num_field->save();

        $results = $results->merge($field->keywordSearchTyped(-1)->get());
        $result = $results->pop();

        $this->assertInstanceOf("App\\NumberField", $result);

        $results = $results->merge($field->keywordSearchTyped(-1.00000000000000000000000000000)->get());
        $result = $results->pop();

        $this->assertInstanceOf("App\\NumberField", $result);

        $num_field->number = 1;
        $num_field->save();

        $results = $results->merge($field->keywordSearchTyped(1)->get());
        $result = $results->pop();

        $this->assertInstanceOf("App\\NumberField", $result);

        $results = $results->merge($field->keywordSearchTyped(1.000000000000000000000000000000)->get());
        $result = $results->pop();

        $this->assertInstanceOf("App\\NumberField", $result);

        // Common fractions.

        $num_field->number = 0.5;
        $num_field->save();

        $results = $results->merge($field->keywordSearchTyped(1/2)->get());
        $result = $results->pop();

        $this->assertInstanceOf("App\\NumberField", $result);

        $results = $results->merge($field->keywordSearchTyped(0.5000000000000000000000000000)->get());
        $result = $results->pop();

        $this->assertInstanceOf("App\\NumberField", $result);

        $num_field->number = 0.333333333333333333333333333333; // 30 places is the max precision for a decimal field.
        $num_field->save();

        $results = $results->merge($field->keywordSearchTyped(1/3)->get());
        $result = $results->pop();

        $this->assertInstanceOf("App\\NumberField", $result);

        // Irrationals

        $num_field->number = 3.141592653589793238462643383279;
        $num_field->save();

        $results = $results->merge($field->keywordSearchTyped(M_PI)->get());
        $result = $results->pop();

        $this->assertInstanceOf("App\\NumberField", $result);

        $num_field->number = 2.718281828459045235360287471352;
        $num_field->save();

        $results = $results->merge($field->keywordSearchTyped(M_E)->get());
        $result = $results->pop();

        $this->assertInstanceOf("App\\NumberField", $result);
    }

    /**
     * Test the list field portion of Field::keywordSearchTyped.
     *
     * Full text indexing seems like overkill for the list field because it is only ever going to be one(ish) word but its here anyway.
     */
    public function test_keywordSearchTyped_listField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_LIST, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $list_field = new \App\ListField();
        $list_field->rid = $record->rid;
        $list_field->flid = $field->flid;
        $list_field->option = "Durangus";
        $list_field->save();

        $arg = "not that";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertNull($result);

        $arg = "Durangus";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\ListField", $result);
    }

    /**
     * Test the multi-select list field portion of Field::keywordSearchTyped.
     */
    public function test_keywordSearchTyped_multiSelectListField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_MULTI_SELECT_LIST, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $msl_field = new \App\MultiSelectListField();
        $msl_field->rid = $record->rid;
        $msl_field->flid = $field->flid;
        $msl_field->options = self::MULTI_SELECT_FIELD_DATA;
        $msl_field->save();

        $arg = "Attack";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\MultiSelectListField", $result);

        $arg = "strength defence";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\MultiSelectListField", $result);

        // We don't want to search across options in an exact search.
        $arg = "strength ranged";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertNull($result);

        $arg = "attack strength defence ranged";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\MultiSelectListField", $result);

        $arg = "prayer";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertNull($result);
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
        $gen_field->options = self::MULTI_SELECT_FIELD_DATA;
        $gen_field->save();

        $arg = "Attack";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\GeneratedListField", $result);

        $arg = "strength defence";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\GeneratedListField", $result);

        // We don't want to search across options in an exact search.
        $arg = "strength ranged";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertNull($result);

        $arg = "attack strength defence ranged";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\GeneratedListField", $result);

        $arg = "prayer";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertNull($result);
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
        $date_field->month = 10;
        $date_field->day = 9;
        $date_field->year = 1994;
        $date_field->era = "BCE";
        $date_field->circa = 0;
        $date_field->save();

        $arg = "October";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\DateField", $result);
        $this->assertEquals(10, $result->month);

        $arg = "9";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\DateField", $result);
        $this->assertEquals(9, $result->day);

        $arg = 9;
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\DateField", $result);
        $this->assertEquals(9, $result->day);

        $arg = 94; // Should not match to 1994.
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertNull($result);

        $arg = 1994;
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();


        $this->assertInstanceOf("App\\DateField", $result);
        $this->assertEquals(1994, $result->year);

        $arg = "10 9 1994";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();


        $this->assertInstanceOf("App\\DateField", $result);
    }

    /**
     * Test the schedule field portion of Field::keywordSearchTyped.
     */
    public function test_keywordSearchTyped_scheduleField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_SCHEDULE, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $sched_field = new \App\ScheduleField();
        $sched_field->rid = $record->rid;
        $sched_field->flid = $field->flid;
        $sched_field->events = self::SCHEDULE_FIELD_DATA;
        $sched_field->save();

        $arg = "Hannah";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\ScheduleField", $result);

        $arg = "My Birthday";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\ScheduleField", $result);
        $this->assertEmpty($results); // Should have only gotten one thing.

        $arg = "Manila Major";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\ScheduleField", $result);
    }

    /**
     * Test the geolocator field portion of Field::keywordSearchTyped.
     */
    public function test_keywordSearchTyped_geolocatorField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_GEOLOCATOR, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $geo_field = new \App\GeolocatorField();
        $geo_field->rid = $record->rid;
        $geo_field->flid = $field->flid;
        $geo_field->locations = self::GEOLOCATOR_FIELD_DATA;
        $geo_field->save();

        $arg = "London";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\GeolocatorField", $result);

        // Actual data has a comma. This should still match though.
        $arg = "London England";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\GeolocatorField", $result);

        $arg = "Cape Town";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\GeolocatorField", $result);

        $arg = "Helsinki";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\GeolocatorField", $result);
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
        $doc_field->documents = self::DOCUMENTS_FIELD_DATA;
        $doc_field->save();

        $arg = "steal";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\DocumentsField", $result);

        $arg = "stealme";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\DocumentsField", $result);

        $arg = "stealme.txt";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\DocumentsField", $result);

        $arg = "stealme.txt";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\DocumentsField", $result);

        $arg = "lose.html";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\DocumentsField", $result);
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
        $gal_field->images = self::GALLERY_FIELD_DATA;
        $gal_field->save();

        $arg = "penguins";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\GalleryField", $result);

        $arg = "penguins.jpg";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\GalleryField", $result);

        $arg = "Jellyfish.jpg";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\GalleryField", $result);
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
        $mod_field->model = self::MODEL_FIELD_DATA;
        $mod_field->save();

        $arg = "airboat";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\ModelField", $result);

        $arg = "airboat.obj";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\ModelField", $result);

        $arg = "not in this";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();

        $this->assertEmpty($results);
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
        $play_field->audio = self::PLAYLIST_FIELD_DATA;
        $play_field->save();

        $arg = "Flaxen Hair";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\PlaylistField", $result);

        $arg = "Maid with the Flaxen Hair";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\PlaylistField", $result);

        $arg = "Kalimba.mp3";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\PlaylistField", $result);

        $arg = "Kalimba.mp3";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\PlaylistField", $result);
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
        $vid_field->video = self::VIDEO_FIELD_DATA;
        $vid_field->save();

        $arg = "sample video";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\VideoField", $result);

        $arg = "sample_number_one";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\VideoField", $result);

        $arg = "sample_number_one";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\VideoField", $result);

        $arg = "sample_number_one.mp4";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\VideoField", $result);

        $arg = "sample_number_one.mp4";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\VideoField", $result);
    }

    /**
     * Test the combo list field portion of Field::keywordSearchTyped.
     */
    public function test_keywordSearchTyped_comboListField() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_COMBO_LIST, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $combo_field = new App\ComboListField();
        $combo_field->rid = $record->rid;
        $combo_field->flid = $field->flid;
        $combo_field->options = self::COMBO_LIST_FIELD_DATA;
        $combo_field->save();

        $arg = "Dragon";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\ComboListField", $result);

        $arg = "Dragon Pickaxe";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\ComboListField", $result);

        $arg = "Dragon Pickaxe";
        $arg = Search::processArgument($arg, Search::SEARCH_EXACT);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\ComboListField", $result);

        $arg = "Warhammer";
        $arg = Search::processArgument($arg, Search::SEARCH_OR);

        $results = $field->keywordSearchTyped($arg)->get();
        $result = $results->pop();

        $this->assertInstanceOf("App\\ComboListField", $result);
    }
}