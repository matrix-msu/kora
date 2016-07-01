<?php namespace App\Http\Controllers;

use App\Form;
use App\Project;
use App\Record;
use App\Search;
use Illuminate\Pagination\LengthAwarePaginator;
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
                flash(self::HELP_MESSAGE . $ignored . '. ');
            }

            $forms = Form::where(function($query) use($fids) {
                foreach($fids as $fid) {
                    $query->orWhere("fid", "=", $fid);
                }
            })->get();

            $rids = [];
            foreach($forms as $form) {
                $search = new Search($form->pid, $form->fid, $arg, $method);
                $rids = array_merge($search->formKeywordSearch2(), $rids);
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

        $records = Record::where(function($query) use ($slice) {
           foreach($slice as $rid) {
               $query->orWhere("rid", "=", $rid);
           }
        })->get();

        $rid_paginator = new LengthAwarePaginator($rids, count($rids), RecordController::RECORDS_PER_PAGE, $page);
        $rid_paginator->appends([
            "query" => $arg,
            "method" => $method,
            "fids" => serialize($fids)
        ]);
        if ($pid == 0) {
            $rid_paginator->setPath( env('BASE_URL') . 'public/keywordSearch/');
        }
        else {
            $rid_paginator->setPath( env('BASE_URL') . 'public/keywordSearch/project/' . $pid);
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

}