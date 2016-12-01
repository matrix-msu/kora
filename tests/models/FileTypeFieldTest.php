<?php
use App\FileTypeField;

/**
 * Class FileTypeFieldTest
 * @group field
 */
class FileTypeFieldTest extends TestCase
{
    /**
     * Some example file type field strings to test on.
     * @type string
     */
    const DOCUMENT = <<<TEXT
[Name]whalenia%40msu.edu.csv[Name][Size]3478[Size][Type]text/csv[Type][!][Name]Proj_Layout_2016-02-18 18-08-58.xml[Name][Size]87[Size][Type]application/xml[Type][!][Name]postmessageRelay.html[Name][Size]4087[Size][Type]text/html[Type]
TEXT;
    const GALLERY = <<<TEXT
[Name]australian-shepherd.jpg[Name][Size]79169[Size][Type]image/jpeg[Type][!][Name]dog-rocking-out.jpg[Name][Size]7765[Size][Type]image/jpeg[Type][!][Name]puppy-with-ball.jpg[Name][Size]56996[Size][Type]image/jpeg[Type][!][Name]Untitled.jpg[Name][Size]181386[Size][Type]image/jpeg[Type]
TEXT;
    const PLAYLIST = <<<TEXT
[Name]SampleAudio_0.4mb.mp3[Name][Size]443926[Size][Type]audio/mpeg[Type][!][Name]SampleAudio_0.5mb.mp3[Name][Size]571258[Size][Type]audio/mpeg[Type][!][Name]SampleAudio_0.7mb.mp3[Name][Size]725240[Size][Type]audio/mpeg[Type]
TEXT;
    const VIDEO = <<<TEXT
[Name]SampleVideo_1280x720_1mb.mp4[Name][Size]1055736[Size][Type]video/mp4[Type][!][Name]SampleVideo_1280x720_2mb.mp4[Name][Size]2107842[Size][Type]video/mp4[Type]
TEXT;
    const MODEL = <<<TEXT
[Name]airboat.obj[Name][Size]308163[Size][Type]application/x-tgif[Type]
TEXT;

    /**
     * Test the file name parsing function.
     */
    public function test_getFileNames() {
        $project = self::dummyProject();
        $this->assertInstanceOf('App\Project', $project);

        $form = self::dummyForm($project->pid);
        $this->assertInstanceOf('App\Form', $form);

        $field = self::dummyField("Documents", $project->pid, $form->fid);
        $this->assertInstanceOf('App\Field', $field);

        $record = self::dummyRecord($project->pid, $form->fid);
        $this->assertInstanceOf('App\Record', $record);

        // Test on a documents field.
        $doc_field = new \App\DocumentsField();
        $doc_field->rid = $record->rid;
        $doc_field->flid = $field->flid;
        $doc_field->documents = self::DOCUMENT;
        $doc_field->save();

        $names = ['whalenia%40msu.edu.csv', 'Proj_Layout_2016-02-18 18-08-58.xml', 'postmessageRelay.html'];
        $this->assertEquals($names, $doc_field->getFileNames());

        $field->type = "Gallery";
        $field->save();

        // Test on a gallery field.
        $gal_field = new \App\GalleryField();
        $gal_field->rid = $record->rid;
        $gal_field->flid = $field->flid;
        $gal_field->images = self::GALLERY;
        $gal_field->save();

        $names = ['australian-shepherd.jpg', 'dog-rocking-out.jpg', 'puppy-with-ball.jpg', 'Untitled.jpg'];
        $this->assertEquals($names, $gal_field->getFileNames());

        $field->type = "Playlist";
        $field->save();

        // Test on a playlist field.
        $play_field = new \App\PlaylistField();
        $play_field->rid = $record->rid;
        $play_field->flid = $field->flid;
        $play_field->audio = self::PLAYLIST;
        $play_field->save();

        $names = ['SampleAudio_0.4mb.mp3', 'SampleAudio_0.5mb.mp3', 'SampleAudio_0.7mb.mp3'];
        $this->assertEquals($names, $play_field->getFileNames());

        $field->type = "Video";
        $field->save();

        // Test on a video field.
        $vid_field = new \App\VideoField();
        $vid_field->rid = $record->rid;
        $vid_field->flid = $field->flid;
        $vid_field->video = self::VIDEO;
        $vid_field->save();

        $names = ['SampleVideo_1280x720_1mb.mp4', 'SampleVideo_1280x720_2mb.mp4'];
        $this->assertEquals($names, $vid_field->getFileNames());

        $field->type = "3D-Model";
        $field->save();

        // Test on a 3d-model field.
        $mod_field = new \App\ModelField();
        $mod_field->rid = $record->rid;
        $mod_field->flid = $field->flid;
        $mod_field->model = self::MODEL;
        $mod_field->save();

        $names = ['airboat.obj'];
        $this->assertEquals($names, $mod_field->getFileNames());
    }

