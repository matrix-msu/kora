<?php namespace App\Http\Controllers;

use App\Form;
use App\KoraFields\ComboListField;
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

        if($request->has('combo'))
            $options = $field[$request->combo]['options']['SearchForms'];
        else
            $options = $field['options']['SearchForms'];

        foreach($options as $opt) {
            $opt_fid = $opt['form_id'];
            $opt_flids = $opt['flids'];

            $flids = array();

            foreach($opt_flids as $flid) {
                if($flid!='') {
                    $field = FieldController::getField($flid,$opt_fid);
                    $flids[$field['name']] = $field['type'];
                }
            }

            $activeForms[$opt_fid] = ['flids' => $flids];
        }

        $results = array();
        foreach($activeForms as $actfid => $details) {
            if(Record::isKIDPattern($keyword)) {
                //KID Search
                $recModel = new Record(array(),$actfid);
                $record = $recModel->newQuery()->where('kid','=',$keyword)->first();
                if(!is_null($record) && $record->form_id == $actfid) {
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
                $results = array_merge($results, self::search($form->project_id, $form, $keyword, $details));
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

        $filters = ["revAssoc" => true, "meta" => false, "fields" => 'ALL', "altNames" => false, "assoc" => false,
            "data" => true, "sort" => null, "count" => null, "index" => null];
        $formRecords = $form->getRecordsForExport($filters);

        if($arg!="") {
            //This line breaks apart the space separated keywords, but also maintains words held together in double quotes
            $keys = str_getcsv($arg, ' ');
            $search = new Search($pid, $fid, $keys, $method);
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
