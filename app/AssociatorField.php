<?php namespace App;

use App\Http\Controllers\FieldController;
use Illuminate\Database\Eloquent\Model;

class AssociatorField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'records'
    ];

    public static function getDefault($default, $blankOpt=false)
    {
        $options = array();

        if ($default == '') {
            //skip
        } else if (!strstr($default, '[!]')) {
            $options = [$default => $default];
        } else {
            $opts = explode('[!]', $default);
            foreach ($opts as $opt) {
                $options[$opt] = $opt;
            }
        }

        if ($blankOpt) {
            $options = array('' => '') + $options;
        }

        return $options;
    }

    public function getPreviewValues($kid){
        //individual kid elements
        $pieces = explode('-',$kid);
        $pid = $pieces[0];
        $fid = $pieces[1];
        $rid = $pieces[2];

        //get the preview flid structure of this associator
        $activeForms = array();
        $field = FieldController::getField($this->flid);
        $option = FieldController::getFieldOption($field,'SearchForms');
        if($option!=''){
            $options = explode('[!]',$option);

            foreach($options as $opt){
                $opt_fid = explode('[fid]',$opt)[1];
                $opt_search = explode('[search]',$opt)[1];
                $opt_flids = explode('[flids]',$opt)[1];
                $opt_flids = explode('-',$opt_flids);

                if($opt_search == 1)
                    $flids = array();
                foreach($opt_flids as $flid){
                    $field = FieldController::getField($flid);
                    $flids[$flid] = $field->type;
                }
                $activeForms[$opt_fid] = ['flids' => $flids];
            }
        }

        //grab the preview fields associated with the form of this kid
        $details = $activeForms[$fid];
        $preview = array();
        foreach($details['flids'] as $flid=>$type){
            if($type=='Text'){
                $text = TextField::where("flid", "=", $flid)->where("rid", "=", $rid)->first();
                if($text->text != '')
                    array_push($preview,$text->text);
            }else if($type=='List'){
                $list = ListField::where("flid", "=", $flid)->where("rid", "=", $rid)->first();
                if($list->option != '')
                    array_push($preview,$list->option);
            }
        }

        $html = "<a href='".env('BASE_URL')."public/projects/".$pid."/forms/".$fid."/records/".$rid."'>".$kid."</a>";
        foreach($preview as $val){
            $html .= " | ".$val;
        }

        return $html;
    }

    /**
     * @param null $field
     * @return string
     */
    public function getRevisionData($field = null) {
        return $this->records;
    }

    /**
     * Rollback a associator field based on a revision.
     *
     * ** Assumes $revision->data is json decoded. **
     *
     * @param Revision $revision
     * @param Field $field
     */
    public static function rollback(Revision $revision, Field $field) {
        $associatorfield = AssociatorField::where("flid", "=", $field->flid)->where("rid", "=", $revision->rid)->first();

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($associatorfield)) {
            $associatorfield = new AssociatorField();
            $associatorfield->flid = $field->flid;
            $associatorfield->fid = $revision->fid;
            $associatorfield->rid = $revision->rid;
        }

        $associatorfield->records = $revision->data[Field::_ASSOCIATOR][$field->flid];
        $associatorfield->save();
    }

    public function isMetafiable() {
        // TODO: Implement isMetafiable() method.
        return false; // I think this will never need to be metafied.
    }

    public function toMetadata(Field $field) {
        // TODO: Implement toMetadata() method.
        return null;
    }
}
