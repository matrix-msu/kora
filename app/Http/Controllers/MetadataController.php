<?php namespace App\Http\Controllers;

use App\ComboListField;
use App\GeolocatorField;
use App\Http\Requests;
use App\Jobs\TestJob;
Use App\Metadata;
Use App\Field;
Use App\Form;
use App\Record;
use App\ScheduleField;
use App\Search;
use App\TextField;
use Illuminate\Bus\MarshalException;
use Illuminate\Support\Facades\Artisan;
Use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class MetadataController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Metadata Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles assigning/removing metadata for a field, as well as
    | displaying all records with their field's metadata, and changing the visibility
    | of metadata to the public, and mass assigning metadata
    |
    */
    public function __construct()
    {
        $this->middleware('auth', ['except'=>'records']);
        $this->middleware('active', ['except'=>'records']);
    }

    /*
     * Attempting meta data function again.
     */
    public function records2($pid, $fid) {
        // Old meta data method
        if(!FormController::validProjForm($pid, $fid)){
            return redirect('projects/'.$pid.'/forms');
        }

        $rids = DB::table("records")->where("fid", "=", $fid)->select("rid")->get();

        // The DB call returns an array of StdObj so we get the rids out of the objects.
        $rids = array_map( function($obj) {
            return $obj->rid;
        }, $rids);

        $output_file = ExportController::exportWithRids($rids,'META');

        if (file_exists($output_file)) {
            header("Content-Disposition: attachment; filename=\"" . basename($output_file) . "\"");
            header("Content-Type: application/rdf+xml");
            header("Content-Length: " . filesize($output_file));

            readfile($output_file);
        }
    }

    /*
     * Attempting meta data function again.
     */
    public function singleRecord($pid, $fid, $resource) {
        // Old meta data method
        if(!FormController::validProjForm($pid, $fid)){
            return redirect('projects/'.$pid.'/forms');
        }

        //Get the record id of the text field whose primary index is equal to the resource
        $rids = DB::table("text_fields")->where("fid", "=", $fid)->where("text","=",$resource)->select("rid")->get();

        // The DB call returns an array of StdObj so we get the rids out of the objects.
        $rids = array_map( function($obj) {
            return $obj->rid;
        }, $rids);

        $output_file = ExportController::exportWithRids($rids,'META');

        if (file_exists($output_file)) {
            header("Content-Disposition: attachment; filename=\"" . basename($output_file) . "\"");
            header("Content-Type: application/rdf+xml");
            header("Content-Length: " . filesize($output_file));

            readfile($output_file);
        }
    }

    /**
     * Search through a metadata result.
     *
     * @param $pid int, project id.
     * @param $fid int, form id.
     * @param $query string, comma separated query string.
     */
    public function search($pid, $fid, $query) {
        if(!FormController::validProjForm($pid, $fid)){
            return redirect('projects/'.$pid.'/forms');
        }

        $query = explode(",", $query);
        $query_str = implode(" ", $query);

        $query = array_diff($query, Search::showIgnoredArguments($query_str));
        $query = implode(" ", $query);

        $search = new Search($pid, $fid, $query, Search::SEARCH_OR);
        $rids = $search->formKeywordSearch();

        dd($rids);
    }

    /**
     * Display all fields and their metadata, visibility option, and new metadata form
     *
     * @params int $pid, int $fid
     * @return Response
     */
    public function index($pid,$fid)
    {
        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects/'.$pid.'/forms');
        }
        //Fields that already have metadata do not get sent to the view to be listed
        $all_fields = Field::where('pid',$pid)->where('fid',$fid)->get();
        $available_fields = new \Illuminate\Support\Collection; //fields without a tag
        $assigned_fields = array(); //fields with a tag
        foreach ($all_fields as $field)
        {
            if($field->metadata()->first() !== null){
                array_push($assigned_fields,$field);
            }
            else {
                $available_fields->push($field);
            }
        }

        $fields = $available_fields->lists('name','flid');
        $form = Form::find($fid);

        $resource_title = $form->lod_resource;

        return view('metadata.index',compact('pid','fid','form','fields','assigned_fields','resource_title'));
    }

    /**
     * Process the form submission and add metadata to field or change visibility
     *
     * @param Request $request
     * @param int $pid, project id.
     * @param int $fid, form id.
     * @return Response
     */
    public function store(Request $request, $pid, $fid)
    {
        //Changing metadata visibility or adding metadata to a field?
        $this->validate($request,[
            'type' => 'required',
        ]);

        //Make the metadata public or private
        if($request->input('type')=='visibility'){
            $form = Form::find($fid);

            //Couple checks to make sure things are set up right
            $resourceTitle = $form->lod_resource;
            if(is_null($resourceTitle) || $resourceTitle==''){
                return response("You must give your resource a title.",200);
            }

            $primeIndex = Metadata::where('fid','=',$fid)->where('primary','=',1)->get()->count();
            if($primeIndex!=1){
                return response("You must select a primary index for this form metadata.",200);
            }

            if($request->input('state') == 'true') $form->public_metadata = true;
            else $form->public_metadata = false;
            $form->save();
            return response("success",200); //The request comes from JQuery, no need to redirect
        }
        //Add metadata to a field
        elseif($request->input('type')=='addmetadata'){
            $this->validate($request,[
                'name' => 'required',
                'field' => 'required|unique:metadatas,flid', //field can only have 1 metadata
            ]);

            if(!$this->isUniqueToForm($fid,$request->input('name'))){
                flash()->overlay(trans('controller_metadata.name'),trans('controller_metadata.whoops'));
                return redirect()->back();
            }

            $field = Field::where('pid',$pid)->where('fid',$fid)->where('flid','=',$request->input('field'))->first();
            $metadata = new Metadata(['pid'=>$pid,'fid'=>$fid, 'name'=>$request->input('name')]);
            $metadata->primary = 0;
            $metadata->field()->associate($field);
            $field->metadata()->save($metadata);

            return redirect()->action('MetadataController@index',compact('pid','fid')); //Laravel form submission needs this
        }
    }

    public function updateResource($pid,$fid,Request $request){
        $form = FormController::getForm($fid);
        $title = $request->title;

        $form->lod_resource = $title;
        $form->save();

        flash()->overlay('Resource Title updated', trans('controller_metadata.success'));
        return redirect()->action('MetadataController@index',compact('pid','fid')); //Laravel form submission needs this
    }

    public function makePrimary($pid, $fid, Request $request){
        $metadatas = Metadata::where('fid','=',$fid)->get();
        $pFlid = $request->flid;

        foreach($metadatas as $meta){
            if($meta->flid==$pFlid){
                $meta->primary = 1;
            }else{
                $meta->primary = 0;
            }

            $meta->save();
        }
    }

    /**
     * Remove metadata from a field
     *
     * @param  int  $pid, int $fid, Request $request
     * @return Response
     */
    public function destroy($pid,$fid,Request $request)
    {
        $meta = Field::find($request->input('flid'))->metadata()->first();
        if($meta !== null) $meta->delete();
        flash()->overlay(trans('controller_metadata.delete'), trans('controller_metadata.success'));
        return response()->json('deleted');

    }

    /**
     * Determines if a particular name is unique in the metadata table.
     * This is constrained to a particular form.
     *
     * @param $fid int, form id.
     * @param $name string, the name to be tested.
     * @return bool, true if the name is unique.
     */
    public function isUniqueToForm($fid, $name) {
        $count = Metadata::where("fid", "=", $fid)
            ->where("name", "=", $name)
            ->count();

        return $count == 0;
    }

    /**
     * Attaches a linked open data association to all fields in a form.
     * Ensures that the name will be unique to the form.
     *
     * @param $pid int, project id.
     * @param $fid int, form id.
     */
    public function massAssign($pid, $fid) {
        $fields = Field::where("fid", "=", $fid)->get();
        foreach($fields as $field){
                if(is_null($field->metadata()->first())) { // Only associate a new metadata if it does not exist already.
                    if ($this->isUniqueToForm($fid,$field->name)) {
                        $metadata = new Metadata(['pid'=>$pid,'fid'=>$fid, 'name'=>$field->name]);
                    }
                    else {
                        if($this->isUniqueToForm($fid,$field->name."_".$field->slug)) {
                            $metadata = new Metadata(['pid'=>$pid,'fid'=>$fid, 'name'=>$field->name."_".$field->slug]);
                        }

                        else {
                            $count = 0;
                            $name = $field->name."_".$field->slug."0";
                            while(!$this->isUniqueToForm($fid,$name)){
                                $name = $field->name."_".$field->slug.$count;
                                $count++;
                            }
                            $metadata = new Metadata(['pid'=>$pid,'fid'=>$fid, 'name'=>$name]);
                        }
                    }

                $metadata->field()->associate($field);
                $field->metadata()->save($metadata);
            }
        }
    }
}