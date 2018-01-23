<?php namespace App\Http\Controllers;

use App\Record;
use App\Search;
use Illuminate\Support\Facades\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class FormSearchController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Form Search Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles form searches in Kora3
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
     * Performs a keyword search within a form and returns the results view.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
    public function keywordSearch($pid, $fid) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $arg = trim((Request::input('query')));
        $method = intval(Request::input('method'));

        $page = (isset($_GET['page'])) ? intval(strip_tags($_GET['page'])) : $page = 1;

        $do_query = false;
        if(Session::has("query")) { // Have we ever searched before?
            $session_query = Session::get("query");
            $session_method = Session::get("method");
            $session_ids = Session::get("searchids");

            if($session_query == $arg && $session_method == $method && $session_ids == $pid.' '.$fid) // This is the same search so we shouldn't re-execute the query.
                $rids = unserialize(Session::get("rids"));
            else // This is a new search, so we have to execute again.
                $do_query = true;
        } else { // We have never searched before, so we must execute.
            $do_query = true;
        }

        if($do_query) {
            // Inform the user about arguments that will be ignored.
            if($method==Search::SEARCH_EXACT) {
                //Here we treat the argument as one single value
                $ignored = Search::showIgnoredArguments($arg,true);
            } else {
                $ignored = Search::showIgnoredArguments($arg);
                $args = explode(" ", $arg);
                $args = array_diff($args, $ignored);
                $arg = implode(" ", $args);
            }

            $ignored = implode(" ", $ignored);

            if($ignored)
                //TODO:: flash("The following arguments were ignored by the search: " . $ignored . '. ');

            $search = new Search($pid, $fid, $arg, $method);

            $rids = $search->formKeywordSearch();

            if(empty($rids))
                $rids = [];

            sort($rids);

            Session::put("rids", serialize($rids));
        }

        Session::put("query", $arg);
        Session::put("method", $method);
        Session::put("searchids",$pid.' '.$fid);

        $form = FormController::getForm($fid);

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
        $rid_paginator->appends([
            "query" => $arg,
            "method" => $method
        ]);
        $rid_paginator->setPath( config('app.url') . 'keywordSearch/project/' . $pid . '/forms/' . $fid);

        return view('search.results', compact("form", "filesize", "records", "rid_paginator", "ignored"));
    }

    /**
     * Deletes a subset of records based upon a search.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
    public function deleteSubset($pid, $fid) {
        $rids = Session::get("rids");
        $rids = is_array($rids) ? $rids : unserialize($rids);

        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $query = Record::where("rid", "=", array_shift($rids));
        foreach($rids as $rid) {
            $query->orWhere("rid", "=", $rid);
        }
        $query->delete();

        Session::forget("rids");

        $controller = new RecordController();
        return $controller->index($pid, $fid);
    }
}