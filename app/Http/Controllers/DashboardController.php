<?php namespace App\Http\Controllers;

use Carbon\Carbon;
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

        if(count($results) == 0) {
            $this->addSection('No Section');
            if(sizeof(Auth::User()->allowedProjects()) > 0)
                $this->makeDefaultBlock('Project'); // create section + block
            else
                $this->makeDefaultBlock('Fun'); // If no projects, make an inspiration quote
        } elseif (count($results) > 1) {
            $this->makeNonSectionLast();
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
                        $b['hiddenOpts'] = [];
						foreach ($hidOpts as $opt) {
                          array_push($b['hiddenOpts'], getDashboardBlockLink($blk, $opt));
                        }
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
                        $b['hiddenOpts'] = [];
						foreach ($hidOpts as $opt) {
                          array_push($b['hiddenOpts'], getDashboardBlockLink($blk, $opt));
                        }
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

		// Sort proj and forms alphabetically by name
		usort($userProjects, function($a, $b){ return strcmp(strtolower($a["name"]), strtolower($b["name"])); });
		usort($userForms, function ($a, $b) { return strcmp(strtolower($a['name']), strtolower($b['name'])); });
		// Sort records numerically
		asort($userRecords);

        //Check if were using a special menu link
        $state = isset($request->state) ? $request->state: 0;

        return view('dashboard', compact('sections', 'userProjects', 'userForms', 'userRecords', 'notification', 'state'));
    }

    /**
     * Create a section and add a block to it
     * This is to populate a new users' empty dashboard
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

    /**
     * Creates a block and adds it to a section.
     *
     * @param  BlockRequest $request
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
                    ', "hidden": ["import", "import2k", "export"]}';
                break;
            case 'Form':
                $fid = $request->block_form;
                $optString = '{"fid": ' . $fid .
                    ', "displayed": ["edit", "search", "record-new", "field-new", "permissions", "revisions"]' .
                    ', "hidden": ["import", "import2k", "export"]}';
                break;
            case 'Record':
                $kid = $request->block_record;
                $rids = explode('-',$kid);
                $rid = end($rids);
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
     * Edits an existing section title and/or ordering.
     *
     * @param  BlockRequest $request
     */
    public function editSection (Request $request) {
        if (isset($request->modified_titles)) {
            $sections = explode('_', $request->modified_titles);

            foreach ($sections as $section) {
                $section = explode('-', $section);
                DB::table('dashboard_sections')
                    ->where('uid','=',Auth::user()->id)
                    ->where('id','=',$section[0])
                    ->update(['title' => $section[1]]);
            }
        }

        if (isset($request->sections)) {
            $int = 0;
            foreach($request->sections as $section) {
                DB::table('dashboard_sections')
                    ->where('id','=',$section)
                    ->update(['order' => $int]);

                $int++;
            }
        }
    }

    /**
     * Edits an existing block type and content.  NOT including quick-action
     *
     * @param  BlockRequest $request
     */
    public function editBlock (BlockRequest $request) {
        $secID = $request->section_to_add;
        $type = $request->block_type;
        $optString = '{}';

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
                $rids = explode('-',$kid);
                $rid = end($rids);
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

        DB::table('dashboard_blocks')->where('id','=',$request->selected_id)->update([
            'sec_id' => $secID,
            'type' => $type,
            'options' => $optString
        ]);

        return redirect('dashboard')->with('k3_global_success', 'block_modified');
    }

    /**
     * Edits an existing block's quick action ordering
     *
     * @param  BlockRequest $request
     */
    public function editBlockQuickActions (Request $request) {

		$newOpts = explode(',', $request->options);
		$newHiddenOpts = explode(',', $request->hiddenOpts);

		$oldOpts = DB::table('dashboard_blocks')->where('id','=',$request->selected_id)->first()->options;
		$options = json_decode($oldOpts, true);

		$options['displayed'] = $newOpts;
		$options['hidden'] = $newHiddenOpts;

		$options = json_encode($options);

		DB::table('dashboard_blocks')->where('id','=',$request->selected_id)->update([
			'options' => $options
		]);

		return redirect('dashboard')->with('k3_global_success', 'options_modified');
    }

    /**
    * Edit note block title and content.
    */
    public function editNoteBlock (Request $request) {
        $title = $request->block_note_title;
        $content = $request->block_note_content;
        $optString = '{"title": "' . $title . '", "content": "' . $content .'"}';

        DB::table('dashboard_blocks')->where('id','=',$request->block_id)->update([
            'options' => $optString
        ]);
    }

    public function editBlockOrder (Request $request) {
        $int = 0;
        foreach($request->blocks as $block) {
            DB::table('dashboard_blocks')
                ->where('id','=',$block)
                ->update(['order' => $int]);

            $int++;
        }
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

    public function addSection($sectionTitle) {
        $order = 0;
        $lastSec = DB::table('dashboard_sections')->where('uid','=',Auth::user()->id)->orderBy('order','desc')->first();
        if(!is_null($lastSec))
            $order = $lastSec->order + 1;

        DB::table('dashboard_sections')->insert([
            'uid' => Auth::user()->id,
            'order' => $order,
            'title' => $sectionTitle,
            'created_at' => Carbon::now()->toDateTimeString()
        ]);

        $this->makeNonSectionLast();
        return response()->json(["status"=>true, "message"=>"Section created", "sec_title"=>$sectionTitle, 200]);
    }

    /**
     * Deletes a dashboard section, moves section's blocks to the section above (unless the top section is deleted, in which case the blocks move down a section)
     *
     * @param  int $secID - Section ID
     * @return JsonResponse
     */
    public function deleteSection($sectionID) {
        $validCnt = DB::table("dashboard_sections")
            ->where("id", "=", $sectionID)
            ->where("uid", "=", Auth::user()->id)
            ->count();
        if($validCnt == 0)
            return redirect('projects')->with('k3_global_error', 'not_dashboard_owner');

        // find the index of the selected section
        $allSections = DB::table('dashboard_sections')->where('uid','=',Auth::user()->id)->orderBy('order')->get();
        foreach ($allSections as $key => $section) {
            if ($section->id == $sectionID) {
                $index = $key;
                break;
            }
        }

        // get ID of desired section with key from selected section
        if (isset($allSections[$key - 1])) { // blocks normally move up (previous sect)
            $newID = $allSections[$key - 1]->id;
            $dir = 'up';
        } elseif (isset($allSections[$key + 1])) { // if prev section doesn't exist, move blocks down
            $newID = $allSections[$key + 1]->id;
            $dir = 'down';
        } else { // otherwise we create new unique invisible section to add the blocks to
            $this->addSection('No Section'); // we shouldn't reach this line since this section should always exist
            $newID = $key + 1;
            $dir = 'down';
        }

        // assign new ID to blocks from old section
        DB::table("dashboard_blocks")->where("sec_id", "=", $sectionID)->update(['sec_id' => $newID]);
        $this->reorderBlocks($newID);

        // delete section
        DB::table("dashboard_sections")->where('uid','=',Auth::user()->id)->where("id", "=", $sectionID)->delete();
        $this->reorderSections();

        return response()->json(["status"=>true, "message"=>"Section destroyed", "direction"=>$dir, 200]);
    }

    /**
     * Deletes a dashboard block.
     *
     * @param  int $blkID - Block ID
     * @return JsonResponse
     */
    public function deleteBlock($blkID, $secID) {
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
        $this->reorderBlocks($secID);

        return response()->json(["status"=>true, "message"=>"Block destroyed", 200]);
    }

    private function makeNonSectionLast () {
        $lastSection = DB::table("dashboard_sections")->where('uid','=',Auth::user()->id)->orderBy('order','desc')->first()->order + 1;

        DB::table('dashboard_sections')
            ->where('uid','=',Auth::user()->id)
            ->where('title','=','No Section')
            ->update(['order' => $lastSection]);

        $this->reorderSections();
    }

    private function reorderSections() {
        $sections = DB::table("dashboard_sections")->where('uid','=',Auth::user()->id)->orderBy('order','asc')->get();
        $int = 0;
        foreach ($sections as $section) {
            DB::table('dashboard_sections')->where('id', $section->id)->update(['order' => $int]);
            $int++;
        }
    }

    private function reorderBlocks($secID) {
        $blocks = DB::table("dashboard_blocks")->where("sec_id", "=", $secID)->orderBy('order','asc')->get();
        $int = 0;
        foreach($blocks as $block) {
            DB::table('dashboard_blocks')->where('id', $block->id)->update(['order' => $int]);
            $int++;
        }
    }
}
