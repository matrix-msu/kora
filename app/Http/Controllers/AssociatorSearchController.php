<?php namespace App\Http\Controllers;

use App\Form;
use App\Record;
use App\Search;
use Illuminate\Http\Request;

class AssociatorSearchController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Associator Search Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles record searching for individual associator
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
        $field = FieldController::getField($flid,$fid);
        $keyword = $request->keyword;

        $activeForms = array();
        $results = array();

//        if(!is_null($request->combo)) //TODO::CASTLE
//            $option = ComboListField::getComboFieldOption($field, 'SearchForms', $request->combo);
//        else
            $options = $field['options']['SearchForms'];

        foreach($options as $opt) {
            $opt_fid = $opt['form_id'];
            $opt_search = $opt['search'];
            $opt_flids = explode('-',$opt['flids']);

            if($opt_search == 1) {
                $flids = array();

                foreach($opt_flids as $flid) {
                    if($flid!='') {
                        $field = FieldController::getField($flid,$opt_fid);
                        $flids[$flid] = $field['type'];
                    }
                }

                $activeForms[$opt_fid] = ['flids' => $flids];
            }
        }

        foreach($activeForms as $actfid => $details) {
            $results = array();

            if(Record::isKIDPattern($keyword)) {
                //KID Search
                $recModel = new Record(array(),$actfid);
                $record = $recModel->newQuery()->where('kid','=',$keyword)->first();
                if(!is_null($record)) {
                    $preview = array();
                    foreach($details['flids'] as $dflid => $type) {
                        if(!in_array($type,Form::$validAssocFields)) {
                            array_push($preview, "Invalid Preview Field");
                        } else {
                            $value = $record->{$dflid};
                            if(is_null($value))
                                $value = "Preview Field Empty";
                            array_push($preview, $value);
                        }
                    }

                    $results[$record->kid] = $preview;
                }
            } else {
                //Form Search
                $form = FormController::getForm($actfid);
                $results = self::search($form->project_id, $form, $keyword, $details);
            }
        }

        return $results;
    }

    /**
     * Performs the associator search for an individual form.
     *
     * @param  int $pid - Project ID
     * @param  Form $form - Form Model
     * @param  string $arg - Keyword used in the search
     * @param  array $details - Details about form searching in
     * @param  int $method - The type of keyword search we want to use
     * @return array - results from the search
     */
    private function search($pid, $form, $arg, $details, $method=Search::SEARCH_OR) {
        $results = array();
        $fid = $form->id;

        $filters = ["revAssoc" => false, "meta" => false, "fields" => 'ALL', "realnames" => false, "assoc" => false,
            "data" => true, "sort" => null, "count" => null, "index" => null];
        $formRecords = $form->getRecordsForExport($filters);

        if($arg!="") {
            $search = new Search($pid, $fid, $arg, $method);
            $rids = $search->formKeywordSearch();

            foreach($rids as $rid) {
                $kid = $pid.'-'.$fid.'-'.$rid;
                $preview = array();

                foreach($details['flids'] as $dflid => $type) {
                    if(!in_array($type,Form::$validAssocFields)) {
                        array_push($preview, "Invalid Preview Field");
                    } else {
                        $value = $formRecords[$kid][$dflid];
                        if(is_null($value))
                            $value = "Preview Field Empty";
                        array_push($preview, $value);
                    }
                }

                $results[$kid] = $preview;
            }
        } else {
            //If no search term given, return everything!!!!
            foreach($formRecords as $kid => $recData) {
                $preview = array();

                foreach($details['flids'] as $dflid => $type) {
                    if(!in_array($type,Form::$validAssocFields)) {
                        array_push($preview, "Invalid Preview Field");
                    } else {
                        $value = $formRecords[$kid][$dflid];
                        if(is_null($value))
                            $value = "Preview Field Empty";
                        array_push($preview, $value);
                    }
                }

                $results[$kid] = $preview;
            }
        }

        return $results;
    }
}
