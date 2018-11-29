<?php namespace App\Http\Controllers;

use App\AssociatorField;
use App\Field;
use App\Http\Requests\BlockRequest;
use App\Page;
use App\Record;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
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
    public function dashboard(Request $request) {
        if(Auth::guest())
            return redirect('/');

        // should probably make a global notificationsController
        $notification = array(
            'message' => '',
            'description' => '',
            'warning' => false,
            'static' => false
        );

        $session = $request->session()->get('k3_global_success');
        if($session) {
            if($session == 'block_added')
                $notification['message'] = 'Block added successfully!';
        }

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
                        $b['projName'] = ProjectController::getProject($form->pid)->name;

                        $b['fid'] = $fid;
                        $b['pid'] = $form->pid;
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
                        $rid = $options['rid'];

                        $record = RecordController::getRecord($rid);
                        $project = ProjectController::getProject($record->pid);
                        $form = FormController::getForm($record->fid);

                        $firstPage = Page::where("fid","=",$record->fid)->where("sequence","=",0)->first();
                        $firstField = Field::where("page_id","=",$firstPage->id)->where("sequence","=",0)->first();

                        $b['kid'] = $record->kid;
                        $b['rid'] = $rid;
                        $b['fid'] = $record->fid;
                        $b['pid'] = $record->pid;
                        $b['projName'] = $project->name;
                        $b['formName'] = $form->name;
                        $b['fieldName'] = $firstField->name;
                        $b['fieldData'] = AssociatorField::previewData($firstField->flid,$rid,$firstField->type);
                        $b['displayedOpts'] = getDashboardRecordBlockLink($record);
                        break;
                    case 'Quote':
                        $quote = Inspiring::quote();

                        $parts = explode('-', $quote);

                        $b["quote"] = $parts[0];
                        $b["author"] = '-'.$parts[1];
                        break;
                    case 'Twitter':
                        //Need to implement this
                        break;
                    case 'Note':
                        $title = $options['title'];
                        $content = $options['content'];

                        $b['title'] = $title;
                        $b['content'] = $content;
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

        //Check if were using a special menu link
        $state = isset($request->state) ? $request->state: 0;

        return view('dashboard', compact('sections', 'userProjects', 'userForms', 'userRecords', 'notification', 'state'));
    }

    /**
     * Adds a block to a section.
     *
     * @param  string $type - Type of default block to make
     * @return array - Array with the new block
     */
    private function makeDefaultBlock($type) {
        switch($type) {
            case "Project":
                $sec_id = DB::table('dashboard_sections')->insertGetId(
                    ['uid' => Auth::User()->id, 'title' => 'Projects', 'order' => 0]
                );

                $proj_id = Auth::User()->allowedProjects()[0]->pid;
                $options_string = '{"pid": ' . $proj_id .
                    ', "displayed": ["edit", "search", "form-new", "form-import", "permissions", "presets"]' .
                    ', "hidden": []}';

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

    /**
     * Adds a block to a section.
     *
     * @param  BlockRequest $request
     * @return Redirect
     */
    public function addBlock(BlockRequest $request) {
        $secID = $request->section_to_add;
        $type = $request->block_type;
        $optString = '{}';
        $order = 0;
        $lastBlkInSec = DB::table('dashboard_blocks')->where('sec_id','=',$secID)->orderBy('order','desc')->first();
        if(!is_null($lastBlkInSec))
            $order = $lastBlkInSec->order + 1;

        switch($type) {
            case 'Project':
                $pid = $request->block_project;
                $optString = '{"pid": ' . $pid .
                    ', "displayed": ["edit", "search", "form-new", "form-import", "permissions", "presets"]' .
                    ', "hidden": []}';
                break;
            case 'Form':
                $fid = $request->block_form;
                $optString = '{"fid": ' . $fid .
                    ', "displayed": ["edit", "search", "record-new", "field-new", "permissions", "revisions"]' .
                    ', "hidden": []}';
                break;
            case 'Record':
                $kid = $request->block_record;
                $rid = end(explode('-',$kid));
                $optString = '{"rid": ' . $rid . '}';
                break;
            case 'Quote':
                break;
            case 'Twitter':
                break;
            case 'Note':
                $title = $request->block_note_title;
                $content = $request->block_note_content;
                $optString = '{"title": "' . $title . '", "content": "' . $content . '"}';
                break;
            default:
                break;
        }

        DB::table('dashboard_blocks')->insert([
            'sec_id' => $secID,
            'type' => $type,
            'order' => $order,
            'options' => $optString
        ]);

        return redirect('dashboard')->with('k3_global_success', 'block_added');
    }

    /**
     * Validates a block request.
     *
     * @param  BlockRequest $request
     * @return JsonResponse
     */
    public function validateBlockFields(BlockRequest $request) {
        return response()->json(["status"=>true, "message"=>"Block Valid", 200]);
    }

    /**
     * Deletes a dashboard section along with its blocks.
     *
     * @param  int $secID - Section ID
     * @return JsonResponse
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
     * @param  int $blkID - Block ID
     * @return JsonResponse
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

        //reorder remaining blocks in section
        $blocks = DB::table("dashboard_blocks")->where("sec_id", "=", $secID)->orderBy('order','asc')->get();
        $int = 0;
        foreach($blocks as $block) {
            DB::table('dashboard_blocks')
                ->where('id', $block->id)
                ->update(['order' => $int]);

            $int++;
        }

        return response()->json(["status"=>true, "message"=>"Block destroyed", 200]);
    }
}
