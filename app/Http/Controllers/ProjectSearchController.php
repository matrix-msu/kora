<?php namespace App\Http\Controllers;

use App\Field;
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
                $forms = Form::where('pid','=',$pid)->get();
            else
                $forms = Form::whereIn('fid',$fids)->get();

            $rids = [];
            foreach ($forms as $form) {
                if(!Auth::user()->inAFormGroup($form))
                    continue;
                $search = new Search($form->pid, $form->fid, $argString, $method);
                $this->imitateMerge($rids, $search->formKeywordSearch());
            }

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
            $initial = true;
        }

        $project = ProjectController::getProject($pid);
        $forms = array("ALL" => 'All Forms');
        $allForms = $project->forms()->get();
        foreach($allForms as $f) {
            $forms[$f->fid] = $f->name;
        }

        return view('projectSearch.results', compact("project", "forms", "records", "total", "ignored", "initial"));
    }

    private function imitateMerge(&$array1, &$array2) {
        foreach($array2 as $i) {
            $array1[] = $i;
        }
    }

    /**
     * Executes and displays results for a global multi-project search in Kora3.
     *
     * @param  Request
     * @return View
     */
    public function globalSearch(Request $request) {
        if(isset($request->keywords)) {
            //DO THE SEARCH
            $argString = trim($request->keywords);
            $method = intval($request->method);
            $pids = $request->projects;

            //Determine if we are searching all forms in project, or just specific ones
            if(in_array("ALL",$pids))
                $projects = Project::all();
            else
                $projects = Project::whereIn('pid',$pids)->get();

            $rids = [];
            foreach($projects as $proj) {
                $forms = $proj->forms()->get();
                foreach($forms as $form) {
                    if(!Auth::user()->inAFormGroup($form))
                        continue;
                    $search = new Search($form->pid, $form->fid, $argString, $method);
                    $this->imitateMerge($rids, $search->formKeywordSearch());
                }
            }

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

        $projects = array("ALL" => 'All Projects');
        $allProjects = Project::all();
        foreach($allProjects as $p) {
            if(!Auth::user()->inAProjectGroup($p))
                continue;
            $projects[$p->pid] = $p->name;
        }

        return view('globalSearch.results', compact("projects", "records", "total", "ignored"));
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
