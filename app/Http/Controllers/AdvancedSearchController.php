<?php namespace App\Http\Controllers;


use App\Field;
use Illuminate\Http\Request;

class AdvancedSearchController extends Controller {

    /**
     * User must be logged in and admin to access views in this controller.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * Advanced search index.
     *
     * @param $pid, project id.
     * @param $fid, form id.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($pid, $fid) {
        if (! FormController::validProjForm($pid, $fid)) {
            return redirect("projects/". $pid);
        }

        $fields = Field::where("fid", "=", $fid)->get();
        return view("advancedSearch.index", compact("pid", "fid", "fields"));
    }

    /**
     * Execute an advanced search.
     *
     * @param $pid, project id
     * @param $fid, form id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse |\Illuminate\Routing\Redirector
     */
    public function search($pid, $fid, Request $request) {
        if (! FormController::validProjForm($pid, $fid)) {
            return redirect("projects/". $pid);
        }

        $form = FormController::getForm($fid);
        $stash = $form->getFieldStash();

        $request = $request->all();
        array_pop($request); // Pop off the CSRF token.

        $results = [];

        dd($this->processRequest($request));

        foreach ($this->processRequest($request) as $flid => $query) {
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
     * Processes the request into a associative array with the following format:
     *      $processed[flid] => search query
     * Ensures all inputs are valid.
     *
     * @param array $request
     * @return array $processed
     */
    private function processRequest(array $request) {
        $processed = [];
        $query = [];
        // Process the search request.
        $prev_flid = -1;
        foreach($request as $key => $value) {
            $flid = explode("_", $key)[0];
            if ($flid != $prev_flid) { // On a new input group.

                // Only add the new query if it is valid.
                if (isset($query[$prev_flid . "_valid"]) && isset($query[$prev_flid . "_dropdown"])) {
                    if ($query[$prev_flid . "_valid"] == "1") {
                        $processed[$prev_flid] = $query;
                    }
                }
                else if (isset($query[$prev_flid . "_1_valid"]) && isset($query[$prev_flid . "_2_valid"])) {
                    if ($query[$prev_flid . "_1_valid"] == "1" || $query[$prev_flid . "_2_valid"] == "1") {
                        $processed[$prev_flid] = $query;
                    }
                }

                $query = [];
                $query[$key] = $value;
                $prev_flid = $flid;
            }
            else {
                $query[$key] = $value;
                $prev_flid = $flid;
            }
        }

        // Check the last query.
        if (isset($query[$prev_flid . "_valid"])) {
            if ($query[$prev_flid . "_valid"] == "1") {
                $processed[$prev_flid] = $query;
            }
        }
        else if (isset($query[$prev_flid . "_1_valid"]) && isset($query[$prev_flid . "_2_valid"])) {
            if ($query[$prev_flid . "_1_valid"] == "1" || $query[$prev_flid . "_2_valid"] == "1") {
                $processed[$prev_flid] = $query;
            }
        }

        return $processed;
    }
}
