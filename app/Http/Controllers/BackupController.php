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
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use \Illuminate\Support\Facades\App;
Use \Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class BackupController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Backup Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles creation of backup files, saving them as a restore point, downloading them to the
    | user's computer, restoring from a saved or uploaded file, and locking and unlocking users during operations.
    |
    */

    private $BACKUP_DIRECTORY = "backups"; //Set the backup directory relative to laravel/storaage/app
    private $UPLOAD_DIRECTORY = "backups/user_upload/"; //Set the upload directory relative to laravel/storage/app


    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
        $this->middleware('admin');
        if(Auth::check()){
            if(Auth::user()->id != 1){
                flash()->overlay("Only the default admin can view that page","Whoops.");
                return redirect("/projects")->send();
            }
        }

        $this->ajax_error_list = new Collection(); //The Exception's getMessage() for data that didn't restore/backup
    }
    /*
     * This method retrieves all restore points saved on the server, and displays a view
     * for the user to create a new backup, revert to a restore point, or to upload a backup file
     *
     * @params Request $request
     * @return view
     */
    public function index(Request $request){

        $available_backups = Storage::files($this->BACKUP_DIRECTORY);
        $saved_backups = new Collection();

        //Load all previously saved backups, and package them up so they can be displayed by the view
        $available_backups_index = 0;
        foreach($available_backups as $backup){
            $backup_info = new Collection();
            $backup_file = Storage::get($backup);
            $parsed_data = json_decode($backup_file);
            $backup_info->put("index",$available_backups_index); //We sort this later,  but it needs to refer to other
            $available_backups_index++;
            try {
                $backup_info->put("date", $parsed_data->kora3->date);
                $backup_info->put("timestamp",Carbon::parse($parsed_data->kora3->date)->timestamp);
            }
            catch(\Exception $e){
                $backup_info->put("date","Unknown");
                $backup_info->put("timestamp",Carbon::now()->timestamp);
            }
            try {
                $backup_info->put("name", $parsed_data->kora3->name);
            }
            catch(\Exception $e){
                $backup_info->put("name","Unknown");
            }
            try{
                $backup_info->put("user",$parsed_data->kora3->created_by);
            }
            catch(\Exception $e){
                $backup_info->put("user","Unknown");
            }

            $saved_backups->push($backup_info);
        }
        $saved_backups = $saved_backups->sortByDesc(function($item){
            return $item->get('timestamp');
        });
        $request->session()->put("restore_points_available",$available_backups);
        return view('backups.index',compact('saved_backups'));
    }


    /*
     * This method validates the backup info, then displays a view with a progress bar
     * the view makes an AJAX call to BackupController@create to start the backup process
     *
     * @params Request $request
     * @return view
     */
    public function startBackup(Request $request){

        $this->validate($request,[
            'backup_label'=>'required|alpha_dash',
        ]);
        $backup_label = $request->input("backup_label");
        $request->session()->put("backup_new_label",$backup_label);
        return view('backups.backup',compact('backup_label'));
    }


    /*
     * This method should be called via AJAX and will create a backup,
     * then return success or error information through JSON.
     *
     * @params Request $request
     * @return response
     */
	public function create(Request $request){

        $users_exempt_from_lockout = new Collection();
        $users_exempt_from_lockout->put(1,1); //Add another one of these with (userid,userid) to exempt extra users

        $this->lockUsers($users_exempt_from_lockout);
        if($request->session()->has("backup_new_label")){
            $backup_label = $request->session()->get("backup_new_label");
            $request->session()->forget("backup_new_label");
        }
        else{
            $backup_label = "";
        }
		$this->backup_filename = Carbon::now()->format("Y-m-d_H:i:s"). ".kora3_backup";

		$this->backup_data = $this->saveDatabase($backup_label);

        $this->backup_filepath = $this->BACKUP_DIRECTORY."/".$this->backup_filename;

		if(Storage::exists($this->backup_filepath)){
            $this->unlockUsers();
            $this->ajaxResponse(false,"Could not create the backup, a file with that name already exists.");
		}
		else{
            try {
                Storage::put($this->backup_filepath, $this->backup_data);
            }
            catch(\Exception $e){
                $this->ajax_error_list->push($e->getMessage());
                $this->ajaxResponse(false,"The backup failed, unable to save the backup file.");
            }
		}
        $this->unlockUsers();
        if($this->ajax_error_list->count() >0){
            $this->ajaxResponse(false,"The backup completed with errors, restoring this file may not work.");
        }
        else{
            $request->session()->put("backup_file_name",$this->backup_filename);
            $this->ajaxResponse(true,"The backup completed successfully");
        }
	}

    /*
     * This method allows the user to download a backup file once.
     * It retrieves the file name from the session, then deletes it from session,
     * then sends the file as response.  If there is no filename, it flashes an error.
     *
     * @params Request $request
     * @return response
     */
    public function download(Request $request){
        if($request->session()->has("backup_file_name")){
            $filename = $request->session()->get("backup_file_name");
            $request->session()->forget("backup_file_name");
            return response()->download((realpath("../storage/app/".$this->BACKUP_DIRECTORY."/".$filename)),$filename,array("Content-Type"=>"application/octet-stream"));
        }
        else{
            flash()->overlay("There is no file available right now.  You may have already downloaded the file, or there may have been an error during the backup process","Whoops.");
            return redirect("/");
        }
    }

    /*
     * This method loops through all of the models and returns them all in a collection.
     * The $backup_name is a friendly name for the backup.
     *
     * If there is an error with just a row, it will add it to $ajax_error_list
     * If there is a more serious error, it will stop and immediately send a JSON response with the error
     *
     * @params String $backup_name
     * @return Collection
     */
	public function saveDatabase($backup_name){

		$entire_database = new Collection(); //This will hold literally the entire database and then some

        //Some info about the backup itself
        $backup_info = new Collection();
        $backup_info->put("version","1");   //In case a breaking change happens in the future (like new table added or a table removed)
        $backup_info->put("date",Carbon::now()->toDateTimeString()); //UTC time the backup started
        $backup_info->put("name",$backup_name); //A user-assigned name for the backup
        $backup_info->put("created_by",Auth::user()->email); //The email for the user that created it

        $entire_database->put("kora3",$backup_info);

		//Models that have data in the database should be put into the $entire_database collection
		//You need to loop through all of your table's columns and add them first to this function, then to restore
		//Don't forget to include important information about relationships like pivot tables (See FormGroups for example)
        //Project
        $all_projects_data = new Collection();
        $entire_database->put("projects",$all_projects_data);
        try {
            //Everything is inside this try-catch block, so if something goes wrong, the exception's getMessage will
            //be displayed to the user.  If there is an error backing up an individual row, it will try to continue.
            //If there is something more serious (ex. kora3_projects table doesn't exist), it will stop.


            // Project
            foreach (Project::all() as $project) {
                try {
                    $individual_project_data = new Collection();
                    $individual_project_data->put("pid", $project->pid);
                    $individual_project_data->put("name", $project->name);
                    $individual_project_data->put("slug", $project->slug);
                    $individual_project_data->put("description", $project->description);
                    $individual_project_data->put("adminGID", $project->adminGID);
                    $individual_project_data->put("active", $project->active);
                    $individual_project_data->put("created_at", $project->created_at->toDateTimeString());
                    $individual_project_data->put("updated_at", $project->updated_at->toDateTimeString());
                    $all_projects_data->push($individual_project_data);
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // Form
            $all_forms_data = new Collection();
            $entire_database->put("forms", $all_forms_data);
            foreach (Form::all() as $form) {
                try {
                    $individual_form_data = new Collection();
                    $individual_form_data->put("fid", $form->fid);
                    $individual_form_data->put("pid", $form->pid);
                    $individual_form_data->put("adminGID", $form->adminGID);
                    $individual_form_data->put("name", $form->name);
                    $individual_form_data->put("slug", $form->slug);
                    $individual_form_data->put("description", $form->description);
                    $individual_form_data->put("layout", $form->layout);
                    $individual_form_data->put("public_metadata", $form->public_metadata);
                    $individual_form_data->put("created_at", $form->created_at->toDateTimeString());
                    $individual_form_data->put("updated_at", $form->updated_at->toDateTimeString());
                    $all_forms_data->push($individual_form_data);
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // FormGroup
            $all_formgroup_data = new Collection();
            $entire_database->put("formgroups", $all_formgroup_data);
            foreach (FormGroup::all() as $formgroup) {
                try {
                    $individual_formgroup_data = new Collection();
                    $group_data = new Collection();
                    $group_data->put("id", $formgroup->id);
                    $group_data->put("name", $formgroup->name);
                    $group_data->put("fid", $formgroup->fid);
                    $group_data->put("create", $formgroup->create);
                    $group_data->put("edit", $formgroup->edit);
                    $group_data->put("delete", $formgroup->delete);
                    $group_data->put("ingest", $formgroup->ingest);
                    $group_data->put("modify", $formgroup->modify);
                    $group_data->put("destroy", $formgroup->destroy);
                    $group_data->put("created_at", $formgroup->created_at->toDateTimeString());
                    $group_data->put("updated_at", $formgroup->updated_at->toDateTimeString());
                    $individual_formgroup_data->put("group_data", $group_data);
                    $individual_formgroup_data->put("user_data", $formgroup->users()->get()->modelKeys());
                    $all_formgroup_data->push($individual_formgroup_data);
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // ProjectGroup
            $all_projectgroup_data = new Collection();
            $entire_database->put("projectgroups", $all_projectgroup_data);
            foreach (ProjectGroup::all() as $projectgroup) {
                try {
                    $individual_projectgroup_data = new Collection();
                    $group_data = new Collection();
                    $group_data->put("id", $projectgroup->id);
                    $group_data->put("name", $projectgroup->name);
                    $group_data->put("pid", $projectgroup->pid);
                    $group_data->put("create", $projectgroup->create);
                    $group_data->put("edit", $projectgroup->edit);
                    $group_data->put("delete", $projectgroup->delete);
                    $group_data->put("created_at", $projectgroup->created_at->toDateTimeString());
                    $group_data->put("updated_at", $projectgroup->updated_at->toDateTimeString());
                    $individual_projectgroup_data->put("group_data", $group_data);
                    $individual_projectgroup_data->put("user_data", $projectgroup->users()->get()->modelKeys());
                    $all_projectgroup_data->push($individual_projectgroup_data);
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // User
            $all_users_data = new Collection();
            $entire_database->put("users", $all_users_data);
            foreach (User::all() as $user) {
                try {
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
                    $individual_user_data->put("created_at", $user->created_at->toDateTimeString());
                    $individual_user_data->put("updated_at", $user->updated_at->toDateTimeString());
                    $all_users_data->push($individual_user_data);
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // Field
            $all_fields_data = new Collection();
            $entire_database->put("fields", $all_fields_data);
            foreach (Field::all() as $field) {
                try {
                    $individual_field_data = new Collection();
                    $individual_field_data->put("flid", $field->flid);
                    $individual_field_data->put("pid", $field->pid);
                    $individual_field_data->put("fid", $field->fid);
                    $individual_field_data->put("order", $field->order);
                    $individual_field_data->put("type", $field->type);
                    $individual_field_data->put("name", $field->name);
                    $individual_field_data->put("slug", $field->slug);
                    $individual_field_data->put("desc", $field->desc);
                    $individual_field_data->put("required", $field->required);
                    $individual_field_data->put("default", $field->default);
                    $individual_field_data->put("options", $field->options);
                    $individual_field_data->put("created_at", $field->created_at->toDateTimeString());
                    $individual_field_data->put("updated_at", $field->updated_at->toDateTimeString());
                    $all_fields_data->push($individual_field_data);
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }

            }

            // Record
            $all_records_data = new Collection();
            $entire_database->put("records", $all_records_data);
            foreach (Record::all() as $record) {
                try {
                    $individual_record_data = new Collection();
                    $individual_record_data->put("rid", $record->rid);
                    $individual_record_data->put("kid", $record->kid);
                    $individual_record_data->put("pid", $record->pid);
                    $individual_record_data->put("fid", $record->fid);
                    $individual_record_data->put("owner", $record->owner);
                    $individual_record_data->put("created_at", $record->created_at->toDateTimeString());
                    $individual_record_data->put("updated_at", $record->updated_at->toDateTimeString());
                    $all_records_data->push($individual_record_data);
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // TextField
            $all_textfields_data = new Collection();
            $entire_database->put("textfields", $all_textfields_data);
            foreach (TextField::all() as $textfield) {
                try {
                    $individual_textfield_data = new Collection();
                    $individual_textfield_data->put("id", $textfield->id);
                    $individual_textfield_data->put("rid", $textfield->rid);
                    $individual_textfield_data->put("flid", $textfield->flid);
                    $individual_textfield_data->put("text", $textfield->text);
                    $individual_textfield_data->put("created_at", $textfield->created_at->toDateTimeString());
                    $individual_textfield_data->put("updated_at", $textfield->updated_at->toDateTimeString());
                    $all_textfields_data->push($individual_textfield_data);
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            //  RichTextField
            $all_richtextfields_data = new Collection();
            $entire_database->put("richtextfields", $all_richtextfields_data);
            foreach (RichTextField::all() as $richtextfield) {
                try {
                    $individual_richtextfield_data = new Collection();
                    $individual_richtextfield_data->put("id", $richtextfield->id);
                    $individual_richtextfield_data->put("rid", $richtextfield->rid);
                    $individual_richtextfield_data->put("flid", $richtextfield->flid);
                    $individual_richtextfield_data->put("rawtext", $richtextfield->rawtext);
                    $individual_richtextfield_data->put("created_at", $richtextfield->created_at->toDateTimeString());
                    $individual_richtextfield_data->put("updated_at", $richtextfield->updated_at->toDateTimeString());
                    $all_richtextfields_data->push($individual_richtextfield_data);
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            //  NumberField
            $all_numberfields_data = new Collection();
            $entire_database->put("numberfields", $all_numberfields_data);
            foreach (NumberField::all() as $numberfield) {
                try {
                    $individual_numberfield_data = new Collection();
                    $individual_numberfield_data->put("id", $numberfield->id);
                    $individual_numberfield_data->put("rid", $numberfield->rid);
                    $individual_numberfield_data->put("flid", $numberfield->flid);
                    $individual_numberfield_data->put("number", $numberfield->number);
                    $individual_numberfield_data->put("created_at", $numberfield->created_at->toDateTimeString());
                    $individual_numberfield_data->put("updated_at", $numberfield->updated_at->toDateTimeString());
                    $all_numberfields_data->push($individual_numberfield_data);
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // ListField
            $all_listfields_data = new Collection();
            $entire_database->put("listfields", $all_listfields_data);
            foreach (ListField::all() as $listfield) {
                try {
                    $individual_listfield_data = new Collection();
                    $individual_listfield_data->put("id", $listfield->id);
                    $individual_listfield_data->put("rid", $listfield->rid);
                    $individual_listfield_data->put("flid", $listfield->flid);
                    $individual_listfield_data->put("option", $listfield->option);
                    $individual_listfield_data->put("created_at", $listfield->created_at->toDateTimeString());
                    $individual_listfield_data->put("updated_at", $listfield->updated_at->toDateTimeString());
                    $all_listfields_data->push($individual_listfield_data);
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // GeneratedListField
            $all_generatedlistfields_data = new Collection();
            $entire_database->put("generatedlistfields", $all_generatedlistfields_data);
            foreach (GeneratedListField::all() as $generatedlistfield) {
                try {
                    $individual_generatedlistfield_data = new Collection();
                    $individual_generatedlistfield_data->put("id", $generatedlistfield->id);
                    $individual_generatedlistfield_data->put("rid", $generatedlistfield->rid);
                    $individual_generatedlistfield_data->put("flid", $generatedlistfield->flid);
                    $individual_generatedlistfield_data->put("options", $generatedlistfield->options);
                    $individual_generatedlistfield_data->put("created_at", $generatedlistfield->created_at->toDateTimeString());
                    $individual_generatedlistfield_data->put("updated_at", $generatedlistfield->updated_at->toDateTimeString());
                    $all_generatedlistfields_data->push($individual_generatedlistfield_data);
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // MultiSelectListField
            $all_multiselectlistfields_data = new Collection();
            $entire_database->put("multiselectlistfields", $all_multiselectlistfields_data);
            foreach (MultiSelectListField::all() as $multiselectlistfield) {
                try {
                    $individual_multiselectlistfield_data = new Collection();
                    $individual_multiselectlistfield_data->put("id", $multiselectlistfield->id);
                    $individual_multiselectlistfield_data->put("rid", $multiselectlistfield->rid);
                    $individual_multiselectlistfield_data->put("flid", $multiselectlistfield->flid);
                    $individual_multiselectlistfield_data->put("options", $multiselectlistfield->options);
                    $individual_multiselectlistfield_data->put("created_at", $multiselectlistfield->created_at->toDateTimeString());
                    $individual_multiselectlistfield_data->put("updated_at", $multiselectlistfield->updated_at->toDateTimeString());
                    $all_multiselectlistfields_data->push($individual_multiselectlistfield_data);
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // DateField
            $all_datefields_data = new Collection();
            $entire_database->put("datefields", $all_datefields_data);
            foreach (DateField::all() as $datefield) {
                try {
                    $individual_datefield_data = new Collection();
                    $individual_datefield_data->put("id", $datefield->id);
                    $individual_datefield_data->put("rid", $datefield->rid);
                    $individual_datefield_data->put("flid", $datefield->flid);
                    $individual_datefield_data->put("circa", $datefield->circa);
                    $individual_datefield_data->put("month", $datefield->month);
                    $individual_datefield_data->put("day", $datefield->year);
                    $individual_datefield_data->put("year", $datefield->year);
                    $individual_datefield_data->put("era", $datefield->era);
                    $individual_datefield_data->put("created_at", $datefield->created_at->toDateTimeString());
                    $individual_datefield_data->put("updated_at", $datefield->updated_at->toDateTimeString());
                    $all_datefields_data->push($individual_datefield_data);
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // ScheduleField
            $all_schedulefields_data = new Collection();
            $entire_database->put("schedulefields", $all_schedulefields_data);
            foreach (ScheduleField::all() as $schedulefield) {
                try {
                    $individual_schedulefield_data = new Collection();
                    $individual_schedulefield_data->put("id", $schedulefield->id);
                    $individual_schedulefield_data->put("rid", $schedulefield->rid);
                    $individual_schedulefield_data->put("flid", $schedulefield->flid);
                    $individual_schedulefield_data->put("events", $schedulefield->events);
                    $individual_schedulefield_data->put("created_at", $schedulefield->created_at->toDateTimeString());
                    $individual_schedulefield_data->put("updated_at", $schedulefield->updated_at->toDateTimeString());
                    $all_schedulefields_data->push($individual_schedulefield_data);
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }


            //  GeolocatorField
            $all_geolocatorfields_data = new Collection();
            $entire_database->put("geolocatorfields", $all_geolocatorfields_data);
            foreach (GeolocatorField::all() as $geolocatorfield) {
                try {
                    $individual_geolocatorfield_data = new Collection();
                    $individual_geolocatorfield_data->put("id", $geolocatorfield->id);
                    $individual_geolocatorfield_data->put("rid", $geolocatorfield->rid);
                    $individual_geolocatorfield_data->put("flid", $geolocatorfield->flid);
                    $individual_geolocatorfield_data->put("locations",$geolocatorfield->locations);
                    $individual_geolocatorfield_data->put("created_at", $geolocatorfield->created_at->toDateTimeString());
                    $individual_geolocatorfield_data->put("updated_at", $geolocatorfield->updated_at->toDateTimeString());
                    $all_geolocatorfields_data->push($individual_geolocatorfield_data);
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            //  Token
            $all_tokens_data = new Collection();
            $entire_database->put("tokens", $all_tokens_data);
            foreach (Token::all() as $token) {
                try {
                    $individual_token_data = new Collection();
                    $individual_token_data->put("id", $token->id);
                    $individual_token_data->put("type", $token->type);
                    $individual_token_data->put("token", $token->token);
                    $individual_token_data->put("created_at", $token->created_at->toDateTimeString());
                    $individual_token_data->put("updated_at", $token->updated_at->toDateTimeString());
                    $all_tokens_data->push($individual_token_data);
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // Metadata
            $all_metadatas_data = new Collection();
            $entire_database->put("metadatas", $all_metadatas_data);
            foreach (Metadata::all() as $metadata) {
                try {
                    $individual_metadata_data = new Collection();
                    $individual_metadata_data->put("flid", $metadata->flid);
                    $individual_metadata_data->put("pid", $metadata->pid);
                    $individual_metadata_data->put("fid", $metadata->fid);
                    $individual_metadata_data->put("name", $metadata->name);
                    $individual_metadata_data->put("created_at", $metadata->created_at->toDateTimeString());
                    $individual_metadata_data->put("updated_at", $metadata->updated_at->toDateTimeString());
                    $all_metadatas_data->push($individual_metadata_data);
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // Revision
            $all_revisions_data = new Collection();
            $entire_database->put("revisions", $all_revisions_data);
            foreach (Revision::all() as $revision) {
                try {
                    $individual_revision_data = new Collection();
                    $individual_revision_data->put("id", $revision->id);
                    $individual_revision_data->put("fid", $revision->fid);
                    $individual_revision_data->put("rid", $revision->rid);
                    $individual_revision_data->put("userId", $revision->userId);
                    $individual_revision_data->put("owner", $revision->owner);
                    $individual_revision_data->put("type", $revision->type);
                    $individual_revision_data->put("data", $revision->data);
                    $individual_revision_data->put("oldData", $revision->oldData);
                    $individual_revision_data->put("rollback", $revision->rollback);
                    $individual_revision_data->put("created_at", $revision->created_at->toDateTimeString());
                    $individual_revision_data->put("updated_at", $revision->updated_at->toDateTimeString());
                    $all_revisions_data->push($individual_revision_data);
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }
        }
        catch(\Exception $e){
            $this->ajax_error_list->push($e->getMessage());
            $this->ajaxResponse(false,"The backup failed, correct these errors and try again.");
        }

		return $entire_database;
	}

    /*
    * This method validates the restore info, then displays a view with a progress bar
    * the view makes an AJAX call to BackupController@restoreData to start the restore process
    *
    * @params Request $request
    * @return view
    */
    public function startRestore(Request $request){


        $this->validate($request,[
            'backup_source'=>'required|in:server,upload',
            'restore_point'=>'required_if:backup_source,server',
            'upload_file'=>'required_if:backup_source,upload'
        ]);

        dd($request);
        if($request->input("backup_source") == "server"){
            $available_backups = $request->session()->get("restore_points_available"); //Same array as previous page (in case file was deleted and indices changed)
            try {
                $filename = $available_backups[$request->input("restore_point")]; //Using index in array so user can't provide weird or malicious file names
            }
            catch(\Exception $e){
                flash()->overlay("The restore point you selected is not valid.","Whoops!"); //This can happen if another user deleted the backup or if the params were edited before POST
                return redirect()->back();
            }
            $request->session()->put("restore_file_path",$filename);
        }
        else if($request->input("backup_source") == "upload"){
            if($request->hasFile("upload_file") == true){
                $file = $request->file("upload_file");
                $new_file_name = "user_upload_" . time() . ".kora3_backup";
                $filename = $this->UPLOAD_DIRECTORY."/".$new_file_name;
                if($file->isValid()){
                    try {
                        //First argument is relative to LARAVEL_ROOT/public (this is not Flysystem, it's something to do with Symfony?)
                        $file->move("../storage/app/".$this->UPLOAD_DIRECTORY,$new_file_name);
                        //$filename is used by Flysystem so it only needs path relative to LARAVEL/storage/app
                    }
                    catch(\Exception $e){
                        flash()->overlay("The file could not be moved to the backup directory.","Whoops!");
                        return redirect()->back();
                    }
                    $request->session()->put("restore_file_path",$filename);
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

        return view('backups.restore');
    }

    /*
     * Deletes all rows from the existing database, then creates new ones based on the JSON file
     * Expects to be called via AJAX, so the response is a JSON object that is
     * {"status": boolean, "message":"string","restore_errors":["array"]}
     *
     * along with an HTTP status code of either 200 or 500
     * If you get an error, it's likely that users are locked out of the application, so you must
     * call BackupController@unlockUsers() to restore access.
     *
     * @params Request $request
     * @return response
     */
	public function restoreData(Request $request){


        $this->json_file = null;
        $this->decoded_json = null;

        $users_exempt_from_lockout = new Collection();
        $users_exempt_from_lockout->put(1,1); //Add another one of these with (userid,userid) to exempt extra users

        $this->lockUsers($users_exempt_from_lockout);

        if($request->session()->has("restore_file_path")){
            $filepath = $request->session()->get("restore_file_path");
            $request->session()->forget("restore_file_path");
        }
        else{
            return $this->ajaxResponse(false,"You did not select a valid restore point or upload a valid backup file");
        }

		try{
			$this->json_file = Storage::get($filepath);
		}
		catch(\Exception $e){
            $this->ajaxResponse(false,"The backup file couldn't be opened.  Make sure it still exists and the permissions are correct.");
		}
        try {
            $this->decoded_json = json_decode($this->json_file);
            $this->decoded_json->kora3;
        }
        catch(\Exception $e){
            $this->ajaxResponse(false,"The backup file contains invalid JSON data, it may be corrupt or damaged.  Check the file or try another one.
            The restore did not start, so data already in the database was not deleted.");
        }

        $backup_data = $this->decoded_json;

        //Delete all existing data
        try {
            foreach (User::all() as $User) {
                if ($User->id == 1) { //Do not delete the default admin user
                    continue;
                } else {
                    $User->delete();
                }
            }
            foreach (Project::all() as $Project) {
                $Project->delete();
            }
            foreach (Form::all() as $Form) {
                $Form->delete();
            }
            foreach (Field::all() as $Field) {
                $Field->delete();
            }
            foreach (Record::all() as $Record) {
                $Record->delete();
            }
            foreach (Metadata::all() as $Metadata) {
                $Metadata->delete();
            }
            foreach (Token::all() as $Token) {
                $Token->delete();
            }
            foreach (Revision::all() as $Revision) {
                $Revision->delete();
            }
            foreach (DateField::all() as $DateField){
                $DateField->delete();
            }
            foreach(FormGroup::all() as $FormGroup){
                $FormGroup->delete();
            }
            foreach(GeneratedListField::all() as $GeneratedListField){
                $GeneratedListField->delete();
            }
            foreach(ListField::all() as $ListField){
                $ListField->delete();
            }
            foreach(MultiSelectListField::all() as $MultiSelectListField){
                $MultiSelectListField->delete();
            }
            foreach(NumberField::all() as $NumberField){
                $NumberField->delete();
            }
            foreach(ProjectGroup::all() as $ProjectGroup){
                $ProjectGroup->delete();
            }
            foreach(RichTextField::all() as $RichTextField){
                $RichTextField->delete();
            }
            foreach(ScheduleField::all() as $ScheduleField){
                $ScheduleField->delete();
            }
            foreach(TextField::all() as $TextField){
                $TextField->delete();
            }
        }catch(\Exception $e){
            $this->ajaxResponse(false, "There was a problem when attempting to remove existing information from the
            database, the database user may not have permission to do this or the database may be in use.");
        }
        try { //This try-catch is for non-QueryExceptions, like if a table is missing entirely from the JSON data
            // User
            foreach ($backup_data->users as $user) {
                try {
                    $new_user = User::create(array("username" => $user->username, "name" => $user->name, "email" => $user->email, "password" => $user->password, "organization" => $user->organization, "language" => $user->language, "regtoken" => $user->regtoken));
                    $new_user->id = $user->id;
                    $new_user->admin = $user->admin;
                    $new_user->active = $user->active;
                    $new_user->remember_token = $user->remember_token;
                    $new_user->created_at = $user->created_at;
                    $new_user->updated_at = $user->updated_at;
                    $new_user->locked_out = true;
                    $new_user->save();
                } catch (\Exception $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }
            // Project
            foreach ($backup_data->projects as $project) {
                //$new_project = Project::create(array("name" => $project->name, "slug" => $project->slug, "description" => $project->description, "adminGID" => $project->adminGID, "active" => $project->active));
                try {
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
                } catch (QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // Form
            foreach ($backup_data->forms as $form) {
                try {
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
                } catch (QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }

            }

            // Field
            foreach ($backup_data->fields as $field) {
                try {
                    $new_field = Field::create(array("pid" => $field->pid, "fid" => $field->fid, "order" => $field->order, "type" => $field->type, "name" => $field->name, "slug" => $field->slug, "desc" => $field->desc, "required" => $field->required, "default" => $field->default, "options" => $field->options));
                    $new_field->flid = $field->flid;
                    $new_field->default = $field->default;
                    $new_field->options = $field->options;
                    $new_field->created_at = $field->created_at;
                    $new_field->updated_at = $field->updated_at;
                    $new_field->save();
                } catch (QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // FormGroup
            foreach ($backup_data->formgroups as $formgroup) {
                try {
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
                    foreach ($formgroup->user_data as $user_id) {
                        $new_formgroup->users()->attach($user_id);
                    }
                } catch (QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // ProjectGroup
            foreach ($backup_data->projectgroups as $projectgroup) {
                try {
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
                    foreach ($projectgroup->user_data as $user_id) {
                        $new_projectgroup->users()->attach($user_id);
                    }
                } catch (QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // Record
            foreach ($backup_data->records as $record) {
                try {
                    $new_record = new Record(array("pid" => $record->pid, "fid" => $record->fid, "owner" => $record->owner, "kid" => $record->kid));
                    $new_record->rid = $record->rid;
                    $new_record->created_at = $record->created_at;
                    $new_record->updated_at = $record->updated_at;
                    $new_record->save();
                } catch (QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // TextField
            foreach ($backup_data->textfields as $textfield) {
                try {
                    $new_textfield = new TextField(array("rid" => $textfield->rid, "flid" => $textfield->flid, "text" => $textfield->text));
                    $new_textfield->id = $textfield->id;
                    $new_textfield->created_at = $textfield->created_at;
                    $new_textfield->updated_at = $textfield->updated_at;
                    $new_textfield->save();
                } catch (QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // RichTextField
            foreach ($backup_data->richtextfields as $richtextfield) {
                try {
                    $new_richtextfield = new RichTextField(array("rid" => $richtextfield->rid, "flid" => $richtextfield->flid, "rawtext" => $richtextfield->rawtext));
                    $new_richtextfield->id = $richtextfield->id;
                    $new_richtextfield->created_at = $richtextfield->created_at;
                    $new_richtextfield->updated_at = $richtextfield->updated_at;
                    $new_richtextfield->save();
                } catch (QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // NumberField
            foreach ($backup_data->numberfields as $numberfield) {
                try {
                    $new_numberfield = new NumberField(array("rid" => $numberfield->rid, "flid" => $numberfield->flid, "number" => $numberfield->number));
                    $new_numberfield->id = $numberfield->id;
                    $new_numberfield->created_at = $numberfield->created_at;
                    $new_numberfield->updated_at = $numberfield->updated_at;
                    $new_numberfield->save();
                } catch (QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // ListField
            foreach ($backup_data->listfields as $listfield) {
                try {
                    $new_listfield = new ListField(array("rid" => $listfield->rid, "flid" => $listfield->flid, "option" => $listfield->option));
                    $new_listfield->id = $listfield->id;
                    $new_listfield->created_at = $listfield->created_at;
                    $new_listfield->updated_at = $listfield->updated_at;
                    $new_listfield->save();
                } catch (QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // GeneratedListField
            foreach ($backup_data->generatedlistfields as $generatedlistfield) {
                try {
                    $new_generatedlistfield = new GeneratedListField(array("rid" => $generatedlistfield->rid, "flid" => $generatedlistfield->flid, "options" => $generatedlistfield->options));
                    $new_generatedlistfield->id = $generatedlistfield->id;
                    $new_generatedlistfield->created_at = $generatedlistfield->created_at;
                    $new_generatedlistfield->updated_at = $generatedlistfield->updated_at;
                    $new_generatedlistfield->save();
                } catch (QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // MultiSelectListField
            foreach ($backup_data->multiselectlistfields as $multiselectlistfield) {
                try {
                    $new_multiselectlistfield = new MultiSelectListField(array("rid" => $multiselectlistfield->rid, "flid" => $multiselectlistfield->flid, "options" => $multiselectlistfield->options));
                    $new_multiselectlistfield->id = $multiselectlistfield->id;
                    $new_multiselectlistfield->created_at = $multiselectlistfield->created_at;
                    $new_multiselectlistfield->updated_at = $multiselectlistfield->updated_at;
                    $new_multiselectlistfield->save();
                } catch (QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // DateField
            foreach ($backup_data->datefields as $datefield) {
                try {
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
                } catch (QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // ScheduleField
            foreach ($backup_data->schedulefields as $schedulefield) {
                try {
                    $new_schedulefield = new ScheduleField(array("rid" => $schedulefield->rid, "flid" => $schedulefield->flid, "events" => $schedulefield->events));
                    $new_schedulefield->id = $schedulefield->id;
                    $new_schedulefield->created_at = $schedulefield->created_at;
                    $new_schedulefield->updated_at = $schedulefield->updated_at;
                    $new_schedulefield->save();
                } catch (QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // GeolocatorField
            foreach($backup_data->geolocatorfields as $geolocatorfield){
                try{
                    $new_geolocatorfield = new GeolocatorField(array("rid"=>$geolocatorfield->rid,"flid"=>$geolocatorfield->flid,"locations"=>$geolocatorfield->locations));
                    $new_geolocatorfield->id = $geolocatorfield->id;
                    $new_geolocatorfield->created_at = $geolocatorfield->created_at;
                    $new_geolocatorfield->updated_at = $new_geolocatorfield->updated_at;
                    $new_geolocatorfield->save();
                }catch(QueryException $e){
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // Token
            foreach ($backup_data->tokens as $token) {
                try {
                    $new_token = new Token(array('token' => $token->token, 'type' => $token->type));
                    $new_token->id = $token->id;
                    $new_token->created_at = $token->created_at;
                    $new_token->updated_at = $token->updated_at;
                    $new_token->save();
                } catch (QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // Metadata
            foreach ($backup_data->metadatas as $metadata) {
                try {
                    $new_metadata = new Metadata(array());
                    $new_metadata->flid = $metadata->flid;
                    $new_metadata->pid = $metadata->pid;
                    $new_metadata->fid = $metadata->fid;
                    $new_metadata->name = $metadata->name;
                    $new_metadata->created_at = $metadata->created_at;
                    $new_metadata->updated_at = $metadata->updated_at;
                    $new_metadata->save();
                } catch (QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }
            // Revision
            foreach ($backup_data->revisions as $revision) {
                try {
                    $new_revision = new Revision(array('id' => $revision->id, 'fid' => $revision->fid, 'rid' => $revision->rid, 'userId' => $revision->userId, 'type' => $revision->type, 'data' => $revision->data, 'oldData' => $revision->oldData, 'rollback' => $revision->rollback));
                    $new_revision->created_at = $revision->created_at;
                    $new_revision->updated_at = $revision->updated_at;
                    $new_revision->save();
                } catch (QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }
        }
		catch(\Exception $e){
                $this->ajax_error_list->push($e->getMessage());
                $this->ajaxResponse(false,"An unknown error prevented the restore from completing.
                You can try restoring from a different backup file or restore point.
                Users will stay locked out until you run a successful restore or manually unlock them above.
                For this error, it's not recommended that you unlock users unless you have resolved the problem");
        }

        if(count($this->ajax_error_list) != 0){
            $this->ajaxResponse(false,"Not all of your data was restored, check the errors below for details.
            The errors are in order that they occurred, if you can resolve the first error, it will often correct
            one or more of the errors below it.
            Users will stay locked out until you run a successful restore or manually unlock them above.");
        }
        else{
            $this->unlockUsers();
            $this->ajaxResponse(true,"The restore completed successfully.");
        }

	}
    /*
     * This method accepts a boolean (status) and a string (message)
     * and it returns a JSON response for AJAX calls with the status, message,
     * and an array of restore errors.  It also sets the HTTP status code.
     *
     * Note that this sends the response immediately, and will exit()!
     *
     * @params String $status, String, $message
     * @return response
     */
    public function ajaxResponse($status,$message){

        $this->ajax_return_data = new Collection(); //This will get a status boolean, a message, and an array of errors
        $this->ajax_return_data->put("status",$status);
        $this->ajax_return_data->put("error_list",$this->ajax_error_list);
        $this->ajax_return_data->put("message",$message);



        if($status == true){
            return response()->json($this->ajax_return_data,200)->send();
        }
        else{
            //This is bad, but otherwise it keeps running, maybe there's an alternative?
            return response()->json($this->ajax_return_data,500)->send() && exit();
        }

    }

    /*
     * This method takes a collection of user IDs as keys, and their username as value
     * It will lock any user that is not exempted, so that they cannot access the app during
     * backup and restore operations.  They should be unlocked afterwards.
     *
     * The default is [1,1]
     *
     * @params Collection $exemptions
     * @return
     */
    public function lockUsers(Collection $exemptions){
        $users = User::all();
        foreach($users as $user){
            if($exemptions->has($user->id)){
                continue;
            }
            else{
                $user->locked_out = true;
                $user->save();
            }
        }
    }

    /*
     * This method will unlock all users, it returns a response with a message and status code,
     * but the response isn't sent (unless this is called from a route).
     *
     * @params
     * @return response
     */
    public function unlockUsers(){

        try {
            $users = User::all();
            foreach ($users as $user) {
                $user->locked_out = false;
                $user->save();
            }
        }
        catch(\Exception $e){
            return response("error",500);
        }
        return response("success",200);
    }
}



