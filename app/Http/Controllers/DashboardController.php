<?php namespace App\Http\Controllers;

use App\Field;
use App\Http\Requests\BlockRequest;
use App\Page;
use App\Record;
use App\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Dashboard Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the user dashboard system
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
     * Gets the dashboard view and all the user's blocks.
     *
     * @return View
     */
    public function dashboard() {
        if(Auth::guest())
            return redirect('/');

        //gather all sections for the dashboard and their blocks
        $sections = array();

        $results = DB::table('dashboard_sections')->where('uid','=',Auth::user()->id)->orderBy('order')->get();

        // Create a section and block if there isn't already one and we have a project to add

        if(count($results) == 0) {
            if(sizeof(Auth::User()->allowedProjects()) > 0)
                $this->makeDefaultBlock('Project');
            else
                $this->makeDefaultBlock('Fun'); // If no projects, make an inspiration quote
        }

        foreach($results as $sec) {
            $s = array();
            $s['title'] = $sec->title;
            $s['id'] = $sec->id;

            $blocks = array();
            $blkResults = DB::table('dashboard_blocks')->where('sec_id','=',$sec->id)->orderBy('order')->get();
            foreach($blkResults as $blk) {
                $b = array();
                $b['id'] = $blk->id;
                $b['type'] = $blk->type;

                $options = json_decode($blk->options, true);
                switch($blk->type) {
                    case 'Project':
                        $pid = $options['pid'];
                        $disOpts = $options['displayed'];
                        $hidOpts = $options['hidden'];

                        $project = ProjectController::getProject($pid);

                        $b['pid'] = $pid;
                        $b['name'] = $project->name;
                        if (strlen($project->description) > 206) {
                          $b['description'] = substr($project->description, 0, 206) . "..." ;
                        } else {
                          $b['description'] = $project->description;
                        }

                        $b['displayedOpts'] = [];
                        foreach ($disOpts as $opt) {
                          array_push($b['displayedOpts'], getDashboardBlockLink($blk, $opt));
                        }
                        $b['hiddenOpts'] = $hidOpts;
                        break;
                    case 'Form':
                        $fid = $options['fid'];
                        $disOpts = $options['displayed'];
                        $hidOpts = $options['hidden'];

                        $form = FormController::getForm($fid);

                        $b['fid'] = $fid;
                        $b['name'] = $form->name;
                        if (strlen($form->description) > 206) {
                          $b['description'] = substr($form->description, 0, 206) . "..." ;
                        } else {
                          $b['description'] = $form->description;
                        }

                        $b['displayedOpts'] = [];
                        foreach ($disOpts as $opt) {
                          array_push($b['displayedOpts'], getDashboardBlockLink($blk, $opt));
                        }
                        $b['hiddenOpts'] = $hidOpts;
                        break;
                    case 'Record':
                        $rid = $options[0];

                        $record = RecordController::getRecord($rid);
                        $project = ProjectController::getProject($record->pid);
                        $form = FormController::getForm($record->fid);

                        $firstPage = Page::where("fid","=",$record->fid)->where("sequence","=",0)->first();
                        $firstField = Field::where("page_id","=",$firstPage->id)->where("sequence","=",0)->first();
                        $typedField = $firstField->getTypedFieldFromRID($rid);

                        $b['kid'] = $record->kid;
                        $b['projName'] = $project->name;
                        $b['formName'] = $form->name;
                        $b['field'] = $firstField;
                        $b['dataField'] = $typedField;
                        break;
                    case 'Quote':
                        $quote = Inspiring::quote();

                        $b["quote"] = $quote;
                        break;
                    case 'Twitter':
                        //TODO::Kora Twitter
                        break;
                    case 'Note':
                        $title = $options[0];
                        $text = $options[1];

                        $b['title'] = $title;
                        $b['text'] = $text;
                        break;
                    default:
                        break;
                }

                array_push($blocks,$b);
            }

            $s['blocks'] = $blocks;

            array_push($sections,$s);
        }

        $userProjects = Auth::user()->allowedProjects();
        $userForms = array();
        $userRecords = array();
        foreach($userProjects as $p) {
            $userForms = array_merge($userForms, Auth::user()->allowedForms($p->pid));
            $projRecs = Record::where('pid','=',$p->pid)->pluck('kid')->toArray();
            $userRecords = array_merge($userRecords, $projRecs);
        }

        return view('dashboard', compact('sections', 'userProjects', 'userForms', 'userRecords'));
    }

    private function makeDefaultBlock($type) {
        switch($type) {
            case "Project":
                $sec_id = DB::table('dashboard_sections')->insertGetId(
                    ['uid' => Auth::User()->id, 'title' => 'Projects', 'order' => 0]
                );

                $proj_id = Auth::User()->allowedProjects()[0]->pid;
                $options_string = '{"pid": ' . $proj_id .
                    ', "displayed": ["edit", "search", "form-new", "form-import", "permissions", "presets"]' .
                    ', "hidden": ["importForm"]}';

                DB::table('dashboard_blocks')->insert([
                    'sec_id' => $sec_id,
                    'type' => 'Project',
                    'order' => 0,
                    'options' => $options_string
                ]);

                return DB::table('dashboard_sections')->where('uid','=',Auth::user()->id)->orderBy('order')->get();
                break;
            case "Fun":
                $sec_id = DB::table('dashboard_sections')->insertGetId(
                    ['uid' => Auth::User()->id, 'title' => 'Example', 'order' => 0]
                );

                $options_string = '{}';

                DB::table('dashboard_blocks')->insert([
                    'sec_id' => $sec_id,
                    'type' => 'Quote',
                    'order' => 0,
                    'options' => $options_string
                ]);

                return DB::table('dashboard_sections')->where('uid','=',Auth::user()->id)->orderBy('order')->get();
                break;
        }

        return array();
    }

    public function addSection($request) {

    }

    public function addBlock(BlockRequest $request) {

    }

    /**
     * Deletes a dashboard section along with its blocks.
     *
     * @param  $secID - Section ID
     */
    public function deleteSection($request) {
        $secID = $request->secID;

        $validCnt = DB::table("dashboard_sections")
            ->where("id", "=", $secID)
            ->where("uid", "=", Auth::user()->id)
            ->count();
        if($validCnt == 0)
            return redirect('projects')->with('k3_global_error', 'not_dashboard_owner');

        DB::table("dashboard_blocks")->where("sec_id", "=", $secID)->delete();
        DB::table("dashboard_sections")->where("id", "=", $secID)->delete();

        return response()->json(["status"=>true, "message"=>"Section destroyed", 200]);
    }

    /**
     * Deletes a dashboard block.
     *
     * @param  $blkID - Block ID
     */
    public function deleteBlock($request) {
        $blkID = $request->blkID;
        $secID = $request->secID;

        $validCnt = DB::table("dashboard_sections")
            ->where("id", "=", $secID)
            ->where("uid", "=", Auth::user()->id)
            ->count();
        if($validCnt == 0)
            return redirect('projects')->with('k3_global_error', 'not_dashboard_owner');

        DB::table("dashboard_blocks")
            ->where("id", "=", $blkID)
            ->where("sec_id", "=", $secID)
            ->delete();

        return response()->json(["status"=>true, "message"=>"Block destroyed", 200]);
    }
}
