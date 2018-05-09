<?php namespace App\Http\Controllers;

use App\Field;
use App\Page;
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
        if(Auth::guest()) {
            return redirect('/');
        }
        //gather all sections for the dashboard and their blocks
        $sections = array();

        $results = DB::table('dashboard_sections')->where('uid','=',Auth::user()->id)->orderBy('order')->get();
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
                        $fid = $options[0];
                        $disOpts = explode(',',$options[1]);
                        $hidOpts = explode(',',$options[2]);

                        $form = FormController::getForm($fid);

                        $b['fid'] = $fid;
                        $b['name'] = $form->name;
                        $b['description'] = $form->description;
                        $b['displayedOpts'] = $disOpts;
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

                        $b['rid'] = $rid;
                        $b['projName'] = $project->name;
                        $b['formName'] = $form->name;
                        $b['fieldName'] = $firstField->name;
                        $b['fieldType'] = $firstField->type;
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

        return view('dashboard', compact('sections'));
    }

    /**
     * Deletes a dashboard section along with its blocks.
     *
     * @param  $secID - Section ID
     */
    public static function deleteSection($secID) {
        DB::table("dashboard_blocks")->where("sec_id", "=", $secID)->delete();

        DB::table("dashboard_sections")->where("id", "=", $secID)->delete();
    }

    /**
     * Deletes a dashboard block.
     *
     * @param  $blkID - Block ID
     */
    public static function deleteBlock($blkID) {
        DB::table("dashboard_blocks")->where("id", "=", $blkID)->delete();
    }
}
