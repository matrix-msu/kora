<?php
/**
 * Created by PhpStorm.
 * User: ian.whalen
 * Date: 4/26/2016
 * Time: 11:16 AM
 */

namespace App\Http\Controllers;

use App\Record;
use App\Search;
use Illuminate\Support\Facades\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;

class FormSearchController extends Controller
{
    const HELP_MESSAGE = "The following arguments were ignored by the search: ";

    /**
     * FormSearchController constructor.
     * User must be logged in and active to access methods here.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * Displays the results of a keyword search.
     *
     * @param $pid, project id.
     * @param $fid, form id.
     *
     * @param $pid int, the project id.
     * @param $fid int, the form id.
     * @return \Illuminate\View\View
     */
    public function keywordSearch($pid, $fid) {
        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects/'.$pid);
        }

        $arg = trim((Request::input('query')));
        $method = intval(Request::input('method'));

        $page = (isset($_GET['page'])) ? intval(strip_tags($_GET['page'])) : $page = 1;

        $do_query = false;
        if (Session::has("query")) { // Have we ever searched before?
            $session_query = Session::get("query");
            $session_method = Session::get("method");
            $session_ids = Session::get("searchids");

            if ($session_query == $arg && $session_method == $method && $session_ids == $pid.' '.$fid) { // This is the same search so we shouldn't re-execute the query.
                $rids = unserialize(Session::get("rids"));
            }
            else { // This is a new search, so we have to execute again.
                $do_query = true;
            }
        }
        else { // We have never searched before, so we must execute.
            $do_query = true;
        }

        if ($do_query) {
            // Inform the user about arguments that will be ignored.
            $ignored = Search::showIgnoredArguments($arg);
            $args = explode(" ", $arg);
            $args = array_diff($args, $ignored);
            $arg = implode(" ", $args);

            $ignored = implode(" ", $ignored);

            if ($ignored) {
                flash(self::HELP_MESSAGE . $ignored . '. ');
            }

            $search = new Search($pid, $fid, $arg, $method);

            $rids = $search->formKeywordSearch2();

            if (empty($rids)) {
                $rids = [];
            }

            sort($rids);

            Session::put("rids", serialize($rids));
        }

        Session::put("query", $arg);
        Session::put("method", $method);
        Session::put("searchids",$pid.' '.$fid);

       // $results = $search->formKeywordSearch();

        $form = FormController::getForm($fid);

        $controller = new RecordController();
        $filesize = $controller->getFormFilesize($fid);

//        $results->sortBy("rid");
//
//        $rids = [];
//        foreach($results as $record) {
//            $rids[] = $record->rid;
//        }
//
//        $records = new LengthAwarePaginator($results, $results->count(), 10, $page);
//        $records->appends([
//            "query" => $arg,
//            "method" => $method
//        ]);
//        $records->setPath( env('BASE_URL') . 'public/keywordSearch/project/' . $pid . '/forms/' . $fid);

        //dd($form, $filesize, $records, $search_results, $ignored, $rids);

        $record_count = $page * RecordController::RECORDS_PER_PAGE;

        $slice = array_slice($rids, $record_count - RecordController::RECORDS_PER_PAGE, $record_count);

        $query = Record::where("rid", "=", array_shift($slice));
        foreach ($slice as $rid) {
            $query->orWhere("rid", "=", $rid);
        }
        $records = $query->get();

        $rid_paginator = new LengthAwarePaginator($rids, count($rids), RecordController::RECORDS_PER_PAGE, $page);
        $rid_paginator->appends([
            "query" => $arg,
            "method" => $method
        ]);
        $rid_paginator->setPath( env('BASE_URL') . 'public/keywordSearch/project/' . $pid . '/forms/' . $fid);

        return view('search.results', compact("form", "filesize", "records", "rid_paginator"));
    }

    /**
     * This function deletes the subset of records returned from a search.
     *
     * @param $pid int, the project id.
     * @param $fid int, the form id.
     * @return Response, redirects to the record index.
     */
    public function deleteSubset ($pid, $fid) {
        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects/'.$pid);
        }

        $rids = unserialize(Session::get("rids"));

        $query = Record::where("rid", "=", array_shift($rids));
        foreach ($rids as $rid) {
            $query->orWhere("rid", "=", $rid);
        }
        $query->delete();

        Session::forget("rids");

        $controller = new RecordController();
        return $controller->index($pid, $fid);
    }
}