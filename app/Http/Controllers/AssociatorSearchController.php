<?php

namespace App\Http\Controllers;

use App\ListField;
use App\Record;
use App\Search;
use App\TextField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

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

        $option = FieldController::getFieldOption($field,'SearchForms');
        if($option!='') {
            $options = explode('[!]',$option);

            foreach($options as $opt) {
                $opt_fid = explode('[fid]',$opt)[1];
                $opt_search = explode('[search]',$opt)[1];
                $opt_flids = explode('[flids]',$opt)[1];
                $opt_flids = explode('-',$opt_flids);

                if($opt_search == 1)
                    $flids = array();
                foreach($opt_flids as $flid) {
                    $field = FieldController::getField($flid);
                    $flids[$flid] = $field->type;
                }
                $activeForms[$opt_fid] = ['flids' => $flids];
            }
        }

        foreach($activeForms as $fid => $details) {
            $form = FormController::getForm($fid);

            $rids = self::search($form->pid, $fid, $keyword);

            foreach($rids as $rid) {
                $kid = $form->pid.'-'.$fid.'-'.$rid;
                $preview = array();
                foreach($details['flids'] as $flid=>$type) {
                    if($type=='Text') {
                        $text = TextField::where("flid", "=", $flid)->where("rid", "=", $rid)->first();
                        if($text->text != '')
                            array_push($preview,$text->text);
                    } else if($type=='List') {
                        $list = ListField::where("flid", "=", $flid)->where("rid", "=", $rid)->first();
                        if($list->option != '')
                            array_push($preview,$list->option);
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
    private function search($pid, $fid, $arg, $method=0) {
        $rids = [];

        $do_query = false;
        if(Session::has("query_assoc")) { // Have we ever searched before?
            $session_query = Session::get("query_assoc");
            $session_method = Session::get("method_assoc");
            $session_ids = Session::get("ids_assoc");

            if($session_query == $arg && $session_method == $method && $session_ids == $pid.' '.$fid) { // This is the same search so we shouldn't re-execute the query.
                $rids = unserialize(Session::get("rids_assoc"));
            } else { // This is a new search, so we have to execute again.
                $do_query = true;
            }
        } else { // We have never searched before, so we must execute.
            $do_query = true;
        }

        if($do_query) {
            // Inform the user about arguments that will be ignored.
            $ignored = Search::showIgnoredArguments($arg);
            $args = explode(" ", $arg);
            $args = array_diff($args, $ignored);
            $arg = implode(" ", $args);

            if($arg!="") {
                $search = new Search($pid, $fid, $arg, $method);

                $rids = $search->formKeywordSearch();
            } else {
                //If no search term given, return everything!!!!
                $rids = Record::where("fid","=",$fid)->lists('rid')->all();
            }

            sort($rids);

            Session::put("rids_assoc", serialize($rids));
        }

        Session::put("query_assoc", $arg);
        Session::put("method_assoc", $method);
        Session::put("ids_assoc", $pid.' '.$fid);

        return $rids;
    }
}