    /**
     * Test the keyword search functionality for a file type field.
     * @group search
     */
    public function test_keywordSearch() {
        $project = self::dummyProject();
        $this->assertInstanceOf('App\Project', $project);

        $form = self::dummyForm($project->pid);
        $this->assertInstanceOf('App\Form', $form);

        $field = self::dummyField("Documents", $project->pid, $form->fid);
        $this->assertInstanceOf('App\Field', $field);

        $record = self::dummyRecord($project->pid, $form->fid);
        $this->assertInstanceOf('App\Record', $record);

        $doc_field = new \App\DocumentsField();
        $doc_field->rid = $record->rid;
        $doc_field->flid = $field->flid;
        $doc_field->documents = self::DOCUMENT;
        $doc_field->save();

        $args = ['whalenia'];
        $this->assertTrue($doc_field->keywordSearch($args, true));
        $this->assertTrue($doc_field->keywordSearch($args, false)); // Non-partial search is not allowed on file type fields.

        $args = ['Proj_Layout_2016-02-18 18-08-58'];
        $this->assertTrue($doc_field->keywordSearch($args, true));

        $args = ['3478', '[Name]', '[!]'];
        $this->assertFalse($doc_field->keywordSearch($args, true));

        $field->type = "Gallery";
        $field->save();

        $gal_field = new \App\GalleryField();
        $gal_field->rid = $record->rid;
        $gal_field->flid = $field->flid;
        $gal_field->images = self::GALLERY;
        $gal_field->save();

        $args = ['australian-shepherd'];
        $this->assertTrue($gal_field->keywordSearch($args, true));

        $args = ['puppy-with-ball', 'untitled'];
        $this->assertTrue($gal_field->keywordSearch($args, true));

        $args = ['79169', '[Type]', -1, 0, null, false, true];
        $this->assertFalse($gal_field->keywordSearch($args, true));

        $field->type = "Playlist";
        $field->save();

        $play_field = new \App\PlaylistField();
        $play_field->rid = $record->rid;
        $play_field->flid = $field->flid;
        $play_field->audio = self::PLAYLIST;
        $play_field->save();

        $args = ['SampleAudio_0.4mb'];
        $this->assertTrue($play_field->keywordSearch($args, true));

        $args = [443926, '[Name]', true, false -1];
        $this->assertFalse($play_field->keywordSearch($args, true));

        $args = ['_0.4', '_0.5', '_0.7'];
        $this->assertTrue($play_field->keywordSearch($args, true));

        $field->type = "Video";
        $field->save();

        $vid_field = new \App\VideoField();
        $vid_field->rid = $record->rid;
        $vid_field->flid = $field->flid;
        $vid_field->video = self::VIDEO;
        $vid_field->save();

        $args = ['SampleVideo_1280x720_1mb.mp4'];
        $this->assertTrue($vid_field->keywordSearch($args, true));

        $args = ['1280x720', false, true, 0, -1, null];
        $this->assertTrue($vid_field->keywordSearch($args, true));

        $args = ['false', false, 'true', -1, '-1'];
        $this->assertFalse($vid_field->keywordSearch($args, true));

        $field->type = "3D-Model";
        $field->save();

        $mod_field = new \App\ModelField();
        $mod_field->rid = $record->rid;
        $mod_field->flid = $field->flid;
        $mod_field->model = self::MODEL;
        $mod_field->save();

        $args = ['airboat.obj'];
        $this->assertTrue($mod_field->keywordSearch($args, true));

        $args = ['[Name]', 'application/x-tgif', '[Type]', '[Size]'];
        $this->assertFalse($mod_field->keywordSearch($args, true));
    }

    public function test_processAdvancedSearchInput() {
        $input = "image.jpg";
        $processed = FileTypeField::processAdvancedSearchInput($input, true);
        $this->assertEquals("\"image.jpg[Name]\"", $processed);

        $processed = FileTypeField::processAdvancedSearchInput($input, false);
        $this->assertEquals("\"image.\"", $processed);

        $input = ".jpg";
        $processed = FileTypeField::processAdvancedSearchInput($input, true);
        $this->assertEquals("\".jpg[Name]\"", $processed);

        $processed = FileTypeField::processAdvancedSearchInput($input, false);
        $this->assertEquals("\".jpg[Name]\"", $processed);
    }
}