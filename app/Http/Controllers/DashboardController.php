<?php namespace App\Http\Controllers;

use App\Form;
use Carbon\Carbon;
use App\Http\Requests\BlockRequest;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

        $results = DB::table('dashboard_sections')->where('user_id','=',Auth::user()->id)->orderBy('order')->get();

        if(count($results) == 0) { // if we have no sections, then we have no blocks
            $this->addSection('No Section');

            if(sizeof(Auth::User()->allowedProjects()) > 0)
                $results = $this->makeDefaultBlock('Project'); // create section + block
            else
                $results = $this->makeDefaultBlock('Fun'); // If no projects, make an inspiration quote
        } else
            $this->makeNonSectionFirst();

        foreach($results as $sec) {
            $blocks = array();
            $blkResults = DB::table('dashboard_blocks')->where('section_id','=',$sec->id)->orderBy('order')->get();

            foreach($blkResults as $blk) {
                $b = array();

                $options = json_decode($blk->options, true);
                switch($blk->type) {
                    case 'Project':
                        $pid = $options['pid'];
                        $disOpts = $options['displayed'];
                        $hidOpts = $options['hidden'];

                        $project = ProjectController::getProject($pid);

                        if(!is_object($project)) {
                            $this->deleteBlock($blk->id, $blk->section_id);
                            break;
                        }

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

                        if(!is_object($form)) {
                            $this->deleteBlock($blk->id, $blk->section_id);
                            break;
                        }

                        $b['projName'] = ProjectController::getProject($form->project_id)->name;

                        $b['fid'] = $fid;
                        $b['pid'] = $form->project_id;
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
                        $kid = $options['kid'];

                        $record = RecordController::getRecord($kid);

                        if(!is_object($record)) {
                            $this->deleteBlock($blk->id, $blk->section_id);
                            $notification['message'] = 'One or more Record blocks were invalid!';
                            $notification['description'] = 'Either KID was invalid, or record no longer exists';
                            $notification['warning'] = 'True';
                            break;
                        }

                        $project = ProjectController::getProject($record->project_id);
                        $form = FormController::getForm($record->form_id);

                        //Get first field in record for preview
                        $layout = $form->layout;
                        $foundField = false;
                        foreach($layout['pages'] as $page) {
                            if(!empty($page['flids'])) {
                                $previewFlid = $page['flids'][0];
                                $previewField = $layout['fields'][$previewFlid];
                                $foundField = true;
                                break;
                            }
                        }

                        $b['kid'] = $record->kid;
                        $b['rid'] = $record->id;
                        $b['fid'] = $record->form_id;
                        $b['pid'] = $record->project_id;
                        $b['projName'] = $project->name;
                        $b['formName'] = $form->name;
                        $b['fieldName'] = $foundField ? $previewField['name'] : "No Record Fields Found!";
                        $b['fieldData'] = $foundField ? $this->getPreviewValues($previewFlid,$previewField,$kid) : "No Record Fields Found!";
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

                if($blk->type === 'Note' || $blk->type === 'Quote' || $blk->type === 'Twitter') {// if twitter, count($b) = 0; if quote, count($b) = 3; note, count($b) = 2;
                    $b['id'] = $blk->id;
                    $b['type'] = $blk->type;
                    array_push($blocks,$b);
                } else if(sizeof($b) > 2) { // projects, forms, and records should all have more than 2 entries in $b, if not, then the proj/form/rec was probably deleted, and should not be added to this array.  otherwise the page will not load
                    $b['id'] = $blk->id;
                    $b['type'] = $blk->type;
                    array_push($blocks,$b);
                }
            }

            $s = array();
            $s['title'] = $sec->title;
            $s['id'] = $sec->id;
            $s['blocks'] = $blocks;
            array_push($sections,$s);
        }

        $userProjects = Auth::user()->allowedProjects();
        $userForms = array();
        foreach($userProjects as $p) {
            $userForms = array_merge($userForms, Auth::user()->allowedForms($p->id));
        }

		// Sort proj and forms alphabetically by name
		usort($userProjects, function($a, $b){ return strcmp(strtolower($a["name"]), strtolower($b["name"])); });
		usort($userForms, function ($a, $b) { return strcmp(strtolower($a['name']), strtolower($b['name'])); });

        //Check if were using a special menu link
        $state = isset($request->state) ? $request->state: 0;

        return view('dashboard', compact('sections', 'userProjects', 'userForms', 'notification', 'state'));
    }

    /**
     * Create a section and add a block to it. This is to populate a new users' empty dashboard.
     *
     * @param  string $type - Type of default block to make
     * @return array - Array with the new block
     */
    private function makeDefaultBlock($type) {
        switch($type) {
            case "Project":
                $section_id = DB::table('dashboard_sections')->insertGetId(
                    ['user_id' => Auth::User()->id, 'title' => 'Projects', 'order' => 1]
                );

                $proj_id = Auth::User()->allowedProjects()[0]->id;
                $options_string = '{"pid": ' . $proj_id .
                    ', "displayed": ["edit", "search", "form-new", "form-import", "permissions", "presets"]' .
                    ', "hidden": ["import", "import2k", "export"]}';

                DB::table('dashboard_blocks')->insert([
                    'section_id' => $section_id,
                    'type' => 'Project',
                    'order' => 0,
                    'options' => $options_string
                ]);

                return DB::table('dashboard_sections')->where('user_id','=',Auth::user()->id)->orderBy('order')->get();
                break;
            case "Fun":
                $section_id = DB::table('dashboard_sections')->insertGetId(
                    ['user_id' => Auth::User()->id, 'title' => 'Example', 'order' => 1]
                );

                $options_string = '{}';

                DB::table('dashboard_blocks')->insert([
                    'section_id' => $section_id,
                    'type' => 'Quote',
                    'order' => 0,
                    'options' => $options_string
                ]);

                return DB::table('dashboard_sections')->where('user_id','=',Auth::user()->id)->orderBy('order')->get();
                break;
        }

        return array();
    }

    /**
     * Add a new section to dashboard.
     *
     * @param  string $sectionTitle - Title of new section
     * @return JsonResponse
     */
    public function addSection($sectionTitle) {
        $order = 0;
        $lastSec = DB::table('dashboard_sections')->where('user_id','=',Auth::user()->id)->orderBy('order','desc')->first();
        if(!is_null($lastSec))
            $order = $lastSec->order + 1;

        DB::table('dashboard_sections')->insert([
            'user_id' => Auth::user()->id,
            'order' => $order,
            'title' => $sectionTitle,
            'created_at' => Carbon::now()->toDateTimeString()
        ]);

        $section_id = DB::table('dashboard_sections')
            ->where('user_id','=',Auth::user()->id)
            ->where('title','=',$sectionTitle)
            ->first()->id;

        $this->makeNonSectionFirst();
        return response()->json(["status"=>true, "message"=>"Section created", "sec_title"=>$sectionTitle, "section_id"=>$section_id, 200]);
    }

    /**
     * Creates a block and adds it to a section.
     *
     * @param  BlockRequest $request
     * @return Redirect
     */
    public function addBlock(BlockRequest $request) {
        $secID = $request->section_to_add;
        $type = $request->block_type;
        $optString = '{}';
        $order = 0;
        $lastBlkInSec = DB::table('dashboard_blocks')->where('section_id','=',$secID)->orderBy('order','desc')->first();
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
                    ', "displayed": ["edit", "search", "record-new", "field-new", "form-permissions", "revisions"]' .
                    ', "hidden": ["import", "batch", "export-records", "assoc-permissions", "export-form"]}';
                break;
            case 'Record':
                $kid = $request->block_record;
                $optString = '{"kid": "' . $kid . '"}';
                break;
            case 'Quote':
                break;
            case 'Twitter':
                break;
            case 'Note':
                $noteArray = [];
                $noteArray['title'] = $request->block_note_title;
                $noteArray['content'] = $request->block_note_content;
                $optString = json_encode($noteArray);
                break;
            default:
                break;
        }

        DB::table('dashboard_blocks')->insert([
            'section_id' => $secID,
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
                    ->where('user_id','=',Auth::user()->id)
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
     * Edits an existing block type and content. NOT including quick-action.
     *
     * @param  BlockRequest $request
     * @return Redirect
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
                    ', "hidden": ["import", "import2k", "export"]}';
                break;
            case 'Form':
                $fid = $request->block_form;
                $optString = '{"fid": ' . $fid .
                    ', "displayed": ["edit", "search", "record-new", "field-new", "permissions", "revisions"]' .
                    ', "hidden": ["import", "batch", "export-records", "assoc-permissions", "export-form"]}';
                break;
            case 'Record':
                $kid = $request->block_record;
                $optString = '{"kid": ' . $kid . '}';
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
            'section_id' => $secID,
            'type' => $type,
            'options' => $optString
        ]);

        return redirect('dashboard')->with('k3_global_success', 'block_modified');
    }

    /**
     * Edits an existing block's quick action ordering.
     *
     * @param  Request $request
     * @return Response
     */
    public function editBlockQuickActions(Request $request) {
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

        return response()->json(["status"=>true, "message"=>"Quick Actions Updated", 200]);
    }

    /**
     * Edit note block title and content.
     *
     * @param  Request $request
     */
    public function editNoteBlock(Request $request) {
        $noteArray = [];
        $noteArray['title'] = $request->block_note_title;
        $noteArray['content'] = $request->block_note_content;
        $optString = json_encode($noteArray);

        DB::table('dashboard_blocks')->where('id','=',$request->block_id)->update([
            'options' => $optString
        ]);
    }

    /**
     * Edits the order of blocks in a section from a JS request.
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function editBlockOrder(Request $request) {
        $int = 0;
        foreach($request->blocks as $block) {
            DB::table('dashboard_blocks')
                ->where('id','=',$block)
                ->update([
					'section_id' => $request->section,
					'order' => $int
				]);

            $int++;
        }

		return response()->json(["status"=>true, "message"=>"blocks_successfully_reordered", 200]);
    }

    /**
     * Deletes a dashboard section, moves section's blocks to the section above (unless the top section is deleted, in which case the blocks move down a section).
     *
     * @param  int $sectionID - Section ID
     * @return JsonResponse
     */
    public function deleteSection($sectionID) {
        $validCnt = DB::table("dashboard_sections")
            ->where("id", "=", $sectionID)
            ->where("user_id", "=", Auth::user()->id)
            ->count();
        if($validCnt == 0)
            return redirect('projects')->with('k3_global_error', 'not_dashboard_owner');

        // find the index of the selected section
        $allSections = DB::table('dashboard_sections')->where('user_id','=',Auth::user()->id)->orderBy('order')->get();
        foreach ($allSections as $key => $section) {
            if ($section->id == $sectionID) {
                $index = $key;
                break;
            }
        }

        // get ID of desired section with key from selected section
        if (isset($allSections[$index - 1])) { // blocks normally move up (previous sect)
            $newID = $allSections[$index - 1]->id;
        } elseif (isset($allSections[$index + 1])) { // if prev section doesn't exist, move blocks down
            $newID = $allSections[$index + 1]->id;
        } else { // otherwise we create new unique invisible section to add the blocks to
            $this->addSection('No Section'); // we shouldn't reach this line since this section should always exist
            $newID = $index + 1;
        }

        // assign new ID to blocks from old section
        DB::table("dashboard_blocks")->where("section_id", "=", $sectionID)->update(['section_id' => $newID]);
        $this->reorderBlocks($newID);

        // delete section
        DB::table("dashboard_sections")->where('user_id','=',Auth::user()->id)->where("id", "=", $sectionID)->delete();
        $this->makeNonSectionFirst();

        return response()->json(["status"=>true, "message"=>"Section destroyed", 'section'=>$newID, 200]);
    }

    /**
     * Deletes a dashboard block.
     *
     * @param  int $blkID - Block ID
     * @param  int $secID - Section ID
     * @return JsonResponse
     */
    public function deleteBlock($blkID, $secID) {
        $validCnt = DB::table("dashboard_sections")
            ->where("id", "=", $secID)
            ->where("user_id", "=", Auth::user()->id)
            ->count();
        if($validCnt == 0)
            return redirect('projects')->with('k3_global_error', 'not_dashboard_owner');

        DB::table("dashboard_blocks")
            ->where("id", "=", $blkID)
            ->where("section_id", "=", $secID)
            ->delete();

        //reorder remaining blocks in section
        $this->reorderBlocks($secID);

        return redirect('dashboard')->with('k3_global_success', 'block_destroyed');
    }

    /**
     * Reorders blocks within a section.
     *
     * @param  int $secID - Section ID
     */
    private function reorderBlocks($secID) {
        $blocks = DB::table("dashboard_blocks")->where("section_id", "=", $secID)->orderBy('order','asc')->get();
        $int = 0;
        foreach($blocks as $block) {
            DB::table('dashboard_blocks')->where('id', $block->id)->update(['order' => $int]);
            $int++;
        }
    }

    /**
     * Makes sure No Section is the first section always.
     */
    private function makeNonSectionFirst () {
        // Check if there is more than 1 non-section section.
        // If there is, we need to remove it. There should only ever be one
        // If there is no non-section, we need to add it.
        $no_sections = DB::table('dashboard_sections')->where('user_id','=',Auth::user()->id)->where('title','=','No Section');
        if($no_sections->count() > 1) {
            // this works for any number of excess `no-section` sections because deleteSection() calls makeNonSectionFirst(), which will then run the check again
            $this->deleteSection($no_sections->latest()->first()->id);
        } else if($no_sections->count() == 0)
            $this->addSection('No Section');

        $firstSection = DB::table("dashboard_sections")->where('user_id','=',Auth::user()->id)->orderBy('order','asc')->first()->order - 1;

        DB::table('dashboard_sections')
            ->where('user_id','=',Auth::user()->id)
            ->where('title','=','No Section')
            ->update(['order' => $firstSection]);

        $this->reorderSections();
    }

    /**
     * Reorders sections within the dashboard.
     */
    private function reorderSections() {
        $sections = DB::table("dashboard_sections")->where('user_id','=',Auth::user()->id)->orderBy('order','asc')->get();
        $int = 0;
        foreach($sections as $section) {
            DB::table('dashboard_sections')->where('id', $section->id)->update(['order' => $int]);
            $int++;
        }
    }

    /**
     * For a record, grab the data of the field that was assigned as a preview
     * for this record.
     *
     * @param  string $flid - Field IDs
     * @param  array $field - Field info array
     * @param  int $kid - Record Kora ID
     * @return string - Html structure of the preview field's value
     */
    private function getPreviewValues($flid,$field,$kid) {
        $record = RecordController::getRecord($kid);
        if(is_null($record))
            return '';

        if(!in_array($field['type'],Form::$validAssocFields)) {
            return "Invalid Preview Field";
        } else {
            $value = $record->{$flid};
            if(is_null($value))
                return "Preview Field Empty";
            else
                return $value;
        }
    }
}
