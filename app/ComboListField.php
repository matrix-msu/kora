<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RevisionController;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ComboListField extends BaseField {

    const SUPPORT_NAME = "combo_support";
    const FIELD_OPTIONS_VIEW = "fields.options.combolist";
    const FIELD_ADV_OPTIONS_VIEW = "partials.field_option_forms.combolist";

    protected $fillable = [
        'rid',
        'flid',
        'ftype1',
        'ftype2'
    ];

    public function getFieldOptionsView(){
        return self::FIELD_OPTIONS_VIEW;
    }

    public function getAdvancedFieldOptionsView(){
        return self::FIELD_ADV_OPTIONS_VIEW;
    }

    public function getDefaultOptions(Request $request){
        $type1 = $request->cftype1;
        $type2 = $request->cftype2;
        $name1 = '[Name]'.$request->cfname1.'[Name]';
        $name2 = '[Name]'.$request->cfname2.'[Name]';
        $options = "";

        $options = "[!Field1!][Type]";
        if($type1=='Text'){
            $options .= "Text[Type]".$name1."[Options][!Regex!][!Regex!][!MultiLine!]0[!MultiLine!]";
        }else if($type1=='Number'){
            $options .= "Number[Type]".$name1."[Options][!Max!]10[!Max!][!Min!]1[!Min!][!Increment!]1[!Increment!][!Unit!][!Unit!]";
        }else if($type1=='List'){
            $options .= "List[Type]".$name1."[Options][!Options!][!Options!]";
        }else if($type1=='Multi-Select List'){
            $options .= "Multi-Select List[Type]".$name1."[Options][!Options!][!Options!]";
        }else if($type1=='Generated List'){
            $options .= "Generated List[Type]".$name1."[Options][!Regex!][!Regex!][!Options!][!Options!]";
        }
        $options .= "[Options][!Field1!]";

        $options .= "[!Field2!][Type]";
        if($type2=='Text'){
            $options .= "Text[Type]".$name2."[Options][!Regex!][!Regex!][!MultiLine!]0[!MultiLine!]";
        }else if($type2=='Number'){
            $options .= "Number[Type]".$name2."[Options][!Max!]10[!Max!][!Min!]1[!Min!][!Increment!]1[!Increment!][!Unit!][!Unit!]";
        }else if($type2=='List'){
            $options .= "List[Type]".$name2."[Options][!Options!][!Options!]";
        }else if($type2=='Multi-Select List'){
            $options .= "Multi-Select List[Type]".$name2."[Options][!Options!][!Options!]";
        }else if($type2=='Generated List'){
            $options .= "Generated List[Type]".$name2."[Options][!Regex!][!Regex!][!Options!][!Options!]";
        }
        $options .= "[Options][!Field2!]";

        return $options;
    }

    public function updateOptions($field, Request $request, $return=true) {
        $flopt_one ='[Type]'.$request->typeone.'[Type][Name]'.$request->nameone.'[Name][Options]';

        if($request->typeone == 'Text'){
            $flopt_one .= '[!Regex!]'.$request->regex_one.'[!Regex!]';
            $flopt_one .= '[!MultiLine!]'.$request->multi_one.'[!MultiLine!]';
        }else if($request->typeone == 'Number'){
            $flopt_one .= '[!Max!]'.$request->max_one.'[!Max!]';
            $flopt_one .= '[!Min!]'.$request->min_one.'[!Min!]';
            $flopt_one .= '[!Increment!]'.$request->inc_one.'[!Increment!]';
            $flopt_one .= '[!Unit!]'.$request->unit_one.'[!Unit!]';
        }else if($request->typeone == 'List' | $request->typeone == 'Multi-Select List'){
            $flopt_one .= '[!Options!]';

            $reqOpts = $request->options_one;
            $options = $reqOpts[0];
            for($i=1;$i<sizeof($reqOpts);$i++){
                $options .= '[!]'.$reqOpts[$i];
            }
            $flopt_one .= $options;
            $flopt_one .= '[!Options!]';
        }else if($request->typeone == 'Generated List'){
            $flopt_one .= '[!Options!]';

            $reqOpts = $request->options_one;
            $options = $reqOpts[0];
            for($i=1;$i<sizeof($reqOpts);$i++){
                $options .= '[!]'.$reqOpts[$i];
            }
            $flopt_one .= $options;
            $flopt_one .= '[!Options!]';
            $flopt_one .= '[!Regex!]'.$request->regex_one.'[!Regex!]';
        }

        $flopt_one .= '[Options]';

        $flopt_two ='[Type]'.$request->typetwo.'[Type][Name]'.$request->nametwo.'[Name][Options]';

        if($request->typetwo == 'Text'){
            $flopt_two .= '[!Regex!]'.$request->regex_two.'[!Regex!]';
            $flopt_two .= '[!MultiLine!]'.$request->multi_two.'[!MultiLine!]';
        }else if($request->typetwo == 'Number'){
            $flopt_two .= '[!Max!]'.$request->max_two.'[!Max!]';
            $flopt_two .= '[!Min!]'.$request->min_two.'[!Min!]';
            $flopt_two .= '[!Increment!]'.$request->inc_two.'[!Increment!]';
            $flopt_two .= '[!Unit!]'.$request->unit_two.'[!Unit!]';
        }else if($request->typetwo == 'List' | $request->typetwo == 'Multi-Select List'){
            $flopt_two .= '[!Options!]';

            $reqOpts = $request->options_two;
            $options = $reqOpts[0];
            for($i=1;$i<sizeof($reqOpts);$i++){
                $options .= '[!]'.$reqOpts[$i];
            }
            $flopt_two .= $options;
            $flopt_two .= '[!Options!]';
        }else if($request->typetwo == 'Generated List'){
            $flopt_two .= '[!Options!]';

            $reqOpts = $request->options_two;
            $options = $reqOpts[0];
            for($i=1;$i<sizeof($reqOpts);$i++){
                $options .= '[!]'.$reqOpts[$i];
            }
            $flopt_two .= $options;
            $flopt_two .= '[!Options!]';
            $flopt_two .= '[!Regex!]'.$request->regex_two.'[!Regex!]';
        }

        $flopt_two .= '[Options]';

        $default='';
        if(!is_null($request->defvalone) && $request->defvalone != ''){
            $default .= '[!f1!]'.$request->defvalone[0].'[!f1!]';
            $default .= '[!f2!]'.$request->defvaltwo[0].'[!f2!]';

            for($i=1;$i<sizeof($request->defvalone);$i++){
                $default .= '[!def!]';
                $default .= '[!f1!]'.$request->defvalone[$i].'[!f1!]';
                $default .= '[!f2!]'.$request->defvaltwo[$i].'[!f2!]';
            }
        }

        $field->updateRequired($request->required);
        $field->updateSearchable($request);
        $field->updateDefault($default);
        $field->updateOptions('Field1', $flopt_one);
        $field->updateOptions('Field2', $flopt_two);

        if($return) {
            flash()->overlay(trans('controller_field.optupdate'), trans('controller_field.goodjob'));
            return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options');
        } else {
            return '';
        }
    }

    public function createNewRecordField($field, $record, $value, $request){
        if($request->input($field->flid.'_val') != null){
            $this->flid = $field->flid;
            $this->rid = $record->rid;
            $this->fid = $field->fid;
            $this->save();

            $type_1 = self::getComboFieldType($field, 'one');
            $type_2 = self::getComboFieldType($field, 'two');

            // Add combo data to support table.
            $this->addData($request->input($field->flid.'_val'), $type_1, $type_2);
        }
    }

    public function editRecordField($value, $request) {
        if(!is_null($this) && !is_null($request->input($this->flid.'_val'))){
            $field = FieldController::getField($this->flid);
            $type_1 = self::getComboFieldType($field, 'one');
            $type_2 = self::getComboFieldType($field, 'two');

            $this->updateData($_REQUEST[$field->flid.'_val'], $type_1, $type_2);
        }elseif(!is_null($this) && is_null($request->input($this->flid.'_val'))){
            $this->delete();
            $this->deleteData();
        }
    }

    public function massAssignRecordField($field, $record, $formFieldValue, $request, $overwrite=0) {
        $matching_record_fields = $record->combolistfields()->where('flid','=',$field->flid)->get();
        $record->updated_at = Carbon::now();
        $record->save();

        if($matching_record_fields->count() > 0){
            $combolistfield = $matching_record_fields->first();
            if($overwrite == true || $combolistfield->options == "" || is_null($combolistfield->options)){
                $revision = RevisionController::storeRevision($record->rid,'edit');

                $combolistfield->updateData($request->input($field->flid.'_val'));

                $revision->oldData = RevisionController::buildDataArray($record);
                $revision->save();
            }
        } else{
            $this->createNewRecordField($field, $record, $formFieldValue, $request);
            $revision = RevisionController::storeRevision($record->rid,'edit');
            $revision->oldData = RevisionController::buildDataArray($record);
            $revision->save();
        }
    }

    public function createTestRecordField($field, $record){
        $this->flid = $field->flid;
        $this->rid = $record->rid;
        $this->fid = $field->fid;
        $val1 = '';
        $val2 = '';
        $type1 = self::getComboFieldType($field,'one');
        $type2 = self::getComboFieldType($field,'two');
        switch($type1){
            case 'Text':
                $val1 = 'K3TR: This is a test record';
                break;
            case 'List':
                $val1 = 'K3TR';
                break;
            case 'Number':
                $val1 = 1337;
                break;
            case 'Multi-Select List'||'Generated List':
                $val1 = 'K3TR[!]1337[!]Test[!]Record';
                break;
        }
        switch($type2){
            case 'Text':
                $val2 = 'K3TR: This is a test record';
                break;
            case 'List':
                $val2 = 'K3TR';
                break;
            case 'Number':
                $val2 = 1337;
                break;
            case 'Multi-Select List'||'Generated List':
                $val2 = 'K3TR[!]1337[!]Test[!]Record';
                break;
        }
        $this->save();

        $this->addData(["[!f1!]".$val1."[!f1!][!f2!]".$val2."[!f2!]", "[!f1!]".$val1."[!f1!][!f2!]".$val2."[!f2!]"], $type1, $type2);
    }

    public function validateField($field, $value, $request) {
        $req = $field->required;
        $flid = $field->flid;

        if($req==1 && !isset($request[$flid.'_val'])){
            return $field->name.trans('fieldhelpers_val.req');
        }

        return '';
    }

    public function rollbackField($field, Revision $revision, $exists=true) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if (is_null($revision->data[Field::_COMBO_LIST][$field->flid]['data'])) {
            return null;
        }

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || !$exists) {
            $this->flid = $field->flid;
            $this->fid = $revision->fid;
            $this->rid = $revision->rid;
        }

        $this->save();

        $type_1 = self::getComboFieldType($field, "one");
        $type_2 = self::getComboFieldName($field, "two");

        $this->updateData($revision->data[Field::_COMBO_LIST][$field->flid]['data']['options'], $type_1, $type_2);
    }

    public static function getExportSample($field,$type){
        switch ($type){
            case "XML":
                $xml = '<' . Field::xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                $typeone = ComboListField::getComboFieldType($field, 'one');
                $typetwo = ComboListField::getComboFieldType($field, 'two');
                $nameone = ComboListField::getComboFieldName($field, 'one');
                $nametwo = ComboListField::getComboFieldName($field, 'two');
                $xml .= '<Value>';
                $xml .= '<' . Field::xmlTagClear($nameone) . '>';
                if ($typeone == 'Text' | $typeone == 'Number' | $typeone == 'List')
                    $xml .= utf8_encode('VALUE');
                else if ($typeone == 'Multi-Select List' | $typeone == 'Generated List') {
                    $xml .= '<value>'.utf8_encode('VALUE 1').'</value>';
                    $xml .= '<value>'.utf8_encode('VALUE 2').'</value>';
                    $xml .= '<value>'.utf8_encode('so on..').'</value>';
                }
                $xml .= '</' . Field::xmlTagClear($nameone) . '>';
                $xml .= '<' . Field::xmlTagClear($nametwo) . '>';
                if ($typetwo == 'Text' | $typetwo == 'Number' | $typetwo == 'List')
                    $xml .= utf8_encode('VALUE');
                else if ($typetwo == 'Multi-Select List' | $typetwo == 'Generated List') {
                    $xml .= '<value>'.utf8_encode('VALUE 1').'</value>';
                    $xml .= '<value>'.utf8_encode('VALUE 2').'</value>';
                    $xml .= '<value>'.utf8_encode('so on..').'</value>';
                }
                $xml .= '</' . Field::xmlTagClear($nametwo) . '>';
                $xml .= '</Value>';
                $xml .= '</' . Field::xmlTagClear($field->slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                $typeone = ComboListField::getComboFieldType($field, 'one');
                $typetwo = ComboListField::getComboFieldType($field, 'two');
                $nameone = ComboListField::getComboFieldName($field, 'one');
                $nametwo = ComboListField::getComboFieldName($field, 'two');

                $fieldArray['values'] = array();
                $valArray = array();

                if ($typeone == 'Text' | $typeone == 'Number' | $typeone == 'List')
                    $valArray[$nameone] = 'VALUE';
                else if ($typeone == 'Multi-Select List' | $typeone == 'Generated List') {
                    $valArray[$nameone] = array('VALUE 1','VALUE 2','so on...');
                }

                if ($typetwo == 'Text' | $typetwo == 'Number' | $typetwo == 'List')
                    $valArray[$nametwo] = 'VALUE';
                else if ($typetwo == 'Multi-Select List' | $typetwo == 'Generated List') {
                    $valArray[$nametwo] = array('VALUE 1','VALUE 2','so on...');
                }

                array_push($fieldArray['values'], $valArray);

                return $fieldArray;
                break;
        }

    }

    public static function setRestfulAdvSearch($data, $field, $request){
        $type1 = self::getComboFieldType($field,'one');
        switch($type1) {
            case 'Number':
                if(isset($data->left_one))
                    $leftNum = $data->left_one;
                else
                    $leftNum = '';
                $request->request->add([$field->flid.'_1_left' => $leftNum]);
                if(isset($data->right_one))
                    $rightNum = $data->right_one;
                else
                    $rightNum = '';
                $request->request->add([$field->flid.'_1_right' => $rightNum]);
                if(isset($data->invert_one))
                    $invert = $data->invert_one;
                else
                    $invert = 0;
                $request->request->add([$field->flid.'_1_invert' => $invert]);
                break;
            default:
                $request->request->add([$field->flid.'_1_input' => $data->input_one]);
                break;
        }
        $type2 = self::getComboFieldType($field,'two');
        switch($type2) {
            case 'Number':
                if(isset($data->left_two))
                    $leftNum = $data->left_two;
                else
                    $leftNum = '';
                $request->request->add([$field->flid.'_2_left' => $leftNum]);
                if(isset($data->right_two))
                    $rightNum = $data->right_two;
                else
                    $rightNum = '';
                $request->request->add([$field->flid.'_2_right' => $rightNum]);
                if(isset($data->invert_two))
                    $invert = $data->invert_two;
                else
                    $invert = 0;
                $request->request->add([$field->flid.'_2_invert' => $invert]);
                break;
            default:
                $request->request->add([$field->flid.'_2_input' => $data->input_two]);
                break;
        }
        $request->request->add([$field->flid.'_operator' => $data->operator]);

        return $request;
    }

    public static function setRestfulRecordData($field, $flid, $recRequest){
        $values = array();
        $nameone = self::getComboFieldName(FieldController::getField($flid), 'one');
        $nametwo = self::getComboFieldName(FieldController::getField($flid), 'two');
        foreach($field->values as $val) {
            if(!is_array($val[$nameone]))
                $fone = '[!f1!]' . $val[$nameone] . '[!f1!]';
            else
                $fone = '[!f1!]' . implode("[!]",$val[$nameone]) . '[!f1!]';
            if(!is_array($val[$nametwo]))
                $ftwo = '[!f2!]' . $val[$nametwo] . '[!f2!]';
            else
                $ftwo = '[!f2!]' . implode("[!]",$val[$nametwo]) . '[!f2!]';
            array_push($values, $fone . $ftwo);
        }
        $recRequest[$flid] = '';
        $recRequest[$flid . '_val'] = $values;

        return $recRequest;
    }

    public static function getRecordPresetArray($field, $record, $data, $flid_array){
        $cmbfield = ComboListField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

        if (!empty($cmbfield->options)) {
            $data['combolists'] = ComboListField::dataToOldFormat($cmbfield->data()->get());
        }
        else {
            $data['combolists'] = null;
        }

        $flid_array[] = $field->flid;

        return array($data,$flid_array);
    }

    public static function getComboList($field, $blankOpt=false, $fnum)
    {
        $dbOpt = self::getComboFieldOption($field, 'Options', $fnum);

        if($dbOpt === null) {
            return [];
        }

        $options = array();

        if ($dbOpt == '') {
            //skip
        } else if (!strstr($dbOpt, '[!]')) {
            $options = [$dbOpt => $dbOpt];
        } else {
            $opts = explode('[!]', $dbOpt);
            foreach ($opts as $opt) {
                $options[$opt] = $opt;
            }
        }

        if ($blankOpt) {
            $options = array('' => '') + $options;
        }

        return $options;
    }

    public static function getComboFieldOption($field, $key, $num){
        $options = $field->options;
        if($num=='one')
            $opt = explode('[!Field1!]',$options)[1];
        else if($num=='two')
            $opt = explode('[!Field2!]',$options)[1];

        $tag = '[!'.$key.'!]';

        $exploded = explode($tag, $opt);

        if (sizeof($exploded) < 2) {
            return null;
        }

        $value = explode($tag,$opt)[1];

        return $value;
    }

    public static function getComboFieldName($field, $num){
        $options = $field->options;

        if($num=='one') {
            $oneOpts = explode('[!Field1!]', $options)[1];
            $name = explode('[Name]', $oneOpts)[1];
        }else if ($num=='two') {
            $twoOpts = explode('[!Field2!]', $options)[1];
            $name = explode('[Name]', $twoOpts)[1];
        }

        return $name;
    }

    public static function getComboFieldType($field, $num){
        $options = $field->options;

        if($num=='one') {
            $oneOpts = explode('[!Field1!]', $options)[1];
            $type = explode('[Type]', $oneOpts)[1];
        }else if ($num=='two') {
            $twoOpts = explode('[!Field2!]', $options)[1];
            $type = explode('[Type]', $twoOpts)[1];
        }

        return $type;
    }


    /**
     * @throws \Exception
     */
    public function delete() {
        $this->deleteData();
        parent::delete();
    }

    /**
     * Adds data to the combo list support field.
     *
     * @param array $data, data as formatted by the record entry.
     * @param string $type_1, type of first combo field.
     * @param string $type_2, type of second combo field.
     */
    public function addData(array $data, $type_1, $type_2) {
        $now = date("Y-m-d H:i:s");

        $inserts = [];

        $one_is_num = $type_1 == 'Number';
        $two_is_num = $type_2 == 'Number';

        $i = 0;
        foreach ($data as $entry) {
            $field_1_data = explode('[!f1!]', $entry)[1];
            $field_2_data = explode('[!f2!]', $entry)[1];

            $inserts[] = [
                'fid' => $this->fid,
                'rid' => $this->rid,
                'flid' => $this->flid,
                'field_num' => 1,
                'list_index' => $i,
                'data' => (!$one_is_num) ? $field_1_data : null,
                'number' => ($one_is_num) ? $field_1_data : null,
                'created_at' => $now,
                'updated_at' => $now
            ];

            $inserts[] = [
                'fid' => $this->fid,
                'rid' => $this->rid,
                'flid' => $this->flid,
                'list_index' => $i,
                'field_num' => 2,
                'data' => (!$two_is_num) ? $field_2_data : null,
                'number' => ($two_is_num) ? $field_2_data : null,
                'created_at' => $now,
                'updated_at' => $now
            ];

            $i++;
        }

        DB::table('combo_support')->insert($inserts);
    }

    /**
     * The query for data in a combo list field. Orders by the index data appears in the list.
     * Use ->get() to obtain all events.
     * @return Builder
     */
    public function data() {
        return DB::table(self::SUPPORT_NAME)->select("*")
            ->where("rid", "=", $this->rid)
            ->where("flid", "=", $this->flid)
            ->orderBy('list_index');
    }

    /**
     * True if there is data associated with a particular Combo List field.
     *
     * @return bool
     */
    public function hasData() {
        return !! $this->data()->count();
    }

    /**
     * @param null $field
     * @return array
     */
    public function getRevisionData($field = null) {
        $field = Field::where('flid', '=', $this->flid)->first();

        $name_1 = self::getComboFieldName($field, 'one');
        $name_2 = self::getComboFieldName($field, 'two');

        return [
            'options' => self::dataToOldFormat($this->data()->get()),
            'name_1' => $name_1,
            'name_2' => $name_2
        ];
    }

    /**
     * Puts an array of data into the old format.
     *      - "Old Format" meaning, and array of the data options formatted as
     *        [!f1!]<Field 1 Data>[!f1!][!f2!]<Field 2 Data>[!f2!]
     *
     * @param array $data, array of StdObjects representing data options.
     * @param bool $array_string, should this be in the old *[!val!]*[!val!]...[!val!]* format?
     * @return array | string
     */
    public static function dataToOldFormat(array $data, $array_string = false) {
        $formatted = [];
        for($i = 0; $i < count($data); $i++) {
            $op1 = $data[$i];
            $op2 = $data[++$i];

            if ($op1->field_num == 2) {
                $tmp = $op1;
                $op1 = $op2;
                $op2 = $tmp;
            }

            if (! is_null($op1->data)) {
                $val1 = $op1->data;
            }
            else {
                $val1 = $op1->number + 0;
            }

            if (! is_null($op2->data)) {
                $val2 = $op2->data;
            }
            else {
                $val2 = $op2->number + 0;
            }


            $formatted[] = "[!f1!]"
                . $val1
                . "[!f1!]"
                . "[!f2!]"
                . $val2
                . "[!f2!]";
        }

        if($array_string) {
            return implode("[!val!]", $formatted);
        }

        return $formatted;
    }

    /**
     * Delete data associated with this field.
     */
    public function deleteData() {
        DB::table(self::SUPPORT_NAME)
            ->where("rid", "=", $this->rid)
            ->where("flid", "=", $this->flid)
            ->delete();
    }

    /**
     * Updates a combo list's data.
     *
     * @param array $data
     * @param $type_1
     * @param $type_2
     */
    public function updateData(array $data, $type_1, $type_2) {
        $this->deleteData();
        $this->addData($data, $type_1, $type_2);
    }

    /**
     * Get advanced search query for a combo list field.
     *
     * @param mixed $flid, field id.
     * @param array $query, query array from the form.
     * @return Builder, the search query for the field rids.
     */
    public static function getAdvancedSearchQuery($flid, $query) {
        $field = Field::where("flid", "=", $flid)->first();
        $type_1 = self::getComboFieldType($field, 'one');
        $type_2 = self::getComboFieldType($field, 'two');

        $one_valid = $query[$flid . "_1_valid"] == "1";
        $two_valid = $query[$flid . "_2_valid"] == "1";

        // Return an impossible query if the two fields are somehow both invalid.
        // May seem extraneous, but this is required for chaining calls elsewhere.
        if (! ($one_valid || $two_valid)) {
            return DB::table(self::SUPPORT_NAME)->select("*")->where("id", "<", 0);
        }
        else if ($one_valid && $two_valid) {
            if ($query[$flid . "_operator"] == "and") {
                //
                // We need to join combo_support with itself.
                // Since each entry represents one sub-field in the combo list, an "and" operation
                // on a combo list would be impossible without two copies of everything.
                //
                $first_prefix = "one.";
                $second_prefix = "two.";

                $db_query = DB::table("combo_support AS " . substr($first_prefix, 0, -1))
                    ->select($first_prefix . "rid")
                    ->where($first_prefix . "flid", "=", $flid)
                    ->join("combo_support AS " . substr($second_prefix, 0, -1),
                        $first_prefix . "rid",
                        "=",
                        $second_prefix . "rid");

                $db_query->where(function($db_query) use ($flid, $query, $type_1, $first_prefix) {
                    self::buildAdvancedQueryRoutine($db_query, "1", $flid, $query, $type_1, $first_prefix);
                });
                $db_query->where(function($db_query) use ($flid, $query, $type_2, $second_prefix) {
                    self::buildAdvancedQueryRoutine($db_query, "2", $flid, $query, $type_2, $second_prefix);
                });

            }
            else { // OR operation.
                $db_query = self::makeAdvancedQueryRoutine($flid);
                $db_query->where(function($db_query) use ($flid, $query, $type_1) {
                   self::buildAdvancedQueryRoutine($db_query, "1", $flid, $query, $type_1);
                });
                $db_query->orWhere(function($db_query) use ($flid, $query, $type_2) {
                   self::buildAdvancedQueryRoutine($db_query, "2", $flid, $query, $type_2);
                });
            }
        }
        else if ($one_valid) {
            $db_query = self::makeAdvancedQueryRoutine($flid);
            self::buildAdvancedQueryRoutine($db_query, "1", $flid, $query, $type_1);
        }
        else { // two valid
            $db_query = self::makeAdvancedQueryRoutine($flid);
            self::buildAdvancedQueryRoutine($db_query, "2", $flid, $query, $type_2);
        }

        return $db_query->distinct();
    }

    /**
     * The logic to build up an advanced query.
     *
     * @param Builder $db_query, reference to the current query.
     * @param mixed $field_num, first or second field in the combo list.
     * @param int $flid, field id.
     * @param array $query, query array from the form.
     * @param string $type, the type of the combo field.
     * @param string $prefix, to deal with joined tables.
     */
    public static function buildAdvancedQueryRoutine(Builder &$db_query, $field_num, $flid, $query, $type, $prefix = "") {
        $db_query->where($prefix . "field_num", "=", $field_num);

        if ($type == Field::_NUMBER) {
            NumberField::buildAdvancedNumberQuery($db_query,
                $query[$flid . "_" . $field_num . "_left"],
                $query[$flid . "_" . $field_num . "_right"],
                isset($query[$flid . "_" . $field_num . "_invert"]),
                $prefix);
        }
        else {
            if ($type == Field::_LIST || $type == Field::_TEXT) {
                $inputs = [$query[$flid . "_" . $field_num . "_input"]];
            }
            else { // Generated or Multi-Select List
                $inputs = $query[$flid . "_" . $field_num . "_input"];
            }

            // Since we're using a raw query, we have to get the database prefix to match our alias.
            $db_prefix = DB::getTablePrefix();
            $prefix = ($prefix == "") ? self::SUPPORT_NAME : substr($prefix, 0, -1);
            $db_query->where(function($db_query) use ($inputs, $prefix, $db_prefix) {
                foreach($inputs as $input) {
                    $db_query->orWhereRaw("MATCH (`" . $db_prefix . $prefix . "`.`data`) AGAINST (? IN BOOLEAN MODE)",
                        [Search::processArgument($input, Search::ADVANCED_METHOD)]);
                }
            });
        }
    }

    /**
     * Makes the initial DB query.
     * @param int $flid, field id.
     * @return Builder, initial query.
     */
    public static function makeAdvancedQueryRoutine($flid) {
        return DB::table(self::SUPPORT_NAME)
            ->select("rid")
            ->where("flid", "=", $flid);
    }

    /**
     * Validates record data for a Combo List Field.
     *
     * @param  int $flid - Field ID
     * @param  Request $request
     * @return string - Returns on error or blank on success
     */
    public static function validateComboListOpt($flid, \Illuminate\Http\Request $request) {
        $field = FieldController::getField($flid);

        $valone = $request->valone;
        $valtwo = $request->valtwo;
        $typeone = $request->typeone;
        $typetwo = $request->typetwo;

        if($valone=="" | $valtwo=="") {
            return trans('controller_field.valueboth');
        }

        if($typeone=='Text') {
            $regex = self::getComboFieldOption($field,'Regex','one');
            if(($regex!=null | $regex!="") && !preg_match($regex,$valone)) {
                return trans('controller_field.v1regex');
            }
        } else if($typeone=='Number') {
            $max = self::getComboFieldOption($field,'Max','one');
            $min = self::getComboFieldOption($field,'Min','one');
            $inc = self::getComboFieldOption($field,'Increment','one');

            if($valone<$min | $valone>$max) {
                return trans('controller_field.v1num');
            }

            if(fmod(floatval($valone),floatval($inc))!=0) {
                return trans('controller_field.v1numinc');
            }
        } else if($typeone=='List') {
            $opts = explode('[!]',self::getComboFieldOption($field,'Options','one'));

            if(!in_array($valone,$opts)) {
                return trans('controller_field.v1list');
            }
        } else if($typeone=='Multi-Select List') {
            $opts = explode('[!]',self::getComboFieldOption($field,'Options','one'));

            if(sizeof(array_diff($valone,$opts))>0) {
                return trans('controller_field.v1mslist');
            }
        } else if($typeone=='Generated List') {
            $regex = self::getComboFieldOption($field,'Regex','one');

            if($regex != null | $regex != "") {
                foreach($valone as $val) {
                    if(!preg_match($regex, $val)) {
                        return trans('controller_field.v1genlist');
                    }
                }
            }
        }

        if($typetwo=='Text') {
            $regex = self::getComboFieldOption($field,'Regex','two');
            if(($regex!=null | $regex!="") && !preg_match($regex,$valtwo)) {
                return trans('controller_field.v2regex');
            }
        } else if($typetwo=='Number') {
            $max = self::getComboFieldOption($field,'Max','two');
            $min = self::getComboFieldOption($field,'Min','two');
            $inc = self::getComboFieldOption($field,'Increment','two');

            if($valtwo<$min | $valtwo>$max) {
                return trans('controller_field.v2num');
            }
            if(fmod(floatval($valtwo),floatval($inc))!=0) {
                return trans('controller_field.v2numinc');
            }
        } else if($typetwo=='List') {
            $opts = explode('[!]',self::getComboFieldOption($field,'Options','two'));

            if(!in_array($valtwo,$opts)) {
                return trans('controller_field.v2list');
            }
        } else if($typetwo=='Multi-Select List') {
            $opts = explode('[!]',self::getComboFieldOption($field,'Options','two'));

            if(sizeof(array_diff($valtwo,$opts))>0) {
                return trans('controller_field.v2mslist');
            }
        } else if($typetwo=='Generated List') {
            $regex = self::getComboFieldOption($field,'Regex','two');

            if($regex != null | $regex != "") {
                foreach($valtwo as $val) {
                    if(!preg_match($regex, $val)) {
                        return trans('controller_field.v2genlist');
                    }
                }
            }
        }
        return '';
    }
}
