<?php namespace App\Http\Controllers;

use App\DateField;
use App\Field;
use App\Form;
use App\FormGroup;
use App\GeneratedListField;
use App\GeolocatorField;
use App\ListField;
use App\Metadata;
use App\MultiSelectListField;
use App\NumberField;
use App\Project;
use App\ProjectGroup;
use App\Record;
use App\Revision;
use App\RichTextField;
use App\ScheduleField;
use App\TextField;
use App\Token;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use \Illuminate\Support\Facades\App;
Use \Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Psy\Exception\ErrorException;
use Illuminate\Support\Facades\Auth;

class BackupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
        $this->middleware('admin');
        if(Auth::check()){
            if(Auth::user()->id != 1){
                flash()->overlay("Only the default admin can view that page","Whoops.");
                return redirect("/")->send();
            }
        }
    }
	/*
	|--------------------------------------------------------------------------
	| Backup Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles backup and restore functions.
	|
	*/

	private $BACKUP_DIRECTORY = "backups"; //Set the backup directory relative to laravel/storaage/app

    public function index(Request $request){
        $available_backups = Storage::files($this->BACKUP_DIRECTORY);
        $saved_backups = new Collection();


        foreach($available_backups as $backup){
            $backup_info = new Collection();
            $backup_file = Storage::get($backup);
            $parsed_data = json_decode($backup_file);
            $backup_info->put("date",$parsed_data->kora3->date);
            $backup_info->put("name",$parsed_data->kora3->name);
            $backup_info->put("user",$parsed_data->kora3->created_by);
            $saved_backups->push($backup_info);
        }
        $saved_backups->sortByDesc(function($item){
            //dd($item);
            return $item->get("date");
        });

        $request->session()->put("restore_points_available",$available_backups);
        return view('backups.index',compact('saved_backups'));
    }

    public function startBackup(Request $request){
        $this->validate($request,[
            'backup_label'=>'required|alpha_dash',
        ]);
        $backup_label = $request->input("backup_label");
        $request->session()->put("backup_new_label",$backup_label);
        return view('backups.backup',compact('backup_label'));
    }

	public function create(Request $request){
        Artisan::call("down");
        $data = new Collection();
        sleep(15);
        if($request->session()->has("backup_new_label")){
            $backup_label = $request->session()->get("backup_new_label");
            $request->session()->forget("backup_new_label");
        }
        else{
            $backup_label = "";
        }
		$filename = Carbon::now()->format("Y-m-d_H:i:s"). ".kora3_backup";

		$backup = $this->saveDatabase($backup_label);

        $filepath = $this->BACKUP_DIRECTORY."/".$filename;

		if(Storage::exists($filepath)){
			//dd("File already exists");
            $data->put("status",false);
            $data->put("message","A file with that name already exists");
		}
		else{
			Storage::put($filepath,$backup);
            $data->put("status",true);
            $data->put("filename",$filename);
            $request->session()->put("backup_file_name",$filename);
		}
        Artisan::call("up");
        return json_encode($data);
        //abort(500);
	}

    public function download(Request $request){

        if($request->session()->has("backup_file_name")){
            $filename = $request->session()->get("backup_file_name");
            $request->session()->forget("backup_file_name");
            return response()->download((realpath("../storage/app/".$this->BACKUP_DIRECTORY."/".$filename)),$filename,array("Content-Type"=>"application/octet-stream"));
        }
        else{
            return response("File Not Found",404);
        }
    }

	public function saveDatabase($backup_name){
		$entire_database = new Collection(); //This will hold literally the entire database and then some

        //Some info about the backup itself
        $backup_info = new Collection();
        $backup_info->put("version","1");   //In case a breaking change happens in the future (like new table added or a table removed)
        $backup_info->put("date",Carbon::now()->toDateTimeString()); //UTC time the backup started 1975-12-25T14:15:16-05:00
        $backup_info->put("name",$backup_name); //A user-assigned name for the backup
        $backup_info->put("created_by","someone@somewhere.com"); //The email for the user that created it

        $entire_database->put("kora3",$backup_info);

		//Models that have data in the database should be put into the $entire_database collection
		//You need to loop through all of your table's columns and add them first to this function, then to restore
		//Don't forget to include important information about relationships like pivot tables (See FormGroups for example)
        //Project
        $all_projects_data = new Collection();
        $entire_database->put("projects",$all_projects_data);
        foreach(Project::all() as $project){
            $individual_project_data = new Collection();
            $individual_project_data->put("pid",$project->pid);
            $individual_project_data->put("name",$project->name);
            $individual_project_data->put("slug",$project->slug);
            $individual_project_data->put("description",$project->description);
            $individual_project_data->put("adminGID",$project->adminGID);
            $individual_project_data->put("active",$project->active);
            $individual_project_data->put("created_at",$project->created_at->getTimestamp());
            $individual_project_data->put("updated_at",$project->updated_at->getTimestamp());
            $all_projects_data->push($individual_project_data);
        }

        // Form
        $all_forms_data = new Collection();
        $entire_database->put("forms",$all_forms_data);
        foreach(Form::all() as $form){
            $individual_form_data = new Collection();
            $individual_form_data->put("fid",$form->fid);
            $individual_form_data->put("pid",$form->pid);
            $individual_form_data->put("adminGID",$form->adminGID);
            $individual_form_data->put("name",$form->name);
            $individual_form_data->put("slug",$form->slug);
            $individual_form_data->put("description",$form->description);
            $individual_form_data->put("layout",$form->layout);
            $individual_form_data->put("public_metadata",$form->public_metadata);
            $individual_form_data->put("created_at",$form->created_at->getTimestamp());
            $individual_form_data->put("updated_at",$form->updated_at->getTimestamp());
            $all_forms_data->push($individual_form_data);
        }

        // FormGroup
		$all_formgroup_data = new Collection();
		$entire_database->put("formgroups", $all_formgroup_data);
		foreach(FormGroup::all() as $formgroup){
			$individual_formgroup_data = new Collection();
			$group_data = new Collection();
            $group_data->put("id",$formgroup->id);
            $group_data->put("name",$formgroup->name);
            $group_data->put("fid",$formgroup->fid);
            $group_data->put("create",$formgroup->create);
            $group_data->put("edit",$formgroup->edit);
            $group_data->put("delete",$formgroup->delete);
            $group_data->put("ingest",$formgroup->ingest);
            $group_data->put("modify",$formgroup->modify);
            $group_data->put("destroy",$formgroup->destroy);
            $group_data->put("created_at",$formgroup->created_at->getTimestamp());
            $group_data->put("updated_at",$formgroup->updated_at->getTimestamp());
			$individual_formgroup_data->put("group_data",$group_data);
			$individual_formgroup_data->put("user_data",$formgroup->users()->get()->modelKeys());
			$all_formgroup_data->push($individual_formgroup_data);
		}

        // ProjectGroup
		$all_projectgroup_data = new Collection();
		$entire_database->put("projectgroups", $all_projectgroup_data);
		foreach(ProjectGroup::all() as $projectgroup){
			$individual_projectgroup_data = new Collection();
            $group_data = new Collection();
            $group_data->put("id",$projectgroup->id);
            $group_data->put("name",$projectgroup->name);
            $group_data->put("pid",$projectgroup->pid);
            $group_data->put("create",$projectgroup->create);
            $group_data->put("edit",$projectgroup->edit);
            $group_data->put("delete",$projectgroup->delete);
            $group_data->put("created_at",$projectgroup->created_at->getTimestamp());
            $group_data->put("updated_at",$projectgroup->updated_at->getTimestamp());
			$individual_projectgroup_data->put("group_data",$group_data);
			$individual_projectgroup_data->put("user_data",$projectgroup->users()->get()->modelKeys());
			$all_projectgroup_data->push($individual_projectgroup_data);
		}

        // User
		$all_users_data = new Collection();
		$entire_database->put("users", $all_users_data);
		foreach (User::all() as $user) {
			$individual_user_data = new Collection();
			if ($user->id == 1) continue; //skip the first admin account (the user who will be restoring)
			$individual_user_data->put("id", $user->id);
			$individual_user_data->put("admin", $user->admin);
			$individual_user_data->put("active", $user->active);
			$individual_user_data->put("username", $user->username);
			$individual_user_data->put("name", $user->name);
			$individual_user_data->put("email", $user->email);
			$individual_user_data->put("password", $user->password);
			$individual_user_data->put("organization", $user->organization);
			$individual_user_data->put("language", $user->language);
			$individual_user_data->put("regtoken", $user->regtoken);
			$individual_user_data->put("remember_token", $user->remember_token);
			$individual_user_data->put("created_at", $user->created_at->getTimestamp());
			$individual_user_data->put("updated_at", $user->updated_at->getTimestamp());
			$all_users_data->push($individual_user_data);
		}

        // Field
        $all_fields_data = new Collection();
        $entire_database->put("fields",$all_fields_data);
        foreach(Field::all() as $field){
            $individual_field_data = new Collection();
            $individual_field_data->put("flid",$field->flid);
            $individual_field_data->put("pid",$field->pid);
            $individual_field_data->put("fid",$field->fid);
            $individual_field_data->put("order",$field->order);
            $individual_field_data->put("type",$field->type);
            $individual_field_data->put("name",$field->name);
            $individual_field_data->put("slug",$field->slug);
            $individual_field_data->put("desc",$field->desc);
            $individual_field_data->put("required",$field->required);
            $individual_field_data->put("default",$field->default);
            $individual_field_data->put("options",$field->options);
            $individual_field_data->put("created_at",$field->created_at->getTimestamp());
            $individual_field_data->put("updated_at",$field->updated_at->getTimestamp());
            $all_fields_data->push($individual_field_data);
        }

        // Record
        $all_records_data = new Collection();
        $entire_database->put("records",$all_records_data);
        foreach(Record::all() as $record){
            $individual_record_data = new Collection();
            $individual_record_data->put("rid",$record->rid);
            $individual_record_data->put("kid",$record->kid);
            $individual_record_data->put("pid",$record->pid);
            $individual_record_data->put("fid",$record->fid);
            $individual_record_data->put("owner",$record->owner);
            $individual_record_data->put("created_at",$record->created_at->getTimestamp());
            $individual_record_data->put("updated_at",$record->updated_at->getTimestamp());
            $all_records_data->push($individual_record_data);
        }

        // TextField
        $all_textfields_data = new Collection();
        $entire_database->put("textfields",$all_textfields_data);
        foreach(TextField::all() as $textfield){
            $individual_textfield_data = new Collection();
            $individual_textfield_data->put("id",$textfield->id);
            $individual_textfield_data->put("rid",$textfield->rid);
            $individual_textfield_data->put("flid",$textfield->flid);
            $individual_textfield_data->put("text",$textfield->text);
            $individual_textfield_data->put("created_at",$textfield->created_at->getTimestamp());
            $individual_textfield_data->put("updated_at",$textfield->updated_at->getTimestamp());
            $all_textfields_data->push($individual_textfield_data);
        }

        //  RichTextField
        $all_richtextfields_data = new Collection();
        $entire_database->put("richtextfields",$all_richtextfields_data);
        foreach(RichTextField::all() as $richtextfield){
            $individual_richtextfield_data = new Collection();
            $individual_richtextfield_data->put("id",$richtextfield->id);
            $individual_richtextfield_data->put("rid",$richtextfield->rid);
            $individual_richtextfield_data->put("flid",$richtextfield->flid);
            $individual_richtextfield_data->put("rawtext",$richtextfield->rawtext);
            $individual_richtextfield_data->put("created_at",$richtextfield->created_at->getTimestamp());
            $individual_richtextfield_data->put("updated_at",$richtextfield->updated_at->getTimestamp());
            $all_richtextfields_data->push($individual_richtextfield_data);
        }

        //  NumberField
        $all_numberfields_data = new Collection();
        $entire_database->put("numberfields",$all_numberfields_data);
        foreach(NumberField::all() as $numberfield){
            $individual_numberfield_data = new Collection();
            $individual_numberfield_data->put("id",$numberfield->id);
            $individual_numberfield_data->put("rid",$numberfield->rid);
            $individual_numberfield_data->put("flid",$numberfield->flid);
            $individual_numberfield_data->put("number",$numberfield->number);
            $individual_numberfield_data->put("created_at",$numberfield->created_at->getTimestamp());
            $individual_numberfield_data->put("updated_at",$numberfield->updated_at->getTimestamp());
            $all_numberfields_data->push($individual_numberfield_data);
        }

        // ListField
        $all_listfields_data = new Collection();
        $entire_database->put("listfields",$all_listfields_data);
        foreach(ListField::all() as $listfield){
            $individual_listfield_data = new Collection();
            $individual_listfield_data->put("id",$listfield->id);
            $individual_listfield_data->put("rid",$listfield->rid);
            $individual_listfield_data->put("flid",$listfield->flid);
            $individual_listfield_data->put("option",$listfield->option);
            $individual_listfield_data->put("created_at",$listfield->created_at->getTimestamp());
            $individual_listfield_data->put("updated_at",$listfield->updated_at->getTimestamp());
            $all_listfields_data->push($individual_listfield_data);
        }

        // GeneratedListField
        $all_generatedlistfields_data = new Collection();
        $entire_database->put("generatedlistfields",$all_generatedlistfields_data);
        foreach(GeneratedListField::all() as $generatedlistfield){
            $individual_generatedlistfield_data = new Collection();
            $individual_generatedlistfield_data->put("id",$generatedlistfield->id);
            $individual_generatedlistfield_data->put("rid",$generatedlistfield->rid);
            $individual_generatedlistfield_data->put("flid",$generatedlistfield->flid);
            $individual_generatedlistfield_data->put("options",$generatedlistfield->options);
            $individual_generatedlistfield_data->put("created_at",$generatedlistfield->created_at->getTimestamp());
            $individual_generatedlistfield_data->put("updated_at",$generatedlistfield->updated_at->getTimestamp());
            $all_generatedlistfields_data->push($individual_generatedlistfield_data);
        }

        // MultiSelectListField
        $all_multiselectlistfields_data = new Collection();
        $entire_database->put("multiselectlistfields",$all_multiselectlistfields_data);
        foreach(MultiSelectListField::all() as $multiselectlistfield){
            $individual_multiselectlistfield_data = new Collection();
            $individual_multiselectlistfield_data->put("id",$multiselectlistfield->id);
            $individual_multiselectlistfield_data->put("rid",$multiselectlistfield->rid);
            $individual_multiselectlistfield_data->put("flid",$multiselectlistfield->flid);
            $individual_multiselectlistfield_data->put("options",$multiselectlistfield->options);
            $individual_multiselectlistfield_data->put("created_at",$multiselectlistfield->created_at->getTimestamp());
            $individual_multiselectlistfield_data->put("updated_at",$multiselectlistfield->updated_at->getTimestamp());
            $all_multiselectlistfields_data->push($individual_multiselectlistfield_data);
        }

        // DateField
        $all_datefields_data = new Collection();
        $entire_database->put("datefields",$all_datefields_data);
        foreach(DateField::all() as $datefield){
            $individual_datefield_data = new Collection();
            $individual_datefield_data->put("id",$datefield->id);
            $individual_datefield_data->put("rid",$datefield->rid);
            $individual_datefield_data->put("flid",$datefield->flid);
            $individual_datefield_data->put("circa",$datefield->circa);
            $individual_datefield_data->put("month",$datefield->month);
            $individual_datefield_data->put("day",$datefield->year);
            $individual_datefield_data->put("year",$datefield->year);
            $individual_datefield_data->put("era",$datefield->era);
            $individual_datefield_data->put("created_at",$datefield->created_at->getTimestamp());
            $individual_datefield_data->put("updated_at",$datefield->updated_at->getTimestamp());
            $all_datefields_data->push($individual_datefield_data);
        }

        // ScheduleField
        $all_schedulefields_data = new Collection();
        $entire_database->put("schedulefields",$all_schedulefields_data);
        foreach(ScheduleField::all() as $schedulefield){
            $individual_schedulefield_data = new Collection();
            $individual_schedulefield_data->put("id",$schedulefield->id);
            $individual_schedulefield_data->put("rid",$schedulefield->rid);
            $individual_schedulefield_data->put("flid",$schedulefield->flid);
            $individual_schedulefield_data->put("events",$schedulefield->events);
            $individual_schedulefield_data->put("created_at",$schedulefield->created_at->getTimestamp());
            $individual_schedulefield_data->put("updated_at",$schedulefield->updated_at->getTimestamp());
            $all_schedulefields_data->push($individual_schedulefield_data);
        }

        /*
        // GeolocatorField  <-- There is NO restore function yet for this
        $all_geolocatorfields_data = new Collection();
        $entire_database->put("geolocatorfields",$all_geolocatorfields_data);
        foreach(GeolocatorField::all() as $geolocatorfield){
            $individual_geolocatorfield_data = new Collection();
            $individual_geolocatorfield_data->put("id",$geolocatorfield->id);
            $individual_geolocatorfield_data->put("created_at",$geolocatorfield->created_at->getTimestamp);
            $individual_geolocatorfield_data->put("updated_at",$geolocatorfield->updated_at->getTimestamp);
            $all_geolocatorfields_data->push($individual_geolocatorfield_data);
        }
         */
        //  Token
        $all_tokens_data = new Collection();
        $entire_database->put("tokens",$all_tokens_data);
        foreach(Token::all() as $token){
            $individual_token_data = new Collection();
            $individual_token_data->put("id",$token->id);
            $individual_token_data->put("type",$token->type);
            $individual_token_data->put("token",$token->token);
            $individual_token_data->put("created_at",$token->created_at->getTimestamp());
            $individual_token_data->put("updated_at",$token->updated_at->getTimestamp());
            $all_tokens_data->push($individual_token_data);
        }

        // Metadata
        $all_metadatas_data = new Collection();
        $entire_database->put("metadatas",$all_metadatas_data);
        foreach(Metadata::all() as $metadata){
            $individual_metadata_data = new Collection();
            $individual_metadata_data->put("flid",$metadata->flid);
            $individual_metadata_data->put("pid",$metadata->pid);
            $individual_metadata_data->put("fid",$metadata->fid);
            $individual_metadata_data->put("name",$metadata->name);
            $individual_metadata_data->put("created_at",$metadata->created_at->getTimestamp());
            $individual_metadata_data->put("updated_at",$metadata->updated_at->getTimestamp());
            $all_metadatas_data->push($individual_metadata_data);
        }

        // Revision
        $all_revisions_data = new Collection();
        $entire_database->put("revisions",$all_revisions_data);
        foreach(Revision::all() as $revision){
            $individual_revision_data = new Collection();
            $individual_revision_data->put("id",$revision->id);
            $individual_revision_data->put("fid",$revision->fid);
            $individual_revision_data->put("rid",$revision->rid);
            $individual_revision_data->put("userId",$revision->userId);
            $individual_revision_data->put("owner",$revision->owner);
            $individual_revision_data->put("type",$revision->type);
            $individual_revision_data->put("data",$revision->data);
            $individual_revision_data->put("oldData",$revision->oldData);
            $individual_revision_data->put("rollback",$revision->rollback);
            $individual_revision_data->put("created_at",$revision->created_at->getTimestamp());
            $individual_revision_data->put("updated_at",$revision->updated_at->getTimestamp());
            $all_revisions_data->push($individual_revision_data);
        }

		$entire_database->put("revisions",Revision::all());

		return $entire_database;
	}

	public function selectRestore(Request $request){
        $available_backups = Storage::files($this->BACKUP_DIRECTORY);
		$saved_backups = new Collection();


		foreach($available_backups as $backup){
			$backup_info = new Collection();
			$backup_file = Storage::get($backup);
			$parsed_data = json_decode($backup_file);
			$backup_info->put("date",$parsed_data->kora3->date);
			$backup_info->put("name",$parsed_data->kora3->name);
			$backup_info->put("user",$parsed_data->kora3->created_by);
			$saved_backups->push($backup_info);
		}

		$request->session()->put("restore_points_available",$available_backups);
        return view('backups.restore',compact('saved_backups'));
    }

    public function loadRestore(Request $request){
		$backup_directory = "../storage/app/backups/user_upload/";
		$this->validate($request,[
			'backup_source'=>'required|in:server,upload',
			'restore_point'=>'required_if:backup_source,server',
			'upload_file'=>'required_if:backup_source,upload'
			]);

		if($request->input("backup_source") == "server"){
			$available_backups = $request->session()->get("restore_points_available"); //Same array as previous page (in case file was deleted and indices changed)
			try {
				$filename = $available_backups[$request->input("restore_point")]; //Using index in array so user can't provide weird or malicious file names
			}
			catch(\Exception $e){
				flash()->overlay("The restore point you selected is not valid.","Whoops!"); //This can happen if another user deleted the backup or if the params were edited before POST
				return redirect()->back();
			}
			return $this->parseRestore($filename);
		}
		else if($request->input("backup_source") == "upload"){
			if($request->hasFile("upload_file") == true){
				$file = $request->file("upload_file");
				$new_file_name = "user_upload_" . time() . ".kora3_backup";
				$filename = "backups/user_upload/".$new_file_name;
				if($file->isValid()){
					try {
						$file->move($backup_directory,$new_file_name);
					}
					catch(\Exception $e){
						flash()->overlay("The file could not be moved to the backup directory.","Whoops!");
						return redirect()->back();
					}
					return $this->parseRestore($filename);
				}
				else{
					flash()->overlay("There is something wrong with the file that was uploaded","Whoops!");
					return redirect()->back();
				}
			}
			else{
				flash()->overlay("No file was uploaded.","Whoops!");
				return redirect()->back();
			}
		}
		else{
			return redirect()->back();
		}
    }

	public function parseRestore($filepath){
		try{
			$data = Storage::get($filepath);

		}
		catch(\Exception $e){
			flash()->overlay("The backup file at " .$filepath." couldn't be opened.  Make sure it still exists and the permissions are correct.","Whoops!");
			dd($e);
			return redirect("/backup/restore");
		}
		$obj = json_decode($data);
		//echo($obj);

		foreach(User::all() as $User){
			if($User->id == 1){
				continue;
			}
			else{
				$User->delete();
			}
		}
		echo "<br>";
		echo "All users were deleted!";
        sleep(100);
		foreach(Project::all() as $Project){
			$Project->delete();
		}
		foreach(Form::all() as $Form){
			$Form->delete();
		}
		foreach(Field::all() as $Field){
			$Field->delete();
		}
		foreach(Record::all() as $Record){
			$Record->delete();
		}
		foreach(Metadata::all() as $Metadata){
			$Metadata->delete();
		}
		foreach(Token::all() as $Token){
			$Token->delete();
		}
		foreach(Revision::all() as $Revision){
			$Revision->delete();
		}
		echo "<br>";
		echo "All projects, forms, fields, records, revisions, tokens, metadatas deleted";
		echo "<br>";
		//try {
            // User
			foreach($obj->users as $user){
				$new_user = User::create(array("username"=>$user->username,"name"=>$user->name,"email"=>$user->email,"password"=>$user->password,"organization"=>$user->organization,"language"=>$user->language,"regtoken"=>$user->regtoken));
				$new_user->id = $user->id;
				$new_user->admin = $user->admin;
				$new_user->active = $user->active;
				$new_user->remember_token = $user->remember_token;
				$new_user->created_at = $user->created_at;
				$new_user->updated_at = $user->updated_at;
				$new_user->save();
				echo("<br>");
				echo("Restored User: ".$new_user);
            }

            // Project
			foreach ($obj->projects as $project) {
				//$new_project = Project::create(array("name" => $project->name, "slug" => $project->slug, "description" => $project->description, "adminGID" => $project->adminGID, "active" => $project->active));
				$new_project = Project::create(array());
                $new_project->name = $project->name;
                $new_project->slug = $project->slug;
                $new_project->description = $project->description;
                $new_project->adminGID = $project->adminGID;
                $new_project->active = $project->active;
                $new_project->pid = $project->pid;
				$new_project->created_at = $project->created_at;
				$new_project->updated_at = $project->updated_at;
				$new_project->save();
				echo("<br>");
				echo("Restored Project: " . $new_project);
			}

            // Form
			foreach ($obj->forms as $form) {
				$new_form = Form::create(array("pid" => $form->pid));
                $new_form->fid = $form->fid;
                $new_form->name = $form->name;
                $new_form->slug = $form->slug;
                $new_form->description = $form->description;
                $new_form->adminGID = $form->adminGID;
                $new_form->layout = $form->layout;
                $new_form->public_metadata = $form->public_metadata;
				$new_form->layout = $form->layout;
				$new_form->adminGID = $form->adminGID;
				$new_form->created_at = $form->created_at;
				$new_form->updated_at = $form->updated_at;
                $new_form->save();
				echo("<br>");
				echo("Restored Form: " . $new_form);
			}

            // Field
			foreach ($obj->fields as $field) {
				$new_field = Field::create(array("pid" => $field->pid, "fid" => $field->fid, "order" => $field->order, "type" => $field->type, "name" => $field->name, "slug" => $field->slug, "desc" => $field->desc, "required" => $field->required, "default" => $field->default, "options" => $field->options));
				$new_field->flid = $field->flid; //Carbon may not be needed here, Laravel may take care of this conversion
                $new_field->default = $field->default;
                $new_field->options = $field->options;
				$new_field->created_at = Carbon::createFromTimestampUTC($field->created_at);
				$new_field->updated_at = Carbon::createFromTimestampUTC($field->updated_at);
				$new_field->save();
				echo("<br>");
				echo("Restored Field: " . $new_field);
			}

            // FormGroup
			foreach ($obj->formgroups as $formgroup) {
				$new_formgroup = new FormGroup();
				$new_formgroup->name = $formgroup->group_data->name;
				$new_formgroup->fid = $formgroup->group_data->fid;
				$new_formgroup->create = $formgroup->group_data->create;
				$new_formgroup->edit = $formgroup->group_data->edit;
				$new_formgroup->ingest = $formgroup->group_data->ingest;
				$new_formgroup->delete = $formgroup->group_data->delete;
				$new_formgroup->modify = $formgroup->group_data->modify;
				$new_formgroup->destroy = $formgroup->group_data->destroy;
				$new_formgroup->id = $formgroup->group_data->id;
				$new_formgroup->created_at = $formgroup->group_data->created_at;
				$new_formgroup->updated_at = $formgroup->group_data->updated_at;
				$new_formgroup->save();
				foreach($formgroup->user_data as $user_id){
					$new_formgroup->users()->attach($user_id);
				}

				echo("<br>");
				echo("Restored FormGroup:" . $new_formgroup);
			}

            // ProjectGroup
			foreach($obj->projectgroups as $projectgroup){
				$new_projectgroup = new ProjectGroup();
				$new_projectgroup->id = $projectgroup->group_data->id;
				$new_projectgroup->name = $projectgroup->group_data->name;
				$new_projectgroup->pid = $projectgroup->group_data->pid;
				$new_projectgroup->create = $projectgroup->group_data->create;
				$new_projectgroup->edit = $projectgroup->group_data->edit;
				$new_projectgroup->delete = $projectgroup->group_data->delete;
				$new_projectgroup->created_at = $projectgroup->group_data->created_at;
				$new_projectgroup->updated_at = $projectgroup->group_data->updated_at;
				$new_projectgroup->save();
				foreach($projectgroup->user_data as $user_id){
					$new_projectgroup->users()->attach($user_id);
				}

				echo("<br>");
				echo("Restored ProjectGroup:" . $new_projectgroup);
			}

            // Record
			foreach($obj->records as $record){
				$new_record = new Record(array("pid"=>$record->pid,"fid"=>$record->fid,"owner"=>$record->owner,"kid"=>$record->kid));
				$new_record->rid = $record->rid;
				$new_record->created_at = $record->created_at;
				$new_record->updated_at = $record->updated_at;
				$new_record->save();
				echo "<br>";
				echo "Restored Record: ".$new_record->kid;
			}

            // TextField
			foreach($obj->textfields as $textfield){
				$new_textfield = new TextField(array("rid"=>$textfield->rid,"flid"=>$textfield->flid,"text"=>$textfield->text));
				$new_textfield->id = $textfield->id;
				$new_textfield->created_at = $textfield->created_at;
				$new_textfield->updated_at = $textfield->updated_at;
				$new_textfield->save();
				echo "<br>";
				echo "Restored TextField: ".$new_textfield->rid;
			}

            // RichTextField
			foreach($obj->richtextfields as $richtextfield){
				$new_richtextfield = new RichTextField(array("rid"=>$richtextfield->rid,"flid"=>$richtextfield->flid,"rawtext"=>$richtextfield->rawtext));
				$new_richtextfield->id = $richtextfield->id;
				$new_richtextfield->created_at = $richtextfield->created_at;
				$new_richtextfield->updated_at = $richtextfield->updated_at;
				$new_richtextfield->save();
				echo "<br>";
				echo "Restored RichTextField: ".$new_richtextfield->rid;
			}

            // NumberField
			foreach($obj->numberfields as $numberfield){
				$new_numberfield = new NumberField(array("rid"=>$numberfield->rid,"flid"=>$numberfield->flid,"number"=>$numberfield->number));
				$new_numberfield->id = $numberfield->id;
				$new_numberfield->created_at = $numberfield->created_at;
				$new_numberfield->updated_at = $numberfield->updated_at;
				$new_numberfield->save();
				echo "<br>";
				echo "Restored NumberField: ".$new_numberfield->rid;
			}

            // ListField
			foreach($obj->listfields as $listfield){
				$new_listfield = new ListField(array("rid"=>$listfield->rid,"flid"=>$listfield->flid,"option"=>$listfield->option));
				$new_listfield->id = $listfield->id;
				$new_listfield->created_at = $listfield->created_at;
				$new_listfield->updated_at = $listfield->updated_at;
				$new_listfield->save();
				echo "<br>";
				echo "Restored ListField: ".$new_listfield->rid;
			}

            // GeneratedListField
			foreach($obj->generatedlistfields as $generatedlistfield){
				$new_generatedlistfield = new GeneratedListField(array("rid"=>$generatedlistfield->rid,"flid"=>$generatedlistfield->flid,"options"=>$generatedlistfield->options));
				$new_generatedlistfield->id = $generatedlistfield->id;
				$new_generatedlistfield->created_at = $generatedlistfield->created_at;
				$new_generatedlistfield->updated_at = $generatedlistfield->updated_at;
				$new_generatedlistfield->save();
				echo "<br>";
				echo "Restored GeneratedListField: ".$new_generatedlistfield->rid;
			}

            // MultiSelectListField
			foreach($obj->multiselectlistfields as $multiselectlistfield){
				$new_multiselectlistfield = new MultiSelectListField(array("rid"=>$multiselectlistfield->rid,"flid"=>$multiselectlistfield->flid,"options"=>$multiselectlistfield->options));
				$new_multiselectlistfield->id = $multiselectlistfield->id;
				$new_multiselectlistfield->created_at = $multiselectlistfield->created_at;
				$new_multiselectlistfield->updated_at = $multiselectlistfield->updated_at;
				$new_multiselectlistfield->save();
				echo "<br>";
				echo "Restored MultiSelectListField: ".$new_multiselectlistfield->rid;
			}

            // DateField
            foreach($obj->datefields as $datefield){
                $new_datefield = new DateField();
                $new_datefield->id = $datefield->id;
                $new_datefield->rid = $datefield->rid;
                $new_datefield->flid = $datefield->flid;
                $new_datefield->circa = $datefield->circa;
                $new_datefield->month = $datefield->month;
                $new_datefield->day = $datefield->day;
                $new_datefield->year = $datefield->year;
                $new_datefield->era = $datefield->era;
                $new_datefield->created_at = $datefield->created_at;
                $new_datefield->updated_at = $datefield->updated_at;
                $new_datefield->save();
                echo "<br>";
                echo "Restored DateField: ".$new_datefield->id;
            }

            // ScheduleField
			foreach($obj->schedulefields as $schedulefield){
				$new_schedulefield = new ScheduleField(array("rid"=>$schedulefield->rid,"flid"=>$schedulefield->flid,"events"=>$schedulefield->events));
                $new_schedulefield->id = $schedulefield->id;
                $new_schedulefield->created_at = $schedulefield->created_at;
                $new_schedulefield->updated_at = $schedulefield->updated_at;
                $new_schedulefield->save();
                echo "<br>";
                echo "Restored ScheduleField: ".$new_schedulefield->rid." ";
			}

            // Token
			foreach($obj->tokens as $token){
				$new_token = new Token(array('token'=>$token->token, 'type'=>$token->type));
				$new_token->id = $token->id;
				$new_token->created_at = $token->created_at;
				$new_token->updated_at = $token->updated_at;
				$new_token->save();
				echo "<br>";
				echo "Restored Token: ".$new_token;
			}

            // Metadata
            foreach($obj->metadatas as $metadata){
                $new_metadata = new Metadata(array());
                $new_metadata->flid = $metadata->flid;
                $new_metadata->pid = $metadata->pid;
                $new_metadata->fid = $metadata->fid;
                $new_metadata->name = $metadata->name;
                $new_metadata->created_at = $metadata->created_at;
                $new_metadata->updated_at = $metadata->updated_at;
                $new_metadata->save();
                echo "<br>";
                echo "Restored Metadata: ".$new_metadata->name;
            }

            // Revision
			foreach($obj->revisions as $revision){
				$new_revision = new Revision(array('id'=>$revision->id,'fid'=>$revision->fid,'rid'=>$revision->rid,'userId'=>$revision->userId,'type'=>$revision->type,'data'=>$revision->data,'oldData'=>$revision->oldData,'rollback'=>$revision->rollback));
				$new_revision->created_at = $revision->created_at;
				$new_revision->updated_at = $revision->updated_at;
				$new_revision->save();
				echo "<br>";
				echo "Restored Revision: ".$new_revision->id . " ".$new_revision->type;
			}
		//}catch(\Exception $e){
			//flash()->overlay("The restore failed.","Whoops!");
			//return redirect("/backup/restore");
		//}



	}
}