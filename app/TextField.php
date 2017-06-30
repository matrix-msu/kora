<?php namespace App;

use App\Http\Controllers\FieldController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TextField extends BaseField {

    const FIELD_OPTIONS_VIEW = "fields.options.text";
    const FIELD_ADV_OPTIONS_VIEW = "partials.field_option_forms.text";

    protected $fillable = [
        'rid',
        'flid',
        'text'
    ];

    public static function getOptions(){
        return '[!Regex!][!Regex!][!MultiLine!]0[!MultiLine!]';
    }

    public static function getExportSample($field,$type){
        switch ($type){
            case "XML":
                $xml = '<' . Field::xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                $xml .= utf8_encode('TEXT VALUE');
                $xml .= '</' . Field::xmlTagClear($field->slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                $fieldArray['text'] = 'TEXT VALUE';

                return $fieldArray;
                break;
        }

    }

    public static function updateOptions($pid, $fid, $flid, $request, $return=true){
        $advString = '';

        if($request->regex!=''){
            $regArray = str_split($request->regex);
            if($regArray[0]!=end($regArray)){
                $request->regex = '/'.$request->regex.'/';
            }
            if ($request->default!='' && !preg_match($request->regex, $request->default))
            {
                if($return){
                    flash()->error('The default value does not match the given regex pattern.');

                    return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options')->withInput();
                }else{
                    $request->default = '';
                    $advString = 'The default value does not match the given regex pattern.';
                }
            }
        }

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request);
        FieldController::updateDefault($pid, $fid, $flid, $request->default);
        FieldController::updateOptions($pid, $fid, $flid, 'Regex', $request->regex);
        FieldController::updateOptions($pid, $fid, $flid, 'MultiLine', $request->multi);

        return $advString;
    }

    public static function setRestfulAdvSearch($data, $field, $request){
        $request->request->add([$field->flid.'_input' => $data->input]);

        return $request;
    }

    public static function setRestfulRecordData($field, $flid, $recRequest){
        $recRequest[$flid] = $field->text;

        return $recRequest;
    }

    /**
     * @param Field | null $field
     * @return string
     */
    public function getRevisionData($field = null) {
        return $this->text;
    }

    /**
     * Rollback a text field based on a revision.
     *
     * @param Revision $revision
     * @param Field $field
     * @return TextField
     */
    public static function rollback(Revision $revision, Field $field) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if (is_null($revision->data[Field::_TEXT][$field->flid]['data'])) {
            return null;
        }

        $textfield = self::where("flid", "=", $field->flid)->where("rid", "=", $revision->rid)->first();

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($textfield)) {
            $textfield = new self();
            $textfield->flid = $field->flid;
            $textfield->rid = $revision->rid;
            $textfield->fid = $revision->fid;
        }

        $textfield->text = $revision->data[Field::_TEXT][$field->flid]['data'];
        $textfield->save();

        return $textfield;
    }

    /**
     * Build the advanced query for a text field.
     *
     * @param $flid, field id
     * @param $query, contents of query.
     * @return Builder
     */
    public static function getAdvancedSearchQuery($flid, $query) {
        return DB::table("text_fields")
            ->select("rid")
            ->where("flid", "=", $flid)
            ->whereRaw("MATCH (`text`) AGAINST (? IN BOOLEAN MODE)",
                [Search::processArgument($query[$flid . "_input"], Search::ADVANCED_METHOD)])
            ->distinct();
    }

    public static function validate($field, $value){
        $req = $field->required;
        $regex = FieldController::getFieldOption($field, 'Regex');

        if($req==1 && ($value==null | $value=="")){
            return $field->name.trans('fieldhelpers_val.req');
        }

        if(($regex!=null | $regex!="") && !preg_match($regex,$value)){
            return trans('fieldhelpers_val.regex',['name'=>$field->name]);
        }

        return '';
    }
}