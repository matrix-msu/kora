<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
Use App\Metadata;
Use App\Field;
Use App\Project;
Use App\Form;
Use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class MetadataController extends Controller {

    public function __construct()
    {
        $this->middleware('auth', ['except'=>'records']);
        $this->middleware('active', ['except'=>'records']);
    }

    /**
     * Display metadata for all records in JSON format, even to public if enabled
     *
     * @params int $pid, int $fid
     * @return JSONArray
     */

    public function records($pid,$fid){
        //if public metadata is enabled OR if the user is signed in, display JSON
        $form = Form::find($fid);
        if($form->public_metadata || Auth::check()) {
            $records = $form->records($pid, $fid)->get();
            $jsonArray = new \Illuminate\Support\Collection;
            $jsRecord = null;
            foreach ($records as $record) {
                $jsRecord = new \Illuminate\Support\Collection; //use a fresh collection for each record
                if (count($record->textfields) > 0) {
                    foreach ($record->textfields as $tf) {
                        $field = Field::find($tf->flid);
                        //Only output field if there is metadata defined, AND field content is not empty or null
                        if (count($field->metadata) > 0 && ($tf->text != "" && $tf->text !== null)) $jsRecord->put($field->metadata()->first()->name, $tf->text);
                    }
                }
                if (count($record->richtextfields) > 0) {
                    foreach ($record->richtextfields as $rtf) {
                        $field = Field::find($rtf->flid);
                        if (count($field->metadata) > 0 && ($rtf->rawtext != "" && $rtf->rawtext !== null)) $jsRecord->put($field->metadata()->first()->name, $rtf->rawtext);

                    }
                }

                $jsonArray->push($jsRecord); //add this record's collection to the collection of all records
            }
            return json_encode($jsonArray);
        }

        //else redirect to projects
        return redirect("/projects");

    }


    /**
     * Display all fields and their metadata, visibility option, and new metadata form
     *
     * @params int $pid, int $fid
     * @return Response
     */

    public function index($pid,$fid)
    {
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

}