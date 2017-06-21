<?php namespace App\Http\Controllers;

use App\Form;
use App\Project;
use App\Record;
use App\Search;
use App\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class ProjectSearchController extends Controller
{
    /**
     * FormSearchController constructor.
     * User must be logged in and active to access methods here.
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * Keyword search for a project (or projects).
     *
     * @param int $pid
     * @return \Illuminate\View\View
     */
    public function keywordSearch($pid = 0) {
        $arg = trim((Request::input('query')));
        $method = intval(Request::input('method'));
        $fids = Request::input("forms");

        $page = (isset($_GET['page'])) ? intval(strip_tags($_GET['page'])) : $page = 1;

        $do_query = false;
        if (Session::has("query") && Session::has("fids")) { // Have we ever searched before?
            $session_query = Session::get("query");
            $session_method = Session::get("method");
            $session_fids = unserialize(Session::get("fids"));

            if ($session_query == $arg &&
                $session_method == $method &&
                array_diff($session_fids, $fids) === array_diff($fids, $session_fids)) { // This is the same search so we shouldn't re-execute the query.
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
                flash(FormSearchController::HELP_MESSAGE . $ignored . '. ');
            }

            $forms = Form::where(function($query) use($fids) {
                foreach($fids as $fid) {
                    $query->orWhere("fid", "=", $fid);
                }
            })->get();

            $rids = [];
            foreach($forms as $form) {
                $search = new Search($form->pid, $form->fid, $arg, $method);
                $rids = array_merge($search->formKeywordSearch(), $rids);
            }

            if (empty($rids)) {
                $rids = [];
            }

            sort($rids);

            Session::put("rids", serialize($rids));
            Session::put("fids", serialize($fids));
        }

        Session::put("query", $arg);
        Session::put("method", $method);

        $record_count = $page * RecordController::RECORDS_PER_PAGE;
        $slice = array_slice($rids, $record_count - RecordController::RECORDS_PER_PAGE, $record_count);

        if (empty($rids)) {
            $records = [];
        }
        else {
            $records = Record::where(function($query) use ($slice) {
                foreach($slice as $rid) {
                    $query->orWhere("rid", "=", $rid);
                }
            })->get();
        }
        $rid_paginator = new LengthAwarePaginator($rids, count($rids), RecordController::RECORDS_PER_PAGE, $page);
        $rid_paginator->appends([
            "query" => $arg,
            "method" => $method,
            "fids" => serialize($fids)
        ]);
        if ($pid == 0) {
            $rid_paginator->setPath( env('BASE_URL') . 'keywordSearch/');
        }
        else {
            $rid_paginator->setPath( env('BASE_URL') . 'keywordSearch/project/' . $pid);
        }

        if ($pid == 0) {
            $projects = Project::all();
            $projectArrays = [];
            foreach ($projects as $project) {
                $projectArrays[] = $project->buildFormSelectorArray();
            }
        }
        else {
            $project = ProjectController::getProject($pid);
            $projectArrays = [$project->buildFormSelectorArray()];
        }
        return view("projectSearch.results", compact("records", "ignored", "rid_paginator", "pid", "projectArrays"));
    }

    /**
     * Global search executed by the navbar search box.
     * Executes as an exact search on every form in the system.
     * If the input is in the form of a KID, it will redirect to the Record's show page if it exists.
     *
     * @return \Illuminate\View\View
     */
    public function globalSearch() {
        $query = trim(Request::input("query"));
        $method = "GLOBAL";

        $page = (isset($_GET['page'])) ? intval(strip_tags($_GET['page'])) : $page = 1;

        if (Record::isKIDPattern($query) && count(explode(" ", $query)) == 1) { // Query is a KID and only a single query was entered.
            $kid_array = explode("-", $query);

            if (RecordController::validProjFormRecord($kid_array[0], $kid_array[1], $kid_array[2])) {
                if (\Auth::user()->inAFormGroup(FormController::getForm($kid_array[1]))) {
                    return redirect("/projects/" . $kid_array[0] . "/forms/" . $kid_array[1] . "/records/" . $kid_array[2]);
                }
                else { // User did not have permission to view the record.
                    flash()->overlay(trans('controller_record.viewper'), trans('controller_record.whoops'));
                    return redirect()->back();
                }
            }
            else { // Record does not exist.
                flash()->overlay(trans("records_show.exist"), trans('controller_record.whoops'));
                return redirect()->back();
            }
        }

        $do_query = true;
        if (Session::has("query") && Session::has("method") ) {
            $session_query = Session::get("query");
            $session_method = Session::get("method");

            if ($query == $session_query && $method == $session_method) {
                $rids = unserialize(Session::get("rids"));
            }
            else {
                $do_query = true;
            }
        }
        else {
            $do_query = true;
        }

        if ($do_query) {
            // Inform the user about arguments that will be ignored.
            $ignored = Search::showIgnoredArguments($query);
            $query_pieces = explode(" ", $query);
            $query_pieces = array_diff($query_pieces, $ignored);
            $query = implode(" ", $query_pieces);

            $ignored = implode(" ", $ignored);

            if ($ignored) {
                flash(FormSearchController::HELP_MESSAGE . $ignored . '. ');
            }

            $user = Auth::user();

            $projects = $user->allowedProjects();

            $rids = [];
            foreach($projects as $project) {
                $forms = $user->allowedForms($project->pid);

                foreach($forms as $form) {
                    // Global search is always an exact search.
                    $search = new Search($form->pid, $form->fid, $query, Search::SEARCH_EXACT);
                    $rids = array_merge($search->formKeywordSearch(), $rids);
                }
            }

            Session::put("rids", serialize($rids));
        }

        Session::put("query", $query);
        Session::put("method", $method);

        $record_count = $page * RecordController::RECORDS_PER_PAGE;
        $slice = array_slice($rids, $record_count - RecordController::RECORDS_PER_PAGE, $record_count);

        if (empty($rids)) {
            $records = [];
        }
        else {
            $records = Record::where(function($builder) use ($slice) {
                foreach($slice as $rid) {
                    $builder->orWhere("rid", "=", $rid);
                }
            })->get();
        }
        $rid_paginator = new LengthAwarePaginator($rids, count($rids), RecordController::RECORDS_PER_PAGE, $page);
        $rid_paginator->appends([
            "query" => $query,
            "method" => $method,
        ]);
        $rid_paginator->setPath( env('BASE_URL') . 'globalSearch/');

        $projects = Project::all();
        $projectArrays = [];
        foreach ($projects as $project) {
            $projectArrays[] = $project->buildFormSelectorArray();
        }

        $pid = 0;
        return view("projectSearch.results", compact("records", "ignored", "rid_paginator", "pid", "projectArrays"));
    }
}