<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model {

    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    |
    | This model represents the data for a Form
    |
    */

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = [
        'project_id',
        'name',
        'description',
    ];

    protected $casts = [
        'layout' => 'array'
    ];

    /**
     * @var array - This is an array of field type values for creation
     */
    static public $validFieldTypes = [ //TODO::NEWFIELD
        'Text Fields' => array('Text' => 'Text'),
        //'Text Fields' => array('Text' => 'Text', 'Rich Text' => 'Rich Text', 'Integer' => 'Integer', 'Floating Point' => 'Floating Point'),
        //'List Fields' => array('List' => 'List', 'Multi-Select List' => 'Multi-Select List', 'Generated List' => 'Generated List', 'Combo List' => 'Combo List'),
        //'Date Fields' => array('Date' => 'Date', 'Schedule' => 'Schedule'),
        //'File Fields' => array('Documents' => 'Documents','Gallery' => 'Gallery (jpg, gif, png)','Playlist' => 'Playlist (mp3, wav)', 'Video' => 'Video (mp4)','3D-Model' => '3D-Model (obj, stl)'),
        //'Specialty Fields' => array('Geolocator' => 'Geolocator (latlon, utm, textual)','Associator' => 'Associator')
    ];

    /**
     * @var string - These are the possible field types at the moment  //TODO::NEWFIELD
     */
    const _TEXT = "Text";
//    const _RICH_TEXT = "Rich Text";
//    const _NUMBER = "Number";
//    const _LIST = "List";
//    const _MULTI_SELECT_LIST = "Multi-Select List";
//    const _GENERATED_LIST = "Generated List";
//    const _DATE = "Date";
//    const _SCHEDULE = "Schedule";
//    const _GEOLOCATOR = "Geolocator";
//    const _DOCUMENTS = "Documents";
//    const _GALLERY = "Gallery";
//    const _3D_MODEL = "3D-Model";
//    const _PLAYLIST = "Playlist";
//    const _VIDEO = "Video";
//    const _COMBO_LIST = "Combo List";
//    const _ASSOCIATOR = "Associator";

    /**
     * @var array - Maps field constant names to model name
     */
    public static $fieldModelMap = [ //TODO::NEWFIELD
        self::_TEXT => "TextField"
    ];

    /**
     * Returns the project associated with a form.
     *
     * @return BelongsTo
     */
    public function project() {
        return $this->belongsTo('App\Project', 'project_id');
    }

    /**
     * Returns the records associated with a form.
     *
     * @return HasMany
     */
    public function records() {
        return $this->hasMany('App\Record', 'form_id');
    }

    /**
     * Returns the form's admin group.
     *
     * @return BelongsTo
     */
    public function adminGroup() {
        return $this->belongsTo('App\FormGroup', 'adminGroup_id');
    }

    /**
     * Returns the form groups associated with a form.
     *
     * @return HasMany
     */
    public function groups() {
        return $this->hasMany('App\FormGroup', 'form_id');
    }

    /**
     * Returns the record revisions associated with a form.
     *
     * @return HasMany
     */
    public function revisions() {
        return $this->hasMany('App\Revision','form_id');
    }

    /**
     * Deletes all data belonging to the form, then deletes self.
     */
    public function delete() {
        $users = User::all();

        //Manually delete from custom
        foreach($users as $user) {
            $user->removeCustomForm($this->id);
        }

        //Delete other record related stuff before dropping records table
        //TODO::CASTLE

        //Drop the records table
        $rTable = new \CreateRecordsTable();
        $rTable->removeFormRecordsTable($this->id);

        parent::delete();
    }

    public function getFieldModel($type) {
        $modName = 'App\\KoraFields\\'.self::$fieldModelMap[$type];
        return new $modName();
    }
}
