<?php namespace App\Http\Controllers;


use App\Field;
use App\Form;
use App\Record;
use Geocoder\Geocoder;
use Illuminate\Http\Request;
use Geocoder\Provider\NominatimProvider;
use Geocoder\HttpAdapter\CurlHttpAdapter;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class AdvancedSearchController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Advanced Search Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles advanced searches for a form
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
     * Gets a list of all fields in the form and returns the advanced search view.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
    public function index($pid, $fid) {
        if(! FormController::validProjForm($pid, $fid)) {
            return redirect('projects/'.$pid);
        }

        $fields = Field::where("fid", "=", $fid)->get();
        return view("advancedSearch.index", compact("pid", "fid", "fields"));
    }

    /**
     * Performs the advanced search and stores results in the session.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return Redirect
     */
    public function search($pid, $fid, Request $request) {
        if(! FormController::validProjForm($pid, $fid)) {
            return redirect("projects/". $pid);
        }

        $form = FormController::getForm($fid);
        $stash = $form->getFieldStash();

        $request = $request->all();
        array_pop($request); // Pop off the CSRF token.

        $results = [];

        foreach($this->processRequest($request) as $flid => $query) {
            // Result will be returned as an array of stdObjects so we have to extract the rid.
            $result = array_map(function($returned) {
                return $returned->rid;
            }, Field::advancedSearch($flid, $stash[$flid]["type"], $query)->get());
            $results[] = $result;
        }

        $rids = array_pop($results);

        // This functions to make sure that a record satisfies all search parameters.
        foreach($results as $result) {
            $rids = array_intersect($rids, $result);
        }

        Session::put("rids", $rids);

        return redirect('projects/'.$pid.'/forms/'.$fid.'/advancedSearch/results');
    }

    /**
     * Handles an advanced search from the API. We need the results back directly, rather than a view to display them.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return array - Record ID search results
     */
    public function apisearch($pid, $fid, Request $request) {
        if (! FormController::validProjForm($pid, $fid)) {
            return redirect("projects/". $pid);
        }

        $form = FormController::getForm($fid);
        $stash = $form->getFieldStash();

        $request = $request->all();

        $results = [];

        foreach($this->processRequest($request) as $flid => $query) {
            // Result will be returned as an array of stdObjects so we have to extract the rid.
            $result = array_map(function($returned) {
                return $returned->rid;
            }, Field::advancedSearch($flid, $stash[$flid]["type"], $query)->get());
            $results[] = $result;
        }

        $rids = array_pop($results);

        // This functions to make sure that a record satisfies all search parameters.
        foreach($results as $result) {
            $rids = array_intersect($rids, $result);
        }

        return $rids;
    }

    /**
     * Processes and prepares the results for the results view, including pagination.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
    public function results($pid, $fid) {
        if(!FormController::validProjForm($pid,$fid)) {
            return redirect('projects/'.$pid);
        }

        $page = (isset($_GET['page'])) ? intval(strip_tags($_GET['page'])) : $page = 1;

        if(Session::has("rids")) {
            $rids = Session::get("rids");
        } else {
            $rids = [];
        }

        $controller = new RecordController();
        $filesize = $controller->getFormFilesize($fid);

        $record_count = $page * RecordController::RECORDS_PER_PAGE;
        $slice = array_slice($rids, $record_count - RecordController::RECORDS_PER_PAGE, $record_count);

        $query = Record::where("rid", "=", array_shift($slice));
        foreach($slice as $rid) {
            $query->orWhere("rid", "=", $rid);
        }
        $records = $query->get();

        $rid_paginator = new LengthAwarePaginator($rids, count($rids), RecordController::RECORDS_PER_PAGE, $page);
        $rid_paginator->setPath( env('BASE_URL') . 'projects/' . $pid . '/forms/' . $fid . '/advancedSearch/results');

        $form = Form::where("fid", "=", $fid)->first();
        return view('search.results', compact("form", "filesize", "records", "rid_paginator"));
    }

    /**
     * Takes the request variables for an advanced search an processed them for use.
     *
     * @param  array $request - Variables from the request
     * @return array - Processed array
     */
    private function processRequest(array $request) {
        $processed = [];
        $query = [];
        // Process the search request.
        $prev_flid = -1;
        foreach($request as $key => $value) {
            $flid = explode("_", $key)[0];
            if($flid != $prev_flid) { // On a new input group.

                // Only add the new query if it is valid.
                if(isset($query[$prev_flid . "_valid"]) && isset($query[$prev_flid . "_dropdown"])) {
                    if($query[$prev_flid . "_valid"] == "1") {
                        $processed[$prev_flid] = $query;
                    }
                } else if(isset($query[$prev_flid . "_1_valid"]) && isset($query[$prev_flid . "_2_valid"])) {
                    if($query[$prev_flid . "_1_valid"] == "1" || $query[$prev_flid . "_2_valid"] == "1") {
                        $processed[$prev_flid] = $query;
                    }
                }

                $query = [];
                $query[$key] = $value;
                $prev_flid = $flid;
            } else {
                $query[$key] = $value;
                $prev_flid = $flid;
            }
        }

        // Check the last query.
        if(isset($query[$prev_flid . "_valid"])) {
            if($query[$prev_flid . "_valid"] == "1") {
                $processed[$prev_flid] = $query;
            }
        } else if(isset($query[$prev_flid . "_1_valid"]) && isset($query[$prev_flid . "_2_valid"])) {
            if($query[$prev_flid . "_1_valid"] == "1" || $query[$prev_flid . "_2_valid"] == "1") {
                $processed[$prev_flid] = $query;
            }
        }

        return $processed;
    }
}
