<?php namespace App\Http\Controllers;

use App\Field;
use App\Form;
use App\Project;
use App\Record;
use App\Search;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class ProjectSearchController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Project Search Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles search for a project and global search
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
     * Performs a keyword search on a project and displays results.
     *
     * @param  int $pid - Project ID
     * @return View
     */
    public function keywordSearch($pid, Request $request) {
        if(isset($request->keywords)) {
            //DO THE SEARCH
            $arg = trim($request->keywords);
            $method = intval($request->method);
            $fids = $request->forms;

            // Inform the user about arguments that will be ignored.
            $args = explode(" ", $arg);
            $ignored = Search::showIgnoredArguments($args, ($method == Search::SEARCH_EXACT));
            $args = array_diff($args, $ignored);
            $arg = implode(" ", $args);

            $ignored = implode(" ", $ignored);

            //TODO::Flash ignored warnings

            //Determine if we are searching all forms in project, or just specific ones
            if(in_array("ALL",$fids))
                $forms = Form::where('pid','=',$pid)->get();
            else
                $forms = Form::whereIn('fid',$fids)->get();

            $rids = [];
            foreach ($forms as $form) {
                $search = new Search($form->pid, $form->fid, $arg, $method);
                $rids = array_merge($search->formKeywordSearch(), $rids);
            }

            if(empty($rids))
                $rids = [];

            sort($rids);

            $recBuilder = Record::whereIn("rid", $rids);
            $total = $recBuilder->count();

            $pagination = app('request')->input('page-count') === null ? 10 : app('request')->input('page-count');
            $order = app('request')->input('order') === null ? 'lmd' : app('request')->input('order');
            $order_type = substr($order, 0, 2) === "lm" ? "updated_at" : "rid";
            $order_direction = substr($order, 2, 3) === "a" ? "asc" : "desc";
            $records = $recBuilder->orderBy($order_type, $order_direction)->paginate($pagination);
        } else {
            //INITIAL PAGE VISIT
            $records = [];
            $total = 0;
            $ignored = [];
        }

        $project = ProjectController::getProject($pid);
        $forms = array("ALL" => 'All Forms');
        $allForms = $project->forms()->get();
        foreach($allForms as $f) {
            $forms[$f->fid] = $f->name;
        }

        return view('projectSearch.results', compact("project", "forms", "records", "total", "ignored"));
    }

    /**
     * Executes and displays results for a global multi-project search in Kora3.
     *
     * @param  Request
     * @return View
     */
    public function globalSearch(Request $request) {
        $query = trim($request->gsQuery);
        $method = "GLOBAL";

        $page = (isset($_GET['page'])) ? intval(strip_tags($_GET['page'])) : $page = 1;

        if(Record::isKIDPattern($query) && count(explode(" ", $query)) == 1) { // Query is a KID and only a single query was entered.
            $kid_array = explode("-", $query);

            if(RecordController::validProjFormRecord($kid_array[0], $kid_array[1], $kid_array[2])) {
                if(\Auth::user()->inAFormGroup(FormController::getForm($kid_array[1]))) {
                    return redirect("/projects/" . $kid_array[0] . "/forms/" . $kid_array[1] . "/records/" . $kid_array[2]);
                } else { // User did not have permission to view the record.
                    return redirect()->back()->with('k3_global_error', 'cant_view_record');
                }
            } else { // Record does not exist.
                return redirect()->back()->with('k3_global_error', 'record_doesnt_exist');
            }
        }

        $do_query = true;
        if(Session::has("query") && Session::has("method")) {
            $session_query = Session::get("query");
            $session_method = Session::get("method");

            if($query == $session_query && $method == $session_method) {
                $rids = unserialize(Session::get("rids"));
            } else {
                $do_query = true;
            }
        } else {
            $do_query = true;
        }

        if($do_query) {
            // Inform the user about arguments that will be ignored.
            $query_pieces = explode(" ", $query);
            $ignored = Search::showIgnoredArguments($query_pieces);
            $query_pieces = array_diff($query_pieces, $ignored);
            $query = implode(" ", $query_pieces);

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

        if(empty($rids)) {
            $records = [];
        } else {
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
        $rid_paginator->setPath( config('app.url') . 'globalSearch/');

        $projects = Project::all();
        $projectArrays = [];
        foreach($projects as $project) {
            $projectArrays[] = $project->buildFormSelectorArray();
        }

        $pid = 0;
        return view("projectSearch.results", compact("records", "ignored", "rid_paginator", "pid", "projectArrays"));
    }

    /**
     * Executes the quick search functionality of the global search bar.
     *
     * @param  Request
     * @return array - The results from the quick search
     */
    public function globalQuickSearch(Request $request) {
        $term = $request->searchText;
        $termWC = "%".$term."%";

        //Do the searches
        $projResults = Project::where("name","like",$termWC)->orWhere("slug","like",$termWC)->get();
        $formResults = Form::where("name","like",$termWC)->orWhere("slug","like",$termWC)->get();
        $fieldResults = Field::where("name","like",$termWC)->orWhere("slug","like",$termWC)->get();
        $recordResults = Record::where("kid","=",$term)->get();

        $returnArray = array();

        //Filter those results
        foreach($projResults as $project) {
            if(\Auth::user()->admin || \Auth::user()->inAProjectGroup($project)) {
                $result = "<li>Go to Project: <a data-type=\"Project\" href=\"".action("ProjectController@show",["pid" => $project->pid])
                    ."\">".$project->name;
                if(Project::where("name","=",$project->name)->count() > 1)
                    $result .= " (".$project->slug.")";
                $result .= "</a></li>";
                array_push($returnArray,$result);
            }
        }

        foreach($formResults as $form) {
            if(\Auth::user()->admin || \Auth::user()->inAFormGroup($form)) {
                $result = "<li>Go to Form: <a data-type=\"Form\" href=\"".action("FormController@show",["pid" => $form->pid, "fid" => $form->fid])
                    ."\">".$form->name;
                if(Form::where("name","=",$form->name)->count() > 1)
                    $result .= " (".$form->slug.")";
                $result .= "</a></li>";
                array_push($returnArray,$result);
            }
        }

        foreach($fieldResults as $field) {
            $form = FormController::getForm($field->flid);
            if(\Auth::user()->admin || \Auth::user()->inAFormGroup($form)) {
                $result = "<li>Go to Field: <a data-type=\"Field\" href=\"".action("FieldController@show",["pid" => $field->pid, "fid" => $field->fid, "flid" => $field->flid])
                    ."\">".$field->name;
                if(Field::where("name","=",$field->name)->count() > 1)
                    $result .= " (".$field->slug.")";
                $result .= "</a></li>";
                array_push($returnArray,$result);
            }
        }

        foreach($recordResults as $record) {
            $form = FormController::getForm($record->fid);
            if(\Auth::user()->admin || \Auth::user()->inAFormGroup($form)) {
                $result = "<li>Go to Record: <a data-type=\"Record\" href=\"".action("RecordController@show",["pid" => $record->pid, "fid" => $record->fid, "rid" => $record->rid])
                    ."\">".$record->kid."</a></li>";
                array_push($returnArray,$result);
            }
        }

        return json_encode($returnArray);
    }

    /**
     * Caches global search results for a user.
     *
     * @param  Request
     */
    public function cacheGlobalSearch(Request $request) {
        $html = $request->html;

        $builder = \Auth::user()->gsCaches()->orderby("id","asc");
        $currCache = $builder->get();
        $currCount = $builder->count();
        $firstCache = $builder->first();

        //check if it exists
        foreach($currCache as $cache) {
            if($cache->html == $html) {
                //lets delete the dupe and before we add so the "new" one is moved to the top
                DB::table('global_cache')->where("id","=",$cache->id)->delete();
                //update the count to reflect this deletion
                $currCount--;
            }
        }

        //If 5 results exist, delete the first one
        if($currCount >= 6)
            DB::table('global_cache')->where("id","=",$firstCache->id)->delete();

        DB::table('global_cache')->insert([
            ['user_id' => \Auth::user()->id, 'html' => $html]
        ]);
    }

    /**
     * Clears a user's global search cache.
     */
    public function clearGlobalCache() {
        \Auth::user()->gsCaches()->delete();
    }
}
