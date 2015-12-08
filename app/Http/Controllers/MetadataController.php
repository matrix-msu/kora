<?php namespace App\Http\Controllers;

use App\Http\Requests;
Use App\Metadata;
Use App\Field;
Use App\Form;
use Illuminate\Bus\MarshalException;
Use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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

    /**
     * Display metadata for all records in JSON format, even to public if enabled
     *
     * @params int $pid, int $fid
     * @return Response
     */
    public function records($pid,$fid){
        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects/'.$pid.'/forms');
        }

        //if public metadata is enabled OR if the user is signed in, display JSON
        $form = Form::find($fid);
        if($form->public_metadata || Auth::check()) {
            $form_layout_tags = \App\Http\Controllers\FormController::xmlToArray($form->layout);
            $this->form_records = $form->records($pid, $fid)->get();
            $node_and_field_order = $this->layout($form_layout_tags);

            $metadata_and_records = new Collection();
            foreach ($this->form_records as $record) {
                $record_field_metadata = $this->matchRecordsAndMetadata($node_and_field_order, $record);
                if($record_field_metadata->count() >0) {
                    $metadata_and_records->push($record_field_metadata);
                }
            }
            return response()->json($metadata_and_records);
        }
        else{
            return redirect("/");
        }
    }

    /**
     * Match the parts of a record with the metadata for their respective field
     *
     * @params String $items, Record $record
     * @return Collection
     */
    public function matchRecordsAndMetadata($items,$record){
        $jsRecord = new Collection(); //Metadata and field contents for a record
        foreach($items as $item){
            if(is_string($item)){
                foreach($record->textfields as $tf){
                    $field = Field::find($tf->flid);
                    if($item==$tf->flid && count($field->metadata)>0 && ($tf->text != "" && $tf->text !== null)) $jsRecord->put($field->metadata()->first()->name,$tf->text);
                }
                foreach($record->numberfields as $nf){
                    $field = Field::find($nf->flid);
                    if($item==$nf->flid && count($field->metadata)>0 && ($nf->number != "" && $nf->number !== null)) $jsRecord->put($field->metadata()->first()->name,$nf->number);
                }
                foreach($record->richtextfields as $rtf){
                    $field = Field::find($rtf->flid);
                    if($item==$rtf->flid && count($field->metadata)>0 && ($rtf->rawtext != "" && $rtf->rawtext !== null)) $jsRecord->put($field->metadata()->first()->name,$rtf->rawtext);
                }
                foreach($record->listfields as $lf){
                    $field = Field::find($lf->flid);
                    if($item==$lf->flid && count($field->metadata)>0 && ($lf->option != "" && $lf->option !== null)) $jsRecord->put($field->metadata()->first()->name,$lf->option);
                }
                foreach($record->multiselectlistfields as $mslf){
                    $field = Field::find($mslf->flid);
                    if($item==$mslf->flid && count($field->metadata)>0 && ($mslf->options != "" && $mslf->options !== null)){
                        $options_array = explode("[!]",$mslf->options);
                        $jsRecord->put($field->metadata()->first()->name,$options_array);
                    }
                }
                foreach($record->generatedlistfields as $glf){
                    $field = Field::find($glf->flid);
                    if($item==$glf->flid && count($field->metadata)>0 && ($glf->options != "" && $glf->options !== null)){
                        $options_array = explode("[!]",$glf->options);
                        $jsRecord->put($field->metadata()->first()->name,$options_array);
                    }
                }
                foreach($record->datefields as $df) {
                    $field = Field::find($df->flid);
                    if ($item == $df->flid && count($field->metadata) > 0 && $df->month != "0") {
                        //DateField has options that change how it will be displayed
                        $options_array = preg_split("/(\[|\])/", $field->options, 0, PREG_SPLIT_NO_EMPTY); //regex split on [ or ] and don't include empty
                        $option_tag_count = 0;
                        $option_values = new Collection(); // [circa,start,end,format,era] refer to database table
                        foreach ($options_array as $option) {
                            if ($option[0] == "!" && $option_tag_count == 0) {
                                $option_tag_count++;
                                continue;
                            } elseif ($option[0] == "!" && $option_tag_count > 0) {
                                $option_tag_count = 0;
                                continue;
                            } else {
                                $option_values->push($option);
                            }
                        }
                        $date_string = "";
                        //Check if Circa display is enabled
                        if ($option_values[0] == "Yes") {
                            $date_string = $date_string . "Circa ";
                        }
                        //Check format of date
                        if ($option_values[3] == "MMDDYYYY") {
                            $date_string = $date_string . $df->month . "-" . $df->day . ", " . $df->year;
                        } elseif ($option_values[3] == "DDMMYYYY") {
                            $date_string = $date_string . $df->day . "-" .$df->month . "-" . $df->year;
                        } elseif ($option_values[3] == "YYYYMMDD") {
                            $date_string = $date_string . $df->year . "-" . $df->month . "-" . $df->day;
                        }
                        //Check if Era display is enabled
                        if ($option_values[4] == "Yes") {
                            $date_string = $date_string." ".$df->era;
                        }
                        $jsRecord->put($field->metadata()->first()->name,$date_string);
                    }
                }
                foreach($record->schedulefields as $sf){
                    $field = Field::find($sf->flid);
                    if($item==$sf->flid && count($field->metadata)>0){
                        $events_array = explode("[!]",$sf->events);
                        $jsRecord->put($field->metadata()->first()->name,$events_array);
                    }
                }

                foreach($record->geolocatorfields as $gf){
                    $field = Field::find($gf->flid);
                    if($item==$gf->flid && count($field->metadata)>0){
                        $locations_array = explode("[!]",$gf->locations);
                        $locations_and_description_array = new Collection();
                        foreach($locations_array as $location){
                            $locations_and_description_array->push(explode(":",$location));
                        }
                        $jsRecord->put($field->metadata()->first()->name,$locations_and_description_array);
                    }
                }

                foreach($record->documentsfields as $df){
                    $field = Field::find($df->flid);
                    if($item==$df->flid && count($field->metadata)>0){
                        $files_array = explode("[!]",$df->documents);
                        $files_and_info_array = new Collection();
                        foreach($files_array as $files){
                            $individual_file_array = new Collection();
                            $individual_file_array->put("Name",explode("[Name]",$files)[1]);
                            $individual_file_array->put("Size",explode("[Size]",$files)[1]);
                            $individual_file_array->put("Type",explode("[Type]",$files)[1]);
                            $files_and_info_array->push($individual_file_array);
                        }
                        $jsRecord->put($field->metadata()->first()->name,$files_and_info_array);
                    }
                }

                foreach($record->galleryfields as $gf){
                    $field = Field::find($gf->flid);
                    if($item==$gf->flid && count($field->metadata)>0){
                        $files_array = explode("[!]",$gf->images);
                        $files_and_info_array = new Collection();
                        foreach($files_array as $files){
                            $individual_file_array = new Collection();
                            $individual_file_array->put("Name",explode("[Name]",$files)[1]);
                            $individual_file_array->put("Size",explode("[Size]",$files)[1]);
                            $individual_file_array->put("Type",explode("[Type]",$files)[1]);
                            $files_and_info_array->push($individual_file_array);
                        }
                        $jsRecord->put($field->metadata()->first()->name,$files_and_info_array);
                    }
                }

                foreach($record->videofields as $vf){
                    $field = Field::find($vf->flid);
                    if($item==$vf->flid && count($field->metadata)>0){
                        $files_array = explode("[!]",$vf->video);
                        $files_and_info_array = new Collection();
                        foreach($files_array as $files){
                            $individual_file_array = new Collection();
                            $individual_file_array->put("Name",explode("[Name]",$files)[1]);
                            $individual_file_array->put("Size",explode("[Size]",$files)[1]);
                            $individual_file_array->put("Type",explode("[Type]",$files)[1]);
                            $files_and_info_array->push($individual_file_array);
                        }
                        $jsRecord->put($field->metadata()->first()->name,$files_and_info_array);
                    }
                }

                foreach($record->modelfields as $mf){
                    $field = Field::find($mf->flid);
                    if($item==$mf->flid && count($field->metadata)>0){
                        $files_array = explode("[!]",$mf->model);
                        $files_and_info_array = new Collection();
                        foreach($files_array as $files){
                            $individual_file_array = new Collection();
                            $individual_file_array->put("Name",explode("[Name]",$files)[1]);
                            $individual_file_array->put("Size",explode("[Size]",$files)[1]);
                            $individual_file_array->put("Type",explode("[Type]",$files)[1]);
                            $files_and_info_array->push($individual_file_array);
                        }
                        $jsRecord->put($field->metadata()->first()->name,$files_and_info_array);
                    }
                }

                foreach($record->playlistfields as $pf){
                    $field = Field::find($pf->flid);
                    if($item==$pf->flid && count($field->metadata)>0){
                        $files_array = explode("[!]",$pf->audio);
                        $files_and_info_array = new Collection();
                        foreach($files_array as $files){
                            $individual_file_array = new Collection();
                            $individual_file_array->put("Name",explode("[Name]",$files)[1]);
                            $individual_file_array->put("Size",explode("[Size]",$files)[1]);
                            $individual_file_array->put("Type",explode("[Type]",$files)[1]);
                            $files_and_info_array->push($individual_file_array);
                        }
                        $jsRecord->put($field->metadata()->first()->name,$files_and_info_array);
                    }
                }

            }
            else{
                $node_fields = $this->matchRecordsAndMetadata($item,$record);
                if($node_fields->count() >0) //Exclude if there were no fields with data in that node
                {
                    $jsRecord->put($items->search($item),$node_fields);
                }
            }
        }
        return $jsRecord;
    }
    /*
     * Takes form layout and modifies it from a flat array to an array of arrays
     * so metadata can be displayed with the correct layout.  This method calls
     * itself recursively until all nodes are completed
     *
     * @params Array $tags
     * @return Collection
     */
    public function layout($tags){

        $count_node_open_tags = 0; //How many <node> tags have been encountered
        $node_contents = new Collection(); //The current node and it's sub-nodes
        $subnode_contents = new Collection(); //A complete sub-node
        $subnode_name = "";

        foreach($tags as $tag){
            $tag_kind = $tag["tag"];
            $tag_type = $tag["type"];
            //If it's a top-level field, not inside anything, then we can immediately add it to our collection
            if($tag_kind == "ID" && $count_node_open_tags == 0){
                $node_contents->push($tag["value"]);
                continue;
            }
            //If we hit a <node> open, then put this aside for further processing
            elseif($tag_kind == "ID" && $count_node_open_tags >0){
                $subnode_contents->push($tag);
                continue;
            }

            elseif($tag_kind == "NODE"){
                //If this is the first <node> we have hit so far, keep the Title, increment count, but don't save it
                if($tag_type == "open" && $count_node_open_tags ==0){
                    $count_node_open_tags++;
                    $subnode_name = $tag["attributes"]["TITLE"];
                    continue;
                }
                //If this is the second <node> then just put it aside in $subnode_contents for later
                elseif($tag_type == "open" && $count_node_open_tags>0){
                    $count_node_open_tags++;
                    $subnode_contents->push($tag);
                }
                elseif($tag_type == "close"){
                    $count_node_open_tags--;
                    //If all <node> tags have been closed, then recursively call this function on the $subnode_contents
                    if($count_node_open_tags == 0){
                        $node_contents->put($subnode_name,$this->layout($subnode_contents));
                        $subnode_contents = new Collection();
                        $subnode_name = "";
                    }
                    //If there are still some <node> open tags unaccounted for, then just put it aside and keep going
                    elseif($count_node_open_tags >0){
                        $subnode_contents->push($tag);
                    }
                }
            }

        }
        return $node_contents; //Return this node, including the contents of any sub-nodes it may have had

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
        $available_fields = new \Illuminate\Support\Collection;
        foreach ($all_fields as $field)
        {
            if($field->metadata()->first() !== null) continue;
            else $available_fields->push($field);
        }

        $fields = $available_fields->lists('name','flid');
        $form = Form::find($fid);
        return view('metadata.index',compact('pid','fid','form','fields'));
    }

    /**
    /**
     * Process the form submission and add metadata to field or change visibility
     *
     * @param Request $request, int $pid, int $fid
     * @return Response
     */
    public function store(Request $request,$pid,$fid)
    {
        //Changing metadata visibility or adding metadata to a field?
        $this->validate($request,[
            'type' => 'required',
        ]);

        //Make the metadata public or private
        if($request->input('type')=='visibility'){
            $form = Form::find($fid);
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
                flash()->overlay('That name is already used in this form","Whoops.');
                return redirect()->back();
            }

            $field = Field::where('pid',$pid)->where('fid',$fid)->where('flid','=',$request->input('field'))->first();
            $metadata = new Metadata(['pid'=>$pid,'fid'=>$fid, 'name'=>$request->input('name')]);
            $metadata->field()->associate($field);
            $field->metadata()->save($metadata);

            return redirect()->action('MetadataController@index',compact('pid','fid')); //Laravel form submission needs this
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
        flash()->overlay('The field\'s metadata was deleted', 'Success!');
        return response()->json('deleted');

    }

    public function isUniqueToForm($fid,$name){
        $fields = Form::find($fid)->fields()->get();
        $metas = new Collection();
        foreach($fields as $field) {
            if (!is_null($field->metadata()->first())) {
                $metas->put($field->metadata()->first()->name,true);
            }
        }
        if($metas->has($name)){
            return false;
        }
        else{
            return true;
        }
    }


    public function massAssign($pid,$fid,Request $request){
        $fields = Form::find($fid)->fields()->get();
        $metas = new Collection();
        foreach($fields as $field){
            if(is_null($field->metadata()->first())){
                //$metas->push($field->metadata()->first());
                if($this->isUniqueToForm($fid,$field->name)){
                    $metadata = new Metadata(['pid'=>$pid,'fid'=>$fid, 'name'=>$field->name]);
                }
                else{
                    if($this->isUniqueToForm($fid,$field->name."_".$field->slug)){
                        $metadata = new Metadata(['pid'=>$pid,'fid'=>$fid, 'name'=>$field->name."_".$field->slug]);
                    }
                    else{
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