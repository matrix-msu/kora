<?php namespace App\Http\Controllers;

use App\ComboListField;
use App\ListField;
use App\Record;
use App\Search;
use App\TextField;
use Illuminate\Http\Request;

class AssociatorSearchController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Associator Search Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles recording searching for individual associator
    | fields in record creation
    |
    */

    /**
     * Constructs controller and makes sure user is authenticated.
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * Handles the request for an association search.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @param  Request $request
     * @return array - The results from the search
     */
    public function assocSearch($pid, $fid, $flid, Request $request) {
        $field = FieldController::getField($flid);
        $keyword = $request->keyword;

        $activeForms = array();
        $results = array();

        if(!is_null($request->combo))
            $option = ComboListField::getComboFieldOption($field, 'SearchForms', $request->combo);
        else
            $option = FieldController::getFieldOption($field,'SearchForms');

        if($option!='') {
            $options = explode('[!]',$option);

            foreach($options as $opt) {
                $opt_fid = explode('[fid]',$opt)[1];
                $opt_search = explode('[search]',$opt)[1];
                $opt_flids = explode('[flids]',$opt)[1];
                $opt_flids = explode('-',$opt_flids);

                if($opt_search == 1) {
                    $flids = array();

                    foreach($opt_flids as $flid) {
                        if($flid!='') {
                            $field = FieldController::getField($flid);
                            $flids[$flid] = $field->type;
                        }
                    }

                    $activeForms[$opt_fid] = ['flids' => $flids];
                }
            }
        }

        foreach($activeForms as $fid => $details) {
            $form = FormController::getForm($fid);

            if(Record::isKIDPattern($keyword)) {
                //KID Search
                $record = Record::where('kid','=',$keyword)->first();
                if(!is_null($record) && $record->fid==$fid)
                    $rids = array($record->rid);
                else
                    $rids = array();
            } else {
                //Form Search
                $rids = self::search($form->pid, $fid, $keyword);
            }

            foreach($rids as $rid) {
                $kid = $form->pid.'-'.$fid.'-'.$rid;
                $preview = array();
                foreach($details['flids'] as $flid=>$type) {
                    //TODO:: add more previews
                    if($type=='Text') {
                        $text = TextField::where("flid", "=", $flid)->where("rid", "=", $rid)->first();
                        if(!is_null($text) && $text->text != '')
                            array_push($preview,$text->text);
                    } else if($type=='List') {
                        $list = ListField::where("flid", "=", $flid)->where("rid", "=", $rid)->first();
                        if(!is_null($list) && $list->option != '')
                            array_push($preview,$list->option);
                    } else {
                        array_push($preview, 'Invalid Preview Field');
                    }
                }

                $results[$kid] = $preview;
            }
        }

        return $results;
    }

    /**
     * Performs the associator search for an individual form.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  string $arg - Keyword used in the search
     * @param  int $method - The type of keyword search we want to use
     * @return array - RID results from the search
     */
    private function search($pid, $fid, $arg, $method=Search::SEARCH_OR) {
        $rids = [];

        // Inform the user about arguments that will be ignored.
        if($arg!="") {
            $ignored = Search::showIgnoredArguments($arg);
            $args = explode(" ", $arg);
            $args = array_diff($args, $ignored);
            $arg = implode(" ", $args);

            $search = new Search($pid, $fid, $arg, $method);
            $rids = $search->formKeywordSearch();
        } else {
            //If no search term given, return everything!!!!
            $rids = Record::where("fid","=",$fid)->pluck('rid')->all();
        }

        sort($rids);

        return $rids;
    }
}
