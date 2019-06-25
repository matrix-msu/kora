<?php namespace App;

use App\Http\Controllers\RestfulBetaController;
use App\KoraFields\BaseField;
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
        'layout' => 'array',
    ];

    /**
     * @var string - These are the possible field types at the moment  //TODO::NEWFIELD
     */
    const _TEXT = "Text";
    const _BOOLEAN = "Boolean";
    const _RICH_TEXT = "Rich Text";
    const _INTEGER = "Integer";
    const _FLOAT = "Float";
    const _LIST = "List";
    const _MULTI_SELECT_LIST = "Multi-Select List";
    const _GENERATED_LIST = "Generated List";
    const _DATE = "Date";
    const _DATETIME = "DateTime";
    const _HISTORICAL_DATE = "Historical Date";
    const _GEOLOCATOR = "Geolocator";
    const _DOCUMENTS = "Documents";
    const _GALLERY = "Gallery";
    const _PLAYLIST = "Playlist";
    const _VIDEO = "Video";
    const _3D_MODEL = "3D-Model";
    const _COMBO_LIST = "Combo List";
    const _ASSOCIATOR = "Associator";

    /**
     * @var array - This is an array of field type values for creation
     */
    static public $validFieldTypes = [ //TODO::NEWFIELD
        'Text Fields' => array(
            self::_TEXT => self::_TEXT,
            self::_RICH_TEXT => self::_RICH_TEXT
        ),
        'Number Fields' => array(
            self::_INTEGER => self::_INTEGER,
            self::_FLOAT => self::_FLOAT
        ),
        'List Fields' => array(
            self::_LIST => self::_LIST,
            self::_MULTI_SELECT_LIST => self::_MULTI_SELECT_LIST,
            self::_GENERATED_LIST => self::_GENERATED_LIST,
            self::_COMBO_LIST => self::_COMBO_LIST
        ),
        'Date Fields' => array(
            self::_DATE => self::_DATE,
            self::_DATETIME => self::_DATETIME,
            self::_HISTORICAL_DATE => self::_HISTORICAL_DATE
        ),
        'File Fields' => array(
            self::_DOCUMENTS => self::_DOCUMENTS,
            self::_GALLERY => self::_GALLERY.' (jpg, gif, png)',
            self::_PLAYLIST => self::_PLAYLIST.' (mp3, wav)',
            self::_VIDEO => self::_VIDEO.' (mp4)',
            self::_3D_MODEL => self::_3D_MODEL.' (obj, stl)'
        ),
        'Specialty Fields' => array(
            self::_BOOLEAN => self::_BOOLEAN,
            self::_GEOLOCATOR => self::_GEOLOCATOR,
            self::_ASSOCIATOR => self::_ASSOCIATOR
        )
    ];

    /**
     * @var array - This is an array of field types that can be filtered
     *
     * NOTE: We currently support filter types of simple values, and JSON types that are simply an array of values
     */
    static public $validFilterFields = [ //TODO::NEWFIELD
        self::_TEXT,
        self::_BOOLEAN,
        self::_LIST,
        self::_MULTI_SELECT_LIST, //JSON Type
        self::_INTEGER,
        self::_FLOAT,
        self::_GENERATED_LIST, //JSON Type
        self::_DATE,
        self::_DATETIME,
        self::_ASSOCIATOR, //JSON Type
    ];

    /**
     * @var array - This is an array of field types that can be previewed in assoc
     */
    static public $validAssocFields = [ //TODO::NEWFIELD
        self::_TEXT,
        self::_LIST,
        self::_INTEGER,
        self::_FLOAT,
        self::_DATE,
        self::_DATETIME,
        self::_BOOLEAN,
    ];

    /**
     * @var array - Maps field constant names to model name
     */
    public static $fieldModelMap = [ //TODO::NEWFIELD
        self::_TEXT => "TextField",
        self::_BOOLEAN => "BooleanField",
        self::_RICH_TEXT => "RichTextField",
        self::_INTEGER => "IntegerField",
        self::_FLOAT => "FloatField",
        self::_LIST => "ListField",
        self::_MULTI_SELECT_LIST => "MultiSelectListField",
        self::_GENERATED_LIST => "GeneratedListField",
        self::_COMBO_LIST => "ComboListField",
        self::_DATE => "DateField",
        self::_DATETIME => "DateTimeField",
        self::_HISTORICAL_DATE => "HistoricalDateField",
        self::_DOCUMENTS => "DocumentsField",
        self::_GALLERY => "GalleryField",
        self::_PLAYLIST => "PlaylistField",
        self::_VIDEO => "VideoField",
        self::_3D_MODEL => "ModelField",
        self::_GEOLOCATOR => "GeolocatorField",
        self::_ASSOCIATOR => "AssociatorField"
    ];

    /**
     * @var array - Fields that need to be decoded coming out of the DB.
     */
    static public $jsonFields = [ //TODO::NEWFIELD
        self::_DOCUMENTS,
        self::_GALLERY,
        self::_PLAYLIST,
        self::_VIDEO,
        self::_3D_MODEL,
        self::_ASSOCIATOR,
        self::_MULTI_SELECT_LIST,
        self::_GENERATED_LIST,
        self::_GEOLOCATOR,
        self::_HISTORICAL_DATE
    ];

    /**
     * @var array - Fields that need their table updated when options updated.
     */
    static public $enumFields = [ //TODO::NEWFIELD
        self::_LIST
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
     * Returns the record revisions associated with a form.
     *
     * @return HasMany
     */
    public function recordPresets() {
        return $this->hasMany('App\RecordPreset','form_id');
    }

    /**
     * Determines if a form has any fields.
     *
     * @return bool - has fields
     */
    public function hasFields() {
        $layout = $this->layout;

        if(!empty($layout['fields']))
            return true;

        return false;
    }

    /**
     * Determines if a form has any fields that are advanced searchable.
     *
     * @return bool - has fields
     */
    public function hasAdvancedSearchFields() {
        $layout = $this->layout;

        foreach($layout['fields'] as $field) {
            if($field['advanced_search'])
                return true;
        }

        return false;
    }

    /**
     * Updates a field within a form. Potentially reindex field name.
     */
    public function updateField($flid, $fieldArray, $newFlid=null, $comboPrefix=array()) {
        $layout = $this->layout;

        //Update the field model
        $layout['fields'][$flid] = $fieldArray;

        //Update column name in DB and page structure
        if(!is_null($newFlid)) {
            $rTable = new \CreateRecordsTable();
            if ($comboPrefix) {
                $cTable = new \CreateRecordsTable($comboPrefix);
                $cTable->renameTable($this->id, $newFlid);
            }
            $rTable->renameColumn($this->id,$flid,$newFlid);

            // Updating new field name
            $layout['fields'][$newFlid] = $layout['fields'][$flid];
            unset($layout['fields'][$flid]);

            foreach($layout['pages'] as $index => $page) {
                $remainingFLIDS = [];
                foreach($page['flids'] as $f) {
                    if($f == $flid)
                        array_push($remainingFLIDS, $newFlid);
                    else
                        array_push($remainingFLIDS, $f);
                }
                $layout['pages'][$index]['flids'] = $remainingFLIDS;
            }
        }

        $this->layout = $layout;
        $this->save();
    }

    /**
     * Updates a field within a form within a combo list.
     */
    public function updateSubField($baseFlid, $flid, $newFlid=null) {

        //Update column name in DB
        if(!is_null($newFlid)) {
            $rTable = new \CreateRecordsTable(['tablePrefix' => $baseFlid]);
            $rTable->renameColumn($this->id,$flid,$newFlid);
        }

        $this->save();
    }

    /**
     * Deletes a field within a form.
     */
    public function deleteField($flid) {
        $layout = $this->layout;

        $type = $layout['fields'][$flid]['type'];

        //Remove from fields
        if(isset($layout['fields'][$flid]))
            unset($layout['fields'][$flid]);

        //Then from page structure
        foreach($layout['pages'] as $index => $page) {
            $remainingFLIDS = [];
            foreach($page['flids'] as $f) {
                if($f != $flid)
                    array_push($remainingFLIDS, $f);
            }
            $layout['pages'][$index]['flids'] = $remainingFLIDS;
        }

        $this->layout = $layout;
        $this->save();

        //Remove table for combo list
        if ($type == Form::_COMBO_LIST) {
            $rTable = new \CreateRecordsTable(
                ['tablePrefix' => $flid]
            );
            $rTable->removeFormRecordsTable($this->id);
        }

        //Remove table column
        $rTable = new \CreateRecordsTable();
        $rTable->dropColumn($this->id,$flid);
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
        $this->revisions()->delete();
        $this->recordPresets()->delete();

        $rTable = new \CreateRecordsTable();

        //Determine and delete combolist tables
        $fields = $this->layout['fields'];
        foreach($fields as $flid => $field) {
            if($field['type'] == self::_COMBO_LIST)
                $rTable->deleteComboListTable($flid.$this->id);
        }

        //Drop the records table
        $rTable->removeFormRecordsTable($this->id);

        parent::delete();
    }

    /**
     * Returns the field type model.
     *
     * @return BaseField
     */
    public function getFieldModel($type) {
        $modName = 'App\\KoraFields\\'.self::$fieldModelMap[$type];
        return new $modName();
    }

    /**
     * Gets the data out of the DB.
     *
     * @param  $filters - The filters to modify the returned results
     * @param  $rids - The subset of rids we would like back
     *
     * @return array - The records
     */
    public function getRecordsForExport($filters, $rids = null) {
        $results = [];
        $jsonFields = [];
        $assocFields = [];
        $assocForms = [];
        $useAssoc = false;

        $con = mysqli_connect(
            config('database.connections.mysql.host'),
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database')
        );
        $prefix = config('database.connections.mysql.prefix');

        //We want to make sure we are doing things in utf8 for special characters
        if(!mysqli_set_charset($con, "utf8")) {
            printf("Error loading character set utf8: %s\n", mysqli_error($con));
            exit();
        }

        //BETA LINE
        if(!isset($filters['beta']))
            $filters['beta'] = false;
        else
            $betaMappings = array();

        //Some prep to make assoc searching faster
        if($filters['assoc']) {
            $useAssoc = true;
            $allowedAssocFields = [];
            foreach($filters['assocFlids'] as $fieldName) {
                if($filters['beta'])
                    $allowedAssocFields[] = RestfulBetaController::removeIllegalFieldCharacters($fieldName);
                else
                    $allowedAssocFields[] = fieldMapper($fieldName,$this->project_id,$this->id);
            }
            $assocSelect = "SELECT distinct(f.`id`), f.`layout` from ".$prefix."associations as a left join ".$prefix."forms as f on f.id=a.data_form where a.`assoc_form`=".$this->id;
            $theForms = $con->query($assocSelect);
            while($row = $theForms->fetch_assoc()) {
                //prep fields like rest of function does
                $aLayout = json_decode($row['layout'],true);
                $aJsonFields = [];

                //Get metadata
                if($filters['meta'])
                    $fields = ['kid','legacy_kid','project_id','form_id','owner','created_at','updated_at'];
                else
                    $fields = ['kid'];

                //Adds the data fields
                //Builds out order of fields based on page
                $flids = array();
                foreach($aLayout['pages'] as $page) {
                    $flids = array_merge($flids, $page['flids']);
                }

                //Get the real names of fields
                //Also check for json types
                if($filters['realnames']) {
                    $realNames = [];
                    foreach($flids as $flid) {
                        //Since this is mostly used on the API, we can force external view on fields
                        if(!$aLayout['fields'][$flid]['external_view'])
                            continue;
                        if($allowedAssocFields != 'ALL' && !in_array($flid,$allowedAssocFields))
                            continue;

                        $name = $flid.' as `'.$aLayout['fields'][$flid]['name'].'`';
                        //We do this in realnames because the flid gets us the type to check if its JSON, but it will be compared against the DB result which will have real names instead of flid
                        if(in_array($aLayout['fields'][$flid]['type'], self::$jsonFields))
                            $aJsonFields[$name] = 1;
                        array_push($realNames,$name);
                    }
                    $flids = $realNames;
                } else {
                    foreach($flids as $flid) {
                        //Since this is mostly used on the API, we can force external view on fields
                        if(!$aLayout['fields'][$flid]['external_view'])
                            continue;
                        if($allowedAssocFields != 'ALL' && !in_array($flid,$allowedAssocFields))
                            continue;

                        if(in_array($aLayout['fields'][$flid]['type'], self::$jsonFields))
                            $aJsonFields[$flid] = 1;
                    }
                }

                //Determine whether to return data
                $fields = array_merge($flids,$fields);
                $fieldString = implode(',',$fields);

                //Save it
                $assocForms[$row['id']] = [
                    'id' => $row['id'],
                    'layout' => json_decode($row['layout'],true),
                    'fieldString' => $fieldString,
                    'jsonFields' => $aJsonFields
                ];
            }
        }

        //Prep to make reverse associations faster
        if($filters['revAssoc']) {
            $reverseAssociations = $this->getReverseAssociationsMapping($con,$prefix);
        }

        //Get metadata
        if($filters['meta'])
            $fields = ['kid','legacy_kid','project_id','form_id','owner','created_at','updated_at'];
        else
            $fields = ['kid'];

        //Adds the data fields
        if(!is_array($filters['fields']) && $filters['fields'] == 'ALL') {
            //Builds out order of fields based on page
            $flids = array();
            foreach($this->layout['pages'] as $page) {
                $flids = array_merge($flids, $page['flids']);
            }
        } else {
            $flids = array();
            foreach($filters['fields'] as $fieldName) {
                if($filters['beta']) {
                    //This helps us remap back to the old field name if it had special characters
                    $tmpBetaName = RestfulBetaController::removeIllegalFieldCharacters($fieldName);
                    if($tmpBetaName!=$fieldName)
                        $betaMappings[$tmpBetaName] = $fieldName;
                    $flids[] = $tmpBetaName;
                } else
                    $flids[] = fieldMapper($fieldName,$this->project_id,$this->id);
            }
        }

        //Before assigning fields, prep merge if it exists
        $mergeMappings = [];
        if(array_key_exists('merge', $filters) && !is_null($filters['merge'])){
            foreach($filters['merge'] as $newName => $mergeFields) {
                foreach($mergeFields as $mergeField) {
                    $mergeFlid = fieldMapper($mergeField,$this->project_id,$this->id);
                    $mergeMappings[$mergeFlid] = $newName;
                }
            }
        }
        //Get the real names of fields
        //Also check for json types
        if($filters['realnames']) {
            $realNames = [];
            foreach($flids as $flid) {
                if(!empty($mergeMappings) && isset($mergeMappings[$flid])) {
                    $tmp = $mergeMappings[$flid];
                    $name = $flid . ' as `' . $tmp . '`';
                } else {
                    $tmp = $this->layout['fields'][$flid]['name'];
                    $name = $flid . ' as `' . $tmp . '`';
                }
                //We do this in realnames because the flid gets us the type to check if its JSON, but it will be compared against the DB result which will have real names instead of flid
                if(in_array($this->layout['fields'][$flid]['type'], self::$jsonFields))
                    $jsonFields[$tmp] = 1;
                if($this->layout['fields'][$flid]['type'] == self::_ASSOCIATOR)
                    $assocFields[$tmp] = 1;
                array_push($realNames,$name);
            }
            $flids = $realNames;
        } else {
            $realFlids = [];
            foreach($flids as $flid) {
                if(!empty($mergeMappings) && isset($mergeMappings[$flid])) {
                    $tmp = $mergeMappings[$flid];
                    $name = $flid . ' as `' . $tmp . '`';
                } else {
                    if($filters['beta'] && isset($betaMappings[$flid])) {
                        $tmp = $betaMappings[$flid];
                        $name = $flid . ' as `' . $tmp . '`';
                    } else {
                        $tmp = $flid;
                        $name = $tmp;
                    }
                }
                if(in_array($this->layout['fields'][$flid]['type'], self::$jsonFields))
                    $jsonFields[$tmp] = 1;
                if($this->layout['fields'][$flid]['type'] == self::_ASSOCIATOR)
                    $assocFields[$tmp] = 1;
                array_push($realFlids,$name);
            }
            $flids = $realFlids;
        }

        //Determine whether to return data
        if($filters['data'])
            $fields = array_merge($flids,$fields);
        $fieldString = implode(',',$fields);

        //Subset of rids?
        $subset = '';
        if(!is_null($rids)) {
            if(empty($rids))
                return [];
            $ridString = implode(',',$rids);
            $subset = " WHERE `id` IN ($ridString)";
        }

        //Add the sorts
        $orderBy = '';
        if(!is_null($filters['sort'])) {
            $orderBy = ' ORDER BY ';
            foreach($filters['sort'] as $sortRule) {
                foreach($sortRule as $flid => $order) {
                    //Used to protect SQL
                    if($filters['beta'])
                        $field = RestfulBetaController::removeIllegalFieldCharacters($flid);
                    else
                        $field = fieldMapper($flid,$this->project_id,$this->id);
                    $field = preg_replace("/[^A-Za-z0-9_]/", '', $field);
                    $orderBy .= "$field IS NULL, $field $order,";
                }
            }
            $orderBy = substr($orderBy, 0, -1); //Trim the last comma
        }

        //Limit the results
        $limitBy = '';
        if(!is_null($filters['count'])) {
            $limitBy = ' LIMIT '.$filters['count'];
            if(!is_null($filters['index']))
                $limitBy .= ' OFFSET '.$filters['index'];
        }

        $selectRecords = "SELECT $fieldString FROM ".$prefix."records_".$this->id.$subset.$orderBy.$limitBy;

        $records = $con->query($selectRecords);
        while($row = $records->fetch_assoc()) {
            $result = [];
            foreach($row as $column => $data) {
                if($useAssoc && array_key_exists($column,$assocFields)) {
                    $aKids = json_decode($data,true);
                    if($aKids !== NULL) {
                        foreach($aKids as $aKid) {
                            $parts = explode('-',$aKid);
                            $aForm = $assocForms[$parts[1]];

                            if($filters['beta'])
                                $result[$column]['value'][$aKid] = $this->getBetaAssocRecord($parts[2], $aForm, $con, $prefix);
                            else
                                $result[$column][$aKid] = $this->getAssocRecord($parts[2], $aForm, $con, $prefix);
                        }
                    }
                } else if(array_key_exists($column,$jsonFields)) { //array key search is faster than in array so that's why we use it here
                    if($filters['beta'])
                        $result[$column]['value'] = json_decode($data, true);
                    else
                        $result[$column] = json_decode($data, true);
                } else {
                    if($filters['beta'])
                        $result[$column]['value'] = $data;
                    else
                        $result[$column] = $data;
                }
            }

            if($filters['revAssoc']) {
                if(array_key_exists($row['kid'],$reverseAssociations))
                    $result['reverseAssociations'] = $reverseAssociations[$row['kid']];
                else
                    $result['reverseAssociations'] = [];
            }

            $results[$row['kid']] = $result;
        }
        $records->free();

        $con->close();

        return $results;
    }

    /**
     * Gets the data out of the DB for a single record.
     *
     * @param  $kid - Record ID
     * @param  $aForm - The form data
     * @param  $con - DB connection
     * @param  $prefix - DB table prefix
     *
     * @return array - The records
     */
    private function getAssocRecord($rid, $aForm, $con, $prefix) {
        $result = [];
        $jsonFields = $aForm['jsonFields'];
        $fieldString = $aForm['fieldString'];

        $selectRecords = "SELECT $fieldString FROM ".$prefix."records_".$aForm['id']." WHERE `id`=$rid";

        $records = $con->query($selectRecords);
        while($row = $records->fetch_assoc()) {
            foreach($row as $column => $data) {
                if(array_key_exists($column,$jsonFields)) //array key search is faster than in array so that's why we use it here
                    $result[$column] = json_decode($data,true);
                else
                    $result[$column] = $data;
            }
        }
        $records->free();

        return $result;
    }
    private function getBetaAssocRecord($rid, $aForm, $con, $prefix) {
        $result = [];
        $jsonFields = $aForm['jsonFields'];
        $fieldString = $aForm['fieldString'];

        $selectRecords = "SELECT $fieldString FROM ".$prefix."records_".$aForm['id']." WHERE `id`=$rid";

        $records = $con->query($selectRecords);
        while($row = $records->fetch_assoc()) {
            foreach($row as $column => $data) {
                if(array_key_exists($column,$jsonFields)) //array key search is faster than in array so that's why we use it here
                    $result[$column]['value'] = json_decode($data,true);
                else
                    $result[$column]['value'] = $data;
            }
        }
        $records->free();

        return $result;
    }

    /**
     * Gets the data out of the DB in kora 2 format.
     *
     * @param  $filters - The filters to modify the returned results
     * @param  $rids - The subset of rids we would like back
     *
     * @return array - The kora 2 formatted records
     */
    public function getRecordsForExportLegacy($filters, $rids = null) {
        $results = [];

        $con = mysqli_connect(
            config('database.connections.mysql.host'),
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database')
        );
        $prefix = config('database.connections.mysql.prefix');

        //We want to make sure we are doing things in utf8 for special characters
        if(!mysqli_set_charset($con, "utf8")) {
            printf("Error loading character set utf8: %s\n", mysqli_error($con));
            exit();
        }

        //BETA LINE
        if(!isset($filters['beta']))
            $filters['beta'] = false;
        else
            $betaMappings = array();

        $fields = ['kid','legacy_kid','updated_at','owner'];
        $fieldToModel = [];
        $fieldToRealName = [];

        //Prep to make reverse associations faster
        $reverseAssociations = $this->getReverseAssociationsMapping($con,$prefix,'KORA_OLD');

        //Adds the data fields
        if(!is_array($filters['fields']) && $filters['fields'] == 'ALL') {
            //Builds out order of fields based on page
            $flids = array();
            foreach($this->layout['pages'] as $page) {
                $flids = array_merge($flids, $page['flids']);
            }
        } else {
            $flids = array();
            foreach($filters['fields'] as $fieldName) {
                if($filters['beta']) {
                    //This helps us remap back to the old field name if it had special characters
                    $tmpBetaName = RestfulBetaController::removeIllegalFieldCharacters($fieldName);
                    if($tmpBetaName != $fieldName)
                        $betaMappings[$tmpBetaName] = $fieldName;
                    $flids[] = $tmpBetaName;
                } else
                    $flids[] = fieldMapper($fieldName,$this->project_id,$this->id);
            }
        }

        $fields = array_merge($flids,$fields);
        $fieldString = implode(',',$fields);

        //Store the models
        foreach($fields as $f) {
            if(!in_array($f,['kid','legacy_kid','updated_at','owner'])) {
                $fieldToModel[$f] = $this->getFieldModel($this->layout['fields'][$f]['type']);
                if($filters['under'])
                    $fieldToRealName[$f] = str_replace(' ','_',$this->layout['fields'][$f]['name']);
                else
                    $fieldToRealName[$f] = $this->layout['fields'][$f]['name'];
            }
        }

        //Subset of rids?
        $subset = '';
        if(!is_null($rids)) {
            if(empty($rids))
                return [];
            $ridString = implode(',',$rids);
            $subset = " WHERE `id` IN ($ridString)";
        }

        //Add the sorts
        $orderBy = '';
        if(!is_null($filters['sort'])) {
            $orderBy = ' ORDER BY ';
            foreach($filters['sort'] as $sortRule) {
                foreach($sortRule as $flid => $order) {
                    //Used to protect SQL
                    if($filters['beta'])
                        $field = RestfulBetaController::removeIllegalFieldCharacters($flid);
                    else
                        $field = fieldMapper($flid,$this->project_id,$this->id);
                    $field = preg_replace("/[^A-Za-z0-9_]/", '', $field);
                    $orderBy .= "$field IS NULL, $field $order,";
                }
            }
            $orderBy = substr($orderBy, 0, -1); //Trim the last comma
        }

        //Limit the results
        $limitBy = '';
        if(!is_null($filters['count'])) {
            $limitBy = ' LIMIT '.$filters['count'];
            if(!is_null($filters['index']))
                $limitBy .= ' OFFSET '.$filters['index'];
        }

        $selectRecords = "SELECT $fieldString FROM ".$prefix."records_".$this->id.$subset.$orderBy.$limitBy;

        $records = $con->query($selectRecords);
        while($row = $records->fetch_assoc()) {
            $kid = $row['kid'];
            $results[$kid] = [
                'kid' => $kid,
                'pid' => $this->project_id,
                'schemeID' => $this->id,
                'legacy_kid' => $row['legacy_kid'],
                'systimestamp' => $row['updated_at'],
                'recordowner' => $row['owner'],
            ];

            foreach($row as $index => $value) {
                if(!in_array($index,['kid','legacy_kid','updated_at','owner'])) {
                    if(is_null($value))
                       $value = '';

                    if($filters['beta'] && isset($betaMappings[$index]))
                        $results[$kid][$betaMappings[$index]] = $fieldToModel[$index]->processLegacyData($value);
                    else
                        $results[$kid][$fieldToRealName[$index]] = $fieldToModel[$index]->processLegacyData($value);
                }
            }

            if($filters['revAssoc']) {
                if(array_key_exists($kid,$reverseAssociations))
                    $results[$kid]['linkers'] = $reverseAssociations[$kid];
                else
                    $results[$kid]['linkers'] = [];
            }
        }
        $records->free();

        $con->close();

        return $results;
    }

    /**
     * Gets the data out of the DB in XML format.
     *
     * @param  $filters - The filters to modify the returned results
     * @param  $rids - The subset of rids we would like back
     *
     * @return string - The XML of records
     */
    public function getRecordsForExportXML($filters, $rids = null) {
        $results = '<?xml version="1.0" encoding="utf-8"?><Records>';

        $con = mysqli_connect(
            config('database.connections.mysql.host'),
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database')
        );
        $prefix = config('database.connections.mysql.prefix');

        //We want to make sure we are doing things in utf8 for special characters
        if(!mysqli_set_charset($con, "utf8")) {
            printf("Error loading character set utf8: %s\n", mysqli_error($con));
            exit();
        }

        $fields = ['kid'];
        $fieldToModel = [];

        //Prep to make reverse associations faster
        if($filters['revAssoc']) {
            $reverseAssociations = $this->getReverseAssociationsMapping($con,$prefix,'XML');
        }

        //Adds the data fields
        if(!is_array($filters['fields']) && $filters['fields'] == 'ALL') {
            //Builds out order of fields based on page
            $flids = array();
            foreach($this->layout['pages'] as $page) {
                $flids = array_merge($flids, $page['flids']);
            }
        } else {
            $flids = array();
            foreach($filters['fields'] as $fieldName) {
                $flids[] = fieldMapper($fieldName,$this->project_id,$this->id);
            }
        }

        $fields = array_merge($flids,$fields);
        $fieldString = implode(',',$fields);

        //Store the models
        foreach($fields as $f) {
            if($f!='kid')
                $fieldToModel[$f] = $this->getFieldModel($this->layout['fields'][$f]['type']);
        }

        //Subset of rids?
        $subset = '';
        if(!is_null($rids)) {
            if(empty($rids))
                return [];
            $ridString = implode(',',$rids);
            $subset = " WHERE `id` IN ($ridString)";
        }

        //Add the sorts
        $orderBy = '';
        if(!is_null($filters['sort'])) {
            $orderBy = ' ORDER BY ';
            foreach($filters['sort'] as $sortRule) {
                foreach($sortRule as $flid => $order) {
                    //Used to protect SQL
                    $field = fieldMapper($flid,$this->project_id,$this->id);
                    $field = preg_replace("/[^A-Za-z0-9_]/", '', $field);
                    $orderBy .= "$field IS NULL, $field $order,";
                }
            }
            $orderBy = substr($orderBy, 0, -1); //Trim the last comma
        }

        //Limit the results
        $limitBy = '';
        if(!is_null($filters['count'])) {
            $limitBy = ' LIMIT '.$filters['count'];
            if(!is_null($filters['index']))
                $limitBy .= ' OFFSET '.$filters['index'];
        }

        $selectRecords = "SELECT $fieldString FROM ".$prefix."records_".$this->id.$subset.$orderBy.$limitBy;

        $records = $con->query($selectRecords);
        while($row = $records->fetch_assoc()) {
            $kid = $row['kid'];
            $results .= "<Record kid='$kid'>";

            foreach($row as $index => $value) {
                if($index != 'kid' && !is_null($value))
                    $results .= $fieldToModel[$index]->processXMLData($index, $value);
            }

            if($filters['revAssoc']) {
                $results .= '<reverseAssociations>';
                if(array_key_exists($row['kid'],$reverseAssociations)) {
                    $results .= implode('',$reverseAssociations[$row['kid']]);
                }
                $results .= '</reverseAssociations>';
            }

            $results .= '</Record>';
        }
        $records->free();

        $con->close();

        $results .= '</Records>';

        return $results;
    }

    /**
     * Get mapping of records to their reverse associations
     *
     * @param  $con - DB connection
     * @param  $prefix - DB table prefix
     * @param  $includeField - Include the flid index?
     * @return array - The mapped array
     */
    private function getReverseAssociationsMapping($con, $prefix, $type = 'JSON') {
        $return = [];

        $reverseSelect = "SELECT * FROM ".$prefix."reverse_associator_cache WHERE `associated_form_id`=".$this->id;
        $results = $con->query($reverseSelect);
        while($row = $results->fetch_assoc()) {
            switch($type) {
                case 'JSON':
                    $return[$row['associated_kid']][$row['source_flid']][] = $row['source_kid'];
                    break;
                case 'KORA_OLD':
                    $return[$row['associated_kid']][] = $row['source_kid'];
                    break;
                case 'XML':
                    $return[$row['associated_kid']][] = "<Record flid='".$row['source_flid']."'>".$row['source_kid']."</Record>";
                    break;
                default:
                    break;
            }
        }

        return $return;
    }

    /**
     * Get number of records in form.
     *
     * @return int
     */
    public function getRecordCount() {
        $recordMod = new Record(array(),$this->id);
        return $recordMod->newQuery()->count();
    }

    /**
     * Scan tables to build out filters list
     *
     * @param  int $count - Minimum occurances required for a filter to return (Maybe reimplement later?)
     * @param  array $fields - Specifies the fields we need filters from
     * @param  array $rids - Record IDs to search for
     * @return array - The array of filters
     */
    public function getDataFilters($count, $fields, $rids=null) {
        //Doing this for pretty much the same reason as keyword search above
        $con = mysqli_connect(
            config('database.connections.mysql.host'),
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database')
        );
        $prefix = config('database.connections.mysql.prefix');

        //We want to make sure we are doing things in utf8 for special characters
        if(!mysqli_set_charset($con, "utf8")) {
            printf("Error loading character set utf8: %s\n", mysqli_error($con));
            exit();
        }

        $layout = $this->layout['fields'];
        $table = $prefix.'records_'.$this->id;
        $filters = [];

        //Subset of rids?
        $subset = 'IS NOT NULL';
        if(!is_null($rids)) {
            if(empty($rids))
                return [];
            $ridString = implode(',',$rids);
            $subset .= " AND `id` IN ($ridString)";
        }

        //Validate the fields
        $valids = [];
        if($fields == 'ALL') {
            foreach(array_keys($layout) as $f) {
                $type = $layout[$f]['type'];
                if(in_array($type,self::$validFilterFields))
                    $valids[] = $f;
            }
        } else {
            foreach($fields as $fieldName) {
                $f = fieldMapper($fieldName,$this->project_id,$this->id);
                $type = $layout[$f]['type'];
                if(in_array($type,self::$validFilterFields))
                    $valids[] = $f;
            }
        }

        //Get filters for reverse associations
        $revFilterQuery = "SELECT `source_flid`, `source_kid`, COUNT(`associated_kid`) as count FROM `".$prefix."reverse_associator_cache` WHERE `associated_form_id`=$this->id AND `source_kid` IS NOT NULL GROUP BY `source_flid`, `source_kid`";
        $results = $con->query($revFilterQuery);
        while($row = $results->fetch_assoc()) {
            $filters['reverseAssociations'][$row['source_flid']][$row['source_kid']] = (int)$row['count'];
        }

        foreach($valids as $f) {
            $filterQuery = "SELECT `$f`, COUNT(*) as count FROM $table WHERE `$f` $subset GROUP BY `$f`";
            $results = $con->query($filterQuery);

            $isJson = false;
            $tmpJsonArray = [];

            while($row = $results->fetch_assoc()) {
                if(!is_array(json_decode($row[$f]))) {
                    if(!is_null($row[$f]) && $row['count'] >= $count)
                        $filters[$f][$row[$f]] = (int)$row['count'];
                } else {
                    //JSON so handle
                    $isJson = true;

                    $values = json_decode($row[$f]);
                    foreach($values as $val) {
                        //Check for initial assignment
                        if(!isset($tmpJsonArray[$val]))
                            $tmpJsonArray[$val] = (int)$row['count'];
                        else
                            $tmpJsonArray[$val] += $row['count'];
                    }
                }
            }

            //Clean up count limit for JSON
            if($isJson) {
                foreach($tmpJsonArray as $val => $cnt) {
                    if($cnt >= $count)
                        $filters[$f][$val] = $cnt;
                }
            }

            $results->free();
        }

        mysqli_close($con);

        return $filters;
    }
    public function getBetaDataFilters($count, $fields, $rids=null) {
        //Doing this for pretty much the same reason as keyword search above
        $con = mysqli_connect(
            config('database.connections.mysql.host'),
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database')
        );
        $prefix = config('database.connections.mysql.prefix');

        //We want to make sure we are doing things in utf8 for special characters
        if(!mysqli_set_charset($con, "utf8")) {
            printf("Error loading character set utf8: %s\n", mysqli_error($con));
            exit();
        }

        $layout = $this->layout['fields'];
        $table = $prefix.'records_'.$this->id;
        $filters = [];

        //Subset of rids?
        $subset = 'IS NOT NULL';
        if(!is_null($rids)) {
            if(empty($rids))
                return [];
            $ridString = implode(',',$rids);
            $subset .= " AND `id` IN ($ridString)";
        }

        //Validate the fields
        $valids = [];
        if($fields == 'ALL') {
            foreach(array_keys($layout) as $f) {
                $type = $layout[$f]['type'];
                if(in_array($type,self::$validFilterFields))
                    $valids[] = $f;
            }
        } else {
            foreach($fields as $f) {
                $type = $layout[$f]['type'];
                if(in_array($type,self::$validFilterFields))
                    $valids[] = $f;
            }
        }

        //Get filters for reverse associations
        $revFilterQuery = "SELECT `source_flid`, `source_kid`, COUNT(`associated_kid`) as count FROM `".$prefix."reverse_associator_cache` WHERE `associated_form_id`=$this->id AND `source_kid` IS NOT NULL GROUP BY `source_flid`, `source_kid`";
        $results = $con->query($revFilterQuery);
        while($row = $results->fetch_assoc()) {
            $filters['reverseAssociations'][$row['source_flid']][$row['source_kid']] = (int)$row['count'];
        }

        foreach($valids as $f) {
            $filterQuery = "SELECT `$f`, COUNT(*) as count FROM $table WHERE `$f` $subset GROUP BY `$f`";
            $results = $con->query($filterQuery);

            $isJson = false;
            $tmpJsonArray = [];

            while($row = $results->fetch_assoc()) {
                if(!is_array(json_decode($row[$f]))) {
                    if(!is_null($row[$f]) && $row['count'] >= $count)
                        $filters[$f][$row[$f]] = (int)$row['count'];
                } else {
                    //JSON so handle
                    $isJson = true;

                    $values = json_decode($row[$f]);
                    foreach($values as $val) {
                        //Check for initial assignment
                        if(!isset($tmpJsonArray[$val]))
                            $tmpJsonArray[$val] = (int)$row['count'];
                        else
                            $tmpJsonArray[$val] += $row['count'];
                    }
                }
            }

            //Clean up count limit for JSON
            if($isJson) {
                foreach($tmpJsonArray as $val => $cnt) {
                    if($cnt >= $count)
                        $filters[$f][$val] = $cnt;
                }
            }

            $results->free();
        }

        mysqli_close($con);

        return $filters;
    }

    /**
     * Sorts RIDs by fields.
     *
     * @param  array $forms - The Forms to sort in
     * @param  array $kids - The KIDs to sort
     * @param  array $sortFields - The field arrays to sort by
     * @param  array $mergeFields - The mappings of form fields to a single field name representation
     * @return array - The new array with sorted KIDs
     */
    public static function sortGlobalKids($forms, $kids, $sortFields, $mergeFields = null) {
        //get field
        $newOrderArray = array();
        $formSelects = array();

        //Doing this for pretty much the same reason as keyword search above
        $con = mysqli_connect(
            config('database.connections.mysql.host'),
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database')
        );
        $prefix = config('database.connections.mysql.prefix');

        //We want to make sure we are doing things in utf8 for special characters
        if(!mysqli_set_charset($con, "utf8")) {
            printf("Error loading character set utf8: %s\n", mysqli_error($con));
            exit();
        }

        //First we build the selects and unionize them
        foreach($forms as $index => $form) {
            $pieces = 'kid';
            $orderBy = ' ORDER BY ';
            foreach($sortFields as $sf) {
                foreach($sf as $key => $dir) {
                    if(!is_null($mergeFields) && isset($mergeFields->{$key})) { // AND in that merge array
                        $subField = $key;
                        $ogField = $mergeFields->{$key}[$index];
                        $ogFLID = fieldMapper($ogField,$form->project_id,$form->id);
                        //Used to protect SQL
                        $subField = preg_replace("/[^A-Za-z0-9_]/", '', $subField);
                        $ogFLID = preg_replace("/[^A-Za-z0-9_]/", '', $ogFLID);
                        $pieces .= ", `$ogFLID` as `$subField`";
                    } else {
                        $subField = $key;
                        //Used to protect SQL
                        $subField = preg_replace("/[^A-Za-z0-9_]/", '', $subField);
                        $pieces .= ", `$subField`";
                    }

                    $orderBy .= "`$subField` IS NULL, `$subField` $dir,";
                }
            }

            $select = "SELECT $pieces from ".$prefix."records_$form->id";
            $formSelects[] = $select;
        }

        $orderBy = substr($orderBy, 0, -1); //Trim the last comma
        $masterSelect = implode(' UNION ALL ', $formSelects);

        $results = $con->query($masterSelect.$orderBy);
        while($row = $results->fetch_assoc()) {
            if(in_array($row['kid'],$kids))
                $newOrderArray[] = $row['kid'];
        }
        $results->free();

        return $newOrderArray;
    }
}
