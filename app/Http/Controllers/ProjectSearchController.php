<?php namespace App\Http\Controllers;

use App\Form;
use App\Project;
use App\Record;
use App\Search;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $initial = false;

        if(isset($request->keywords)) {
            //DO THE SEARCH
            $argString = trim($request->keywords);
            $method = intval($request->method);
            $fids = $request->forms;

            //Determine if we are searching all forms in project, or just specific ones
            if(in_array("ALL",$fids))
                $forms = Form::where('project_id','=',$pid)->get();
            else
                $forms = Form::whereIn('id',$fids)->get();

            $first = true;
            $foundRecords = null;
            foreach($forms as $form) {
                if(!Auth::user()->inAFormGroup($form))
                    continue;

                $recordMod = new Record(array(),$form->id);

                //This line breaks apart the space separated keywords, but also maintains words held together in double quotes
                $keys = str_getcsv($argString, ' ');
                $search = new Search($pid, $form->id, $keys, $method);

                $rids = $search->formKeywordSearch();

                if($first) {
                    $foundRecords = $recordMod->newQuery()->select('kid', 'updated_at')->whereIn("id", $rids);
                    $first = false;
                } else
                    $foundRecords = $foundRecords->union($recordMod->newQuery()->select('kid','updated_at')->whereIn("id", $rids));
            }

            if(!is_null($foundRecords))
                $total = $foundRecords->get()->count();
            else
                $total = 0;

            $pageCount = $request->input('page-count') === null ? 10 : app('request')->input('page-count');
            $page = $request->input('page') === null ? 1 : app('request')->input('page');
            $order = app('request')->input('order') === null ? 'lmd' : app('request')->input('order');
            $order_type = substr($order, 0, 2) === "lm" ? "updated_at" : "kid";
            $order_direction = substr($order, 2, 3) === "a" ? "asc" : "desc";

            if(!is_null($foundRecords))
                $records = $foundRecords->orderBy($order_type, $order_direction)->skip(($page-1)*$pageCount)->take($pageCount)->pluck('kid')->toArray();
            else
                $records = [];
        } else {
            //INITIAL PAGE VISIT
            $records = [];
            $total = 0;
            $initial = true;
            $page = 1;
            $pageCount = 10;
        }

        $project = ProjectController::getProject($pid);
        $forms = array("ALL" => 'All Forms');
        $allForms = $project->forms()->get();
        foreach($allForms as $f) {
            $forms[$f->id] = $f->name;
        }

        return view('projectSearch.results', compact("project", "forms", "records", "total", "initial", "page", "pageCount"));
    }

    private function imitateMerge(&$array1, &$array2) {
        foreach($array2 as $i) {
            $array1[] = $i;
        }
    }

    /**
     * Executes and displays results for a global multi-project search in kora.
     *
     * @param  Request
     * @return View
     */
    public function globalSearch(Request $request) {
        if(isset($request->keywords) && $request->keywords != '') {
            //DO THE SEARCH
            $argString = trim($request->keywords);
            $method = intval($request->method);
            $pids = $request->projects;

            //Determine if we are searching all forms in project, or just specific ones
            if(in_array("ALL",$pids))
                $projects = Project::all();
            else
                $projects = Project::whereIn('pid',$pids)->get();

            $first = true;
            $foundRecords = null;
            foreach($projects as $proj) {
                $forms = $proj->forms()->get();
                foreach($forms as $form) {
                    if(!Auth::user()->inAFormGroup($form))
                        continue;

                    $recordMod = new Record(array(),$form->id);

                    //This line breaks apart the space separated keywords, but also maintains words held together in double quotes
                    $keys = str_getcsv($argString, ' ');
                    $search = new Search($proj->id, $form->id, $keys, $method);

                    $rids = $search->formKeywordSearch();

                    if($first) {
                        $foundRecords = $recordMod->newQuery()->select('kid', 'updated_at')->whereIn("id", $rids);
                        $first = false;
                    } else
                        $foundRecords = $foundRecords->union($recordMod->newQuery()->select('kid','updated_at')->whereIn("id", $rids));
                }
            }

            if(!is_null($foundRecords))
                $total = $foundRecords->get()->count();
            else
                $total = 0;

            $pageCount = $request->input('page-count') === null ? 10 : app('request')->input('page-count');
            $page = $request->input('page') === null ? 1 : app('request')->input('page');
            $order = app('request')->input('order') === null ? 'lmd' : app('request')->input('order');
            $order_type = substr($order, 0, 2) === "lm" ? "updated_at" : "kid";
            $order_direction = substr($order, 2, 3) === "a" ? "asc" : "desc";

            if(!is_null($foundRecords))
                $records = $foundRecords->orderBy($order_type, $order_direction)->skip(($page-1)*$pageCount)->take($pageCount)->pluck('kid')->toArray();
            else
                $records = [];
        } else {
            //INITIAL PAGE VISIT
            $records = [];
            $total = 0;
            $page = 1;
            $pageCount = 10;
        }

        $projects = array("ALL" => 'All Projects');
        $allProjects = Project::all();
        foreach($allProjects as $p) {
            if(!Auth::user()->inAProjectGroup($p))
                continue;
            $projects[$p->id] = $p->name;
        }

        if(isset($request->keywords) && $request->keywords != '') {
            // Search through Forms, Fields, and Projects and display results as a card
            $termWC = '%' . $request->keywords . '%';
            $projResults = Project::where("name", "like", $termWC)->get();
            $formResults = Form::where("name", "like", $termWC)->get();
            $fieldResults = Form::whereRaw("layout->\"$.fields.*.name\" like \"$termWC\"")->get();
            $projectArray = array();
            $formArray = array();
            $fieldArray = array();
            foreach ($projResults as $project) {
                if (\Auth::user()->admin || \Auth::user()->inAProjectGroup($project))
                    array_push($projectArray, $project);
            }
            foreach ($formResults as $form) {
                if (\Auth::user()->admin || \Auth::user()->inAFormGroup($form))
                    array_push($formArray, $form);
            }
            foreach ($fieldResults as $form) {
                $lo = $form->layout;
                if (\Auth::user()->admin || \Auth::user()->inAFormGroup($form)) {
                    foreach ($lo['fields'] as $flid => $field) {
                        if(strpos($field['name'], $request->keywords) !== false) {
                            $field['formModel'] = $form; //Need for view
                            $fieldArray[$flid] = $field;
                        }
                    }
                }
            }
        } else {
            $projectArray = [];
            $formArray = [];
            $fieldArray = [];
        }

        return view('globalSearch.results', compact(
            "projects", "records", "total",
            'projectArray', 'formArray', 'fieldArray', "page", "pageCount"
        ));
    }

    /**
     * Executes the quick search functionality of the global search bar.
     *
     * @param  Request
     * @return array - The results from the quick search
     */
    public function globalQuickSearch(Request $request) {
        if(!is_null($request->searchText))
            $term = $request->searchText;
        else
            $term = $request->keywords;
        $termWC = "%".$term."%";

        //Do the searches
        $projResults = Project::where("name","like",$termWC)->get();
        $formResults = Form::where("name","like",$termWC)->get();
        $fieldResults = Form::whereRaw("layout->\"$.fields.*.name\" like \"$termWC\"")->get();
        $recordResult = RecordController::getRecord($term);
        $legacyRecordResult = RecordController::getRecordByLegacy($term);

        $returnArray = array();

        //Filter those results
        foreach($projResults as $project) {
            if(\Auth::user()->admin || \Auth::user()->inAProjectGroup($project)) {
                $result = "<li>Go to Project: <a data-type=\"Project\" href=\"".action("ProjectController@show",["pid" => $project->id])
                    ."\">".$project->name;
                if(Project::where("name","=",$project->name)->count() > 1)
                    $result .= " (".$project->internal_name.")";
                $result .= "</a></li>";
                array_push($returnArray,$result);
            }
        }

        foreach($formResults as $form) {
            if(\Auth::user()->admin || \Auth::user()->inAFormGroup($form)) {
                $result = "<li>Go to Form: <a data-type=\"Form\" href=\"".action("FormController@show",["pid" => $form->project_id, "fid" => $form->id])
                    ."\">".$form->name;
                if(Form::where("name","=",$form->name)->count() > 1)
                    $result .= " (".$form->internal_name.")";
                $result .= "</a></li>";
                array_push($returnArray,$result);
            }
        }

        foreach($fieldResults as $form) {
            $lo = $form->layout;
            if(\Auth::user()->admin || \Auth::user()->inAFormGroup($form)) {
                foreach($lo['fields'] as $flid => $field) {
                    if(strpos($field['name'], $term) !== false) {
                        $result = "<li>Go to Field: <a data-type=\"Field\" href=\"".
                            action("FieldController@show",["pid" => $form->project_id, "fid" => $form->id, "flid" => $flid])."\">".$field['name'];
                        $result .= " (".$form->name.")";
                        $result .= "</a></li>";
                        array_push($returnArray,$result);
                    }
                }
            }
        }

        if(!is_null($recordResult)) {
            $form = FormController::getForm($recordResult->form_id);
            if (\Auth::user()->admin || \Auth::user()->inAFormGroup($form)) {
                $result = "<li>Go to Record: <a data-type=\"Record\" href=\"" .
                    action("RecordController@show", ["pid" => $recordResult->project_id, "fid" => $recordResult->form_id, "rid" => $recordResult->id])
                    . "\">" . $recordResult->kid . "</a></li>";
                array_push($returnArray, $result);
            }
        }

        if(!is_null($legacyRecordResult)) {
            $form = FormController::getForm($legacyRecordResult->form_id);
            if (\Auth::user()->admin || \Auth::user()->inAFormGroup($form)) {
                $result = "<li>Go to Record: <a data-type=\"Record\" href=\"" .
                    action("RecordController@show", ["pid" => $legacyRecordResult->project_id, "fid" => $legacyRecordResult->form_id, "rid" => $legacyRecordResult->id])
                    . "\">" . $legacyRecordResult->kid . " (".$legacyRecordResult->legacy_kid.")</a></li>";
                array_push($returnArray, $result);
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

        if(!is_null(\Auth::user()->gsCaches())) {
            $builder = \Auth::user()->gsCaches()->orderby("id", "asc");
            $currCache = $builder->get();
            $currCount = $builder->count();
            $firstCache = $builder->first();
        } else {
            $currCache = [];
            $currCount = 0;
            $firstCache = null;
        }

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
