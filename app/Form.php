<?php namespace App;

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
    const _RICH_TEXT = "Rich Text";
//    const _NUMBER = "Number";
//    const _LIST = "List";
//    const _MULTI_SELECT_LIST = "Multi-Select List";
//    const _GENERATED_LIST = "Generated List";
//    const _DATE = "Date";
//    const _SCHEDULE = "Schedule";
//    const _GEOLOCATOR = "Geolocator";
    const _DOCUMENTS = "Documents";
//    const _GALLERY = "Gallery";
//    const _3D_MODEL = "3D-Model";
//    const _PLAYLIST = "Playlist";
//    const _VIDEO = "Video";
//    const _COMBO_LIST = "Combo List";
//    const _ASSOCIATOR = "Associator";

    /**
     * @var array - This is an array of field type values for creation
     */
    static public $validFieldTypes = [ //TODO::NEWFIELD
        'Text Fields' => array(
            self::_TEXT => self::_TEXT,
            self::_RICH_TEXT => self::_RICH_TEXT,
        ),        'File Fields' => array(self::_DOCUMENTS => self::_DOCUMENTS),
        //'Text Fields' => array('Text' => 'Text', 'Rich Text' => 'Rich Text', 'Integer' => 'Integer', 'Floating Point' => 'Floating Point'),
        //'List Fields' => array('List' => 'List', 'Multi-Select List' => 'Multi-Select List', 'Generated List' => 'Generated List', 'Combo List' => 'Combo List'),
        //'Date Fields' => array('Date' => 'Date', 'Schedule' => 'Schedule'),
        //'File Fields' => array('Documents' => 'Documents','Gallery' => 'Gallery (jpg, gif, png)','Playlist' => 'Playlist (mp3, wav)', 'Video' => 'Video (mp4)','3D-Model' => '3D-Model (obj, stl)'),
        //'Specialty Fields' => array('Geolocator' => 'Geolocator (latlon, utm, textual)','Associator' => 'Associator')
    ];

    /**
     * @var array - This is an array of field types that can be filtered
     */
    static public $validFilterFields = [ //TODO::NEWFIELD See getDataFilters for which fields we support
        self::_TEXT,
        self::_RICH_TEXT
    ];

    /**
     * @var array - Maps field constant names to model name
     */
    public static $fieldModelMap = [ //TODO::NEWFIELD
        self::_TEXT => "TextField",
        self::_DOCUMENTS => "DocumentsField",
        self::_RICH_TEXT => "RichTextField"
    ];

    /**
     * @var array - Fields that need to be decoded coming out of the DB.
     */
    static public $jsonFields = [ //TODO::NEWFIELD
        self::_DOCUMENTS
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
    public function updateField($flid, $fieldArray, $newFlid=null) {
        $layout = $this->layout;

        //Update the field model
        $layout['fields'][$flid] = $fieldArray;

        //Update column name in DB and page structure
        if(!is_null($newFlid)) {
            $rTable = new \CreateRecordsTable();
            $rTable->renameColumn($this->id,$flid,$newFlid);

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
     * Updates a field within a form.
     */
    public function deleteField($flid) {
        $layout = $this->layout;

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
        //Revisions. Presets?
        //TODO::CASTLE

        //Drop the records table
        $rTable = new \CreateRecordsTable();
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

        //Get metadata
        if($filters['meta'])
            $fields = ['kid','legacy_kid','project_id','form_id','owner','created_at','updated_at'];
        else
            $fields = ['kid'];

        //Adds the data fields
        if(!is_array($filters['fields']) && $filters['fields'] == 'ALL')
            $flids = array_keys($this->layout['fields']);
        else
            $flids = $filters['fields'];

        //Get the real names of fields
        //Also check for json types
        if($filters['realnames']) {
            $realNames = [];
            foreach($flids as $flid) {
                $name = $flid.' as `'.$this->layout['fields'][$flid]['name'].'`';
                //We do this in realnames because the flid gets us the type to check if its JSON, but it will be compared against the DB result which will have real names instead of flid
                if(in_array($this->layout['fields'][$flid]['type'], self::$jsonFields))
                    $jsonFields[$name] = 1;
                array_push($realNames,$name);
            }
            $flids = $realNames;
        } else {
            foreach($flids as $flid) {
                if(in_array($this->layout['fields'][$flid]['type'], self::$jsonFields))
                    $jsonFields[$flid] = 1;
            }
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
            for($i=0;$i<sizeof($filters['sort']);$i = $i+2) {
                $orderBy .= $filters['sort'][$i].' '.$filters['sort'][$i+1].',';
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
                if(array_key_exists($column,$jsonFields)) //array key search is faster than in array so that's why we use it here
                    $result[$column] = json_decode($data,true);
                else
                    $result[$column] = $data;
            }
            $results[$row['kid']] = $result;
        }
        $records->free();

        $con->close();

        return $results;
    }

    /**
     * Gets the data out of the DB in Kora 2 format.
     *
     * @param  $filters - The filters to modify the returned results
     * @param  $rids - The subset of rids we would like back
     *
     * @return array - The Kora 2 formatted records
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

        $fields = ['kid','legacy_kid','updated_at','owner'];
        $fieldToModel = [];
        $fieldToRealName = [];

        //Adds the data fields
        if(!is_array($filters['fields']) && $filters['fields'] == 'ALL')
            $flids = array_keys($this->layout['fields']);
        else
            $flids = $filters['fields'];

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
            for($i=0;$i<sizeof($filters['sort']);$i = $i+2) {
                $orderBy .= $filters['sort'][$i].' '.$filters['sort'][$i+1].',';
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

                    $results[$kid][$fieldToRealName[$index]] = $fieldToModel[$index]->processLegacyData($value);
                }
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

        //$filters['revAssoc']; //TODO::CASTLE Need assoc first

        //Adds the data fields
        if(!is_array($filters['fields']) && $filters['fields'] == 'ALL')
            $flids = array_keys($this->layout['fields']);
        else
            $flids = $filters['fields'];

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
            for($i=0;$i<sizeof($filters['sort']);$i = $i+2) {
                $orderBy .= $filters['sort'][$i].' '.$filters['sort'][$i+1].',';
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

            $results .= '</Record>';
        }
        $records->free();

        $con->close();

        $results .= '</Records>';

        return $results;
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
    public function getTestRecordCount() {
        $recordMod = new Record(array(),$this->id);
        return $recordMod->newQuery()->where('is_test','=',1)->count();
    }

    /**
     * Scan tables to build out filters list
     *
     * @param  int $count - Minimum occurances required for a filter to return (Maybe reimplement later?)
     * @param  array $flids - Specifies the fields we need filters from
     * @param  array $rids - Record IDs to search for
     * @return array - The array of filters
     */
    public function getDataFilters($count, $flids, $rids=null) {
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
        $subset = '';
        if(!is_null($rids)) {
            if(empty($rids))
                return [];
            $ridString = implode(',',$rids);
            $subset = " WHERE `id` IN ($ridString)";
        }

        if($flids == 'ALL')
            $flids = array_keys($layout);

        //Validate the fields
        $valids = [];
        foreach($flids as $f) {
            $type = $layout[$f]['type'];
            if(in_array($type,self::$validFilterFields))
                $valids[] = $f;
        }

        //TODO::CASTLE to implement
        //$listOccurrences = "select `option`, `flid`, `rid` from ".$prefix."list_fields where $wherePiece $flidSQL";
        //$msListOccurrences = "select `options`, `flid`, `rid` from ".$prefix."multi_select_list_fields where $wherePiece $flidSQL";
        //$genListOccurrences = "select `options`, `flid`, `rid` from ".$prefix."generated_list_fields where $wherePiece $flidSQL";
        //$numberOccurrences = "select `number`, `flid`, `rid` from ".$prefix."number_fields where $wherePiece $flidSQL";
        //$dateOccurrences = "select `month`, `day`, `year`, `flid`, `rid` from ".$prefix."date_fields where $wherePiece $flidSQL";
        //$assocOccurrences = "select s.`flid`, r.`kid`, r.`rid` from ".$prefix."associator_support as s left join kora3_records as r on s.`record`=r.`rid` where s.$wherePiece and s.`flid` in ($flidString)";
        //$rAssocOccurrences = "select s.`flid`, r.`kid`, r.`rid` from ".$prefix."associator_support as s left join kora3_records as r on s.`rid`=r.`rid` where s.$wherePiece and s.`flid` in ($flidString)";

        foreach($valids as $f) {
            $filterQuery = "SELECT `$f`, COUNT(*) as count FROM $table$subset GROUP BY `$f`";
            $results = $con->query($filterQuery);
            while($row = $results->fetch_assoc()) {
                if(!is_null($row[$f]) && $row['count']>=$count)
                    $filters[$f][$row[$f]] = $row['count'];
            }
            $results->free();
        }

        mysqli_close($con);

        return $filters;
    }
}
