<?php namespace App\Http\Controllers;

use App\ComboListField;
use App\Commands\SaveComboListFieldsTable;
use App\Commands\SaveDateFieldsTable;
use App\Commands\SaveDocumentsFieldsTable;
use App\Commands\SaveFieldsTable;
use App\Commands\SaveFormsTable;
use App\Commands\SaveGalleryFieldsTable;
use App\Commands\SaveGeneratedListFieldsTable;
use App\Commands\SaveGeolocatorFieldsTable;
use App\Commands\SaveListFieldTable;
use App\Commands\SaveMetadatasTable;
use App\Commands\SaveModelFieldsTable;
use App\Commands\SaveMultiSelectListFieldsTable;
use App\Commands\SaveNumberFieldsTable;
use App\Commands\SaveOptionPresetsTable;
use App\Commands\SavePlaylistFieldsTable;
use App\Commands\SaveProjectGroupsTable;
use App\Commands\SaveProjectsTable;
use App\Commands\SaveRecordsTable;
use App\Commands\SaveRevisionsTable;
use App\Commands\SaveRichTextFields;
use App\Commands\SaveScheduleFieldsTable;
use App\Commands\SaveTextFieldsTable;
use App\Commands\SaveTokensTable;
use App\Commands\SaveUsersTable;
use App\Commands\SaveVideoFieldsTable;
use App\DateField;
use App\DocumentsField;
use App\Field;
use App\Form;
use App\FormGroup;
use App\GalleryField;
use App\GeneratedListField;
use App\GeolocatorField;
use App\ListField;
use App\Metadata;
use App\ModelField;
use App\MultiSelectListField;
use App\NumberField;
use App\PlaylistField;
use App\Project;
use App\ProjectGroup;
use App\Record;
use App\OptionPreset;
use App\Revision;
use App\RichTextField;
use App\ScheduleField;
use App\TextField;
use App\Token;
use App\User;
use App\Http\Controllers\OptionPresetController;
use Illuminate\Support\Facades\Log;
use App\VideoField;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use \Illuminate\Support\Facades\App;
Use \Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
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

    private $BACKUP_DIRECTORY = "backups"; //Set the backup directory relative to laravel/storage/app
    private $UPLOAD_DIRECTORY = "backups/user_upload/"; //Set the upload directory relative to laravel/storage/app
    private $MEDIA_DIRECTORY =  "files"; //Set the storage directory for media field types relative to laravel/storage/app


    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['projectIndex','saveProject']]);
        $this->middleware('active');
        $this->middleware('admin');
        if(Auth::check()){
            if(Auth::user()->id != 1){
                flash()->overlay(trans('controller_backup.admin'),trans('controller_backup.whoops'));
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
        try {
            $user_support = DB::table('backup_support')->where('user_id', Auth::user()->id)->where('view', 'backups.index')->first();
            if ($user_support === null) {
                $user_support = DB::table('backup_support')->insert(['user_id' => Auth::user()->id, 'view' => 'backups.index', 'hasRun' => Carbon::now(), 'accessed' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            }
            else {
                if((Carbon::createFromFormat('Y#m#d G#i#s', ($user_support->updated_at))->diffInMinutes(Carbon::now()) < 2)){
                    if($user_support->accessed>0)
                        DB::table('backup_support')->where('id', $user_support->id)->update(['accessed' => $user_support->accessed - 1, 'updated_at' => Carbon::now()]);
                }
                elseif ((Carbon::createFromFormat('Y#m#d G#i#s', ($user_support->hasRun))->diffInMinutes(Carbon::now()) > 30) && ($user_support->accessed % 10 == 0 && $user_support->accessed != 0)) {
                    DB::table('backup_support')->where('id', $user_support->id)->update(['hasRun' => Carbon::now(), 'accessed' => 0, 'updated_at' => Carbon::now()]);
                    $request->session()->flash('user_backup_support',true);
                } else {
                    DB::table('backup_support')->where('id', $user_support->id)->update(['accessed' => $user_support->accessed + 1, 'updated_at' => Carbon::now()]);
                    //dd(['support'=>$user_support,'date'=>Carbon::createFromFormat('Y#m#d G#i#s',($user_support->hasRun))->diffInMinutes(Carbon::now())]);
                }
            }
        }
        catch(\Exception $e){
            $user_support = null;
        }

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
        $type  = "system";
        return view('backups.backup',compact('backup_label','type'));
    }


    /*
     * This method should be called via AJAX and will create a backup,
     * then return success or error information through JSON.
     *
     * @params Request $request
     * @return response
     */
	public function create0(Request $request){

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

        $this->copyMediaFiles($this->MEDIA_DIRECTORY,$this->BACKUP_DIRECTORY."/files/".$this->backup_filename,addcslashes($this->BACKUP_DIRECTORY . "/files/" . $this->backup_filename, "/"));

        $this->backup_filepath = $this->BACKUP_DIRECTORY."/".$this->backup_filename;

        $backup_files_list = new Collection();

		if(Storage::exists($this->backup_filepath)){
            $this->unlockUsers();
            $this->ajaxResponse(false,trans('controller_backup.fileexists'));
		}
		else{
            try {
                Storage::put($this->backup_filepath, $this->backup_data);
            }
            catch(\Exception $e){
                $this->ajax_error_list->push($e->getMessage());
                $this->ajaxResponse(false,trans('controller_backup.cantsave'));
            }
		}
        $this->unlockUsers();
        $request->session()->put("backup_file_name",$this->backup_filename);
        if($this->ajax_error_list->count() >0){
            //$request->session()->put("backup_file_name",$this->backup_filename);
            $this->ajaxResponse(false,trans('controller_backup.errors'));
        }
        else{
            //$request->session()->put("backup_file_name",$this->backup_filename);
            $this->ajaxResponse(true,trans('controller_backup.complete'));
        }
	}

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
        $this->backup_filename = Carbon::now()->format("Y-m-d_H:i:s");
        $this->backup_filepath = $this->BACKUP_DIRECTORY."/".$this->backup_filename;
        //Get an instance of Flysystem disk, to use Amazon AWS, SFTP, or Dropbox, change this!
        $this->backup_fs = Storage::disk('local');
        $this->backup_disk = "local";
        //
        $this->backup_fs->makeDirectory($this->backup_filepath);
        $this->saveDatabase2();

    }

    public function saveDatabase2(){
        ini_set('max_execution_time',0);
        Log::info("Backup fp: ".$this->backup_filepath);
        $this->backup_id = DB::table('backup_overall_progress')->insertGetId(['progress'=>0,'overall'=>0,'start'=>Carbon::now(),'created_at'=>Carbon::now(),'updated_at'=>Carbon::now()]);
        $jobs = [new SaveFormsTable($this->backup_disk, $this->backup_filepath, $this->backup_id ),
            new SaveProjectsTable($this->backup_disk, $this->backup_filepath, $this->backup_id),
            new SaveRecordsTable($this->backup_disk, $this->backup_filepath, $this->backup_id ),
            new SaveTextFieldsTable($this->backup_disk, $this->backup_filepath, $this->backup_id ),
            new SaveComboListFieldsTable($this->backup_disk, $this->backup_filepath, $this->backup_id),
            new SaveDateFieldsTable($this->backup_disk, $this->backup_filepath, $this->backup_id),
            new SaveFieldsTable($this->backup_disk, $this->backup_filepath, $this->backup_id),
            new SaveGeneratedListFieldsTable($this->backup_disk, $this->backup_filepath, $this->backup_id),
            new SaveGeolocatorFieldsTable($this->backup_disk, $this->backup_filepath, $this->backup_id),
            new SaveListFieldTable($this->backup_disk, $this->backup_filepath, $this->backup_id),
            new SaveMetadatasTable($this->backup_disk, $this->backup_filepath, $this->backup_id),
            new SaveMultiSelectListFieldsTable($this->backup_disk, $this->backup_filepath, $this->backup_id),
            new SaveNumberFieldsTable($this->backup_disk, $this->backup_filepath, $this->backup_id),
            new SaveOptionPresetsTable($this->backup_disk, $this->backup_filepath, $this->backup_id),
            new SaveProjectGroupsTable($this->backup_disk, $this->backup_filepath, $this->backup_id),
            new SaveRevisionsTable($this->backup_disk, $this->backup_filepath, $this->backup_id),
            new SaveRichTextFields($this->backup_disk, $this->backup_filepath, $this->backup_id),
            new SaveScheduleFieldsTable($this->backup_disk, $this->backup_filepath, $this->backup_id),
            new SaveTokensTable($this->backup_disk, $this->backup_filepath, $this->backup_id),
            new SaveUsersTable($this->backup_disk, $this->backup_filepath, $this->backup_id)];

        foreach($jobs as $job){
            Queue::push($job);
        }

        //
        // These are not implemented yet, we need to decide how we are handling file backups first...
        //
//        Queue::push(new SaveDocumentsFieldsTable($this->backup_disk, $this->backup_filepath, $this->backup_id));
//        Queue::push(new SavePlaylistFieldsTable($this->backup_disk, $this->backup_filepath, $this->backup_id));
//        Queue::push(new SaveVideoFieldsTable($this->backup_disk, $this->backup_filepath, $this->backup_id));
//        Queue::push(new SaveGalleryFieldsTable($this->backup_disk, $this->backup_filepath, $this->backup_id));
//        Queue::push(new SaveModelFieldsTable($this->backup_disk, $this->backup_filepath, $this->backup_id));
    }

    public function checkProgress(){
        $overall = DB::table('backup_overall_progress')->where('created_at',DB::table('backup_overall_progress')->max('created_at'))->first();
        $partial = DB::table('backup_partial_progress')->where('backup_id',$overall->id)->get();
        return response()->json(["overall"=>$overall,"partial"=>$partial],200);
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
            flash()->overlay(trans('controller_backup.nofiletemp'),trans('controller_backup.whoops'));
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
        $backup_info->put("filename",$this->backup_filename);
        $backup_info->put("name",$backup_name); //A user-assigned name for the backup
        $backup_info->put("created_by",Auth::user()->email); //The email for the user that created it
        $backup_info->put("type","system_backup");

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
            TextField::chunk(1000,function ($tf_chunk) use ($all_textfields_data){
                foreach ($tf_chunk as $textfield) {
                    try {
                        Log::info("Textfield processed: ".$textfield->id);
                        Log::info("Memory usage: ".memory_get_usage());
                        Log::info("Memory peak usage: ".memory_get_peak_usage());
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
            });


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

            // DocumentsField
            $all_documentsfield_data = new Collection();
            $entire_database->put('documentsfield',$all_documentsfield_data);
            foreach(DocumentsField::all() as $documentsfield){
                try{
                    $individual_documentsfield_data = new Collection();
                    $individual_documentsfield_data->put("id",$documentsfield->id);
                    $individual_documentsfield_data->put("rid",$documentsfield->rid);
                    $individual_documentsfield_data->put("flid",$documentsfield->flid);
                    $individual_documentsfield_data->put("documents",$documentsfield->documents);
                    $individual_documentsfield_data->put("created_at", $documentsfield->created_at->toDateTimeString());
                    $individual_documentsfield_data->put("updated_at", $documentsfield->updated_at->toDateTimeString());
                    $all_documentsfield_data->push($individual_documentsfield_data);
                } catch(\Exception $e){
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // PlaylistField
            $all_playlistfield_data = new Collection();
            $entire_database->put('playlistfield',$all_playlistfield_data);
            foreach(PlaylistField::all() as $playlistfield){
                try{
                    $individual_playlistfield_data = new Collection();
                    $individual_playlistfield_data->put("id",$playlistfield->id);
                    $individual_playlistfield_data->put("rid",$playlistfield->rid);
                    $individual_playlistfield_data->put("flid",$playlistfield->flid);
                    $individual_playlistfield_data->put("audio",$playlistfield->audio);
                    $individual_playlistfield_data->put("created_at", $playlistfield->created_at->toDateTimeString());
                    $individual_playlistfield_data->put("updated_at", $playlistfield->updated_at->toDateTimeString());
                    $all_playlistfield_data->push($individual_playlistfield_data);
                } catch(\Exception $e){
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // VideoField
            $all_videofield_data = new Collection();
            $entire_database->put('videofield',$all_videofield_data);
            foreach(VideoField::all() as $videofield){
                try{
                    $individual_videofield_data = new Collection();
                    $individual_videofield_data->put("id",$videofield->id);
                    $individual_videofield_data->put("rid",$videofield->rid);
                    $individual_videofield_data->put("flid",$videofield->flid);
                    $individual_videofield_data->put("video",$videofield->video);
                    $individual_videofield_data->put("created_at", $videofield->created_at->toDateTimeString());
                    $individual_videofield_data->put("updated_at", $videofield->updated_at->toDateTimeString());
                    $all_videofield_data->push($individual_videofield_data);
                } catch(\Exception $e){
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // Model Field
            $all_modelfield_data = new Collection();
            $entire_database->put('modelfield',$all_modelfield_data);
            foreach(ModelField::all() as $modelfield){
                try{
                    $individual_modelfield_data = new Collection();
                    $individual_modelfield_data->put("id",$modelfield->id);
                    $individual_modelfield_data->put("rid",$modelfield->rid);
                    $individual_modelfield_data->put("flid",$modelfield->flid);
                    $individual_modelfield_data->put("model",$modelfield->model);
                    $individual_modelfield_data->put("created_at", $modelfield->created_at->toDateTimeString());
                    $individual_modelfield_data->put("updated_at", $modelfield->updated_at->toDateTimeString());
                    $all_modelfield_data->push($individual_modelfield_data);
                } catch(\Exception $e){
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // Gallery Field
            $all_galleryfield_data = new Collection();
            $entire_database->put('galleryfield',$all_galleryfield_data);
            foreach(GalleryField::all() as $galleryfield){
                try{
                    $individual_galleryfield_data = new Collection();
                    $individual_galleryfield_data->put("id",$galleryfield->id);
                    $individual_galleryfield_data->put("rid",$galleryfield->rid);
                    $individual_galleryfield_data->put("flid",$galleryfield->flid);
                    $individual_galleryfield_data->put("images",$galleryfield->images);
                    $individual_galleryfield_data->put("created_at", $galleryfield->created_at->toDateTimeString());
                    $individual_galleryfield_data->put("updated_at", $galleryfield->updated_at->toDateTimeString());
                    $all_galleryfield_data->push($individual_galleryfield_data);
                } catch(\Exception $e){
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // ComboList Field
            $all_combolistfield_data = new Collection();
            $entire_database->put('combolistfield',$all_combolistfield_data);
            foreach(ComboListField::all() as $combolistfield){
                try{
                    $individual_combolistfield_data = new Collection();
                    $individual_combolistfield_data->put('id',$combolistfield->id);
                    $individual_combolistfield_data->put('rid',$combolistfield->rid);
                    $individual_combolistfield_data->put('flid',$combolistfield->flid);
                    $individual_combolistfield_data->put('options',$combolistfield->options);
                    $individual_combolistfield_data->put('ftype1',$combolistfield->ftype1);
                    $individual_combolistfield_data->put('ftype2',$combolistfield->ftype2);
                    $individual_combolistfield_data->put("created_at", $combolistfield->created_at->toDateTimeString());
                    $individual_combolistfield_data->put("updated_at", $combolistfield->updated_at->toDateTimeString());
                    $all_combolistfield_data->push($individual_combolistfield_data);

                }catch(\Exception $e){
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // Tokens Field
            $all_tokens_data = new Collection();
            $entire_database->put("tokens", $all_tokens_data);
            foreach (Token::all() as $token) {
                try {
                    $individual_token_data = new Collection();
                    $token_data = new Collection();
                    $token_data->put("id", $token->id);
                    $token_data->put("type", $token->type);
                    $token_data->put("token", $token->token);
                    $token_data->put("created_at", $token->created_at->toDateTimeString());
                    $token_data->put("updated_at", $token->updated_at->toDateTimeString());
                    $individual_token_data->put("token_data",$token_data);
                    $individual_token_data->put("project_data",$token->projects()->get()->modelKeys());
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

            // Option Presets
            $all_optionpresets_data = new Collection();
            foreach(OptionPreset::all() as $optionpreset){
                try {
                    $individual_optionpresets_data = new Collection();
                    $individual_optionpresets_data->put("id", $optionpreset->id);
                    $individual_optionpresets_data->put("pid", $optionpreset->pid);
                    $individual_optionpresets_data->put("type", $optionpreset->type);
                    $individual_optionpresets_data->put("name", $optionpreset->name);
                    $individual_optionpresets_data->put("preset", $optionpreset->preset);
                    $individual_optionpresets_data->put("shared", $optionpreset->shared);
                    $individual_optionpresets_data->put("created_at", $optionpreset->created_at->toDateTimeString());
                    $individual_optionpresets_data->put("updated_at", $optionpreset->updated_at->toDateTimeString());

                    $all_optionpresets_data->push($individual_optionpresets_data);
                }
                catch(\Exception $e){
                    $this->ajax_error_list->push($e->getMessage());
                }
            }
            $entire_database->put("option_presets",$all_optionpresets_data);


        }
        catch(\Exception $e){
            $this->ajax_error_list->push($e->getMessage());
            $this->ajaxResponse(false,trans('controller_backup.correct'));
        }
        Log::info("DATABASE DUMPED!!");
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

        $type = "system";
        if($request->input("backup_source") == "server"){
            $available_backups = $request->session()->get("restore_points_available"); //Same array as previous page (in case file was deleted and indices changed)
            try {
                $filename = $available_backups[$request->input("restore_point")]; //Using index in array so user can't provide weird or malicious file names
            }
            catch(\Exception $e){
                flash()->overlay(trans('controller_backup.badrestore'),trans('controller_backup.whoops')); //This can happen if another user deleted the backup or if the params were edited before POST
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
                        flash()->overlay(trans('controller_backup.cantmove'),trans('controller_backup.whoops'));
                        return redirect()->back();
                    }
                    $request->session()->put("restore_file_path",$filename);
                }
                else{
                    flash()->overlay(trans('controller_backup.badfile'),trans('controller_backup.whoops'));
                    return redirect()->back();
                }
            }
            else{
                flash()->overlay(trans('controller_backup.nofiles'),trans('controller_backup.whoops'));
                return redirect()->back();
            }
        }
        else{
            return redirect()->back();
        }


        return view('backups.restore',compact('type'));
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
     *
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
            return $this->ajaxResponse(false,trans('controller_backup.noselect'));
        }

		try{
			$this->json_file = Storage::get($filepath);
		}
		catch(\Exception $e){
            $this->ajaxResponse(false,trans('controller_backup.noopen'));
		}
        try {
            $this->decoded_json = json_decode($this->json_file);
            $this->decoded_json->kora3;
            if($this->decoded_json->kora3->type != "system_backup"){
                $this->ajaxResponse(false,"The backup type is not valid, you are trying to restore a non-system backup.");
            }
        }
        catch(\Exception $e){
            $this->ajaxResponse(false,trans('controller_backup.badjson'));
        }
        try{
            $media_file_location = $this->decoded_json->kora3->filename;
            Storage::get($this->BACKUP_DIRECTORY."/files/".$media_file_location."/files");
        }
        catch(\Exception $e){
            $this->ajaxResponse(false,trans('controller_backup.reqmedia')."$this->BACKUP_DIRECTORY/$media_file_location/files.".trans('controller_backup.placefiles'));
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
            foreach(DocumentsField::all() as $DocumentsField){
                $DocumentsField->delete();
            }
            foreach(ModelField::all() as $ModelField){
                $ModelField->delete();
            }
            foreach(GalleryField::all() as $GalleryField){
                $GalleryField->delete();
            }
            foreach(VideoField::all() as $VideoField){
                $VideoField->delete();
            }
            foreach(PlaylistField::all() as $PlaylistField){
                $PlaylistField->delete();
            }
            foreach(ComboListField::all() as $ComboListField){
                $ComboListField->delete();
            }


        }catch(\Exception $e){
            $this->ajaxResponse(false, trans('controller_backup.dbpermission'));
        }
        try{
            //$this->copyMediaFiles(ENV('BASE_PATH').'storage/app/backups/files/'.$backup_data->kora3->filename,ENV('BASE_PATH')."storage/app/".$this->MEDIA_DIRECTORY);
            //$this->deleteMediaFiles(ENV('BASE_PATH').'storage/app/backups/files/'.$backup_data->kora3->filename);
            $this->deleteMediaFiles($this->MEDIA_DIRECTORY);

        }catch(\Exception $e){
            $this->ajax_error_list->push($e->getMessage());
            $this->ajaxResponse(false,trans('controller_backup.filepermission'));
        }

        $this->backup_media_files_path = ENV('BASE_PATH') . 'storage/app/' . $this->BACKUP_DIRECTORY . "/files/" . $backup_data->kora3->filename . "/files";
        $this->backup_filename = $backup_data->kora3->filename; //Don't rename Kora3 backups
        $this->backup_file_list = new Collection();
        try {

            //Prepare a list
            $this->verifyBackedUpMediaFiles($this->backup_media_files_path, $this->backup_file_list);
        }
        catch(\Exception $e){
            $this->ajax_error_list->push($e->getMessage());
            $this->ajaxResponse(false,trans('controller_backup.mediaproblem'));
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
                } catch(QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // ComboListField
            foreach($backup_data->combolistfield as $combolistfield){
                try{
                    $new_combolistfield = new ComboListField(array("rid"=>$combolistfield->rid,"flid"=>$combolistfield->flid,"options"=>$combolistfield->options,"ftype1"=>$combolistfield->ftype1,"ftype2"=>$combolistfield->ftype2));
                    $new_combolistfield->id = $combolistfield->id;
                    $new_combolistfield->created_at = $combolistfield->created_at;
                    $new_combolistfield->updated_at = $combolistfield->updated_at;
                    $new_combolistfield->save();
                }
                catch(QueryException $e){
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // GeolocatorField
            foreach($backup_data->geolocatorfields as $geolocatorfield){
                try{
                    $new_geolocatorfield = new GeolocatorField(array("rid"=>$geolocatorfield->rid,"flid"=>$geolocatorfield->flid,"locations"=>$geolocatorfield->locations));
                    $new_geolocatorfield->id = $geolocatorfield->id;
                    $new_geolocatorfield->created_at = $geolocatorfield->created_at;
                    $new_geolocatorfield->updated_at = $geolocatorfield->updated_at;
                    $new_geolocatorfield->save();
                }catch(QueryException $e){
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // DocumentsField (other file/media fields have slightly different names but are the same
            foreach($backup_data->documentsfield as $documentsfield){
                $files_db_row = $documentsfield->documents;  //This is the database row with filenames/info
                $df_filenames = $this->getRecordFileNames($documentsfield->documents); //get the file names only from the row
                $files_present = $this->verifyMediaFilesExist($documentsfield->rid, $documentsfield->flid, $df_filenames); //check that the files exist at the expected location

                //If there are less files than there should be, remove them from the database row before restoring it
                if($files_present->count() < $df_filenames->count()){
                    $files_db_row = $this->removeFilesFromDbRow($files_db_row,$files_present);
                    $this->ajax_error_list->push(trans('controller_backup.record').$documentsfield->rid.trans('controller_backup.partrestored'));
                }

                //Only create a databse row if at least SOME files were restored, but not if none
                if ($files_present->count() > 0) {
                    try {
                        $new_documentsfield = new DocumentsField(array("rid" => $documentsfield->rid, "flid" => $documentsfield->flid, "documents" => $files_db_row));
                        $new_documentsfield->id = $documentsfield->id;
                        $new_documentsfield->created_at = $documentsfield->created_at;
                        $new_documentsfield->updated_at = $documentsfield->updated_at;
                        $new_documentsfield->save();
                    } catch (QueryException $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }
                else{
                    $this->ajax_error_list->push(trans('controller_backup.record').$documentsfield->rid.trans('controller_backup.docmissing'));
                }
            }
            // GalleryField
            foreach($backup_data->galleryfield as $galleryfield){
                $files_db_row = $galleryfield->images;
                $gf_filenames = $this->getRecordFileNames($galleryfield->images);
                $files_present = $this->verifyMediaFilesExist($galleryfield->rid, $galleryfield->flid, $gf_filenames);

                if($files_present->count() < $gf_filenames->count()){
                    $files_db_row = $this->removeFilesFromDbRow($files_db_row,$files_present);
                    $this->ajax_error_list->push(trans('controller_backup.record').$galleryfield->rid.trans('controller_backup.partrestored'));
                }

                if ($files_present->count() > 0) {

                    try {
                        $new_galleryfield = new GalleryField(array("rid" => $galleryfield->rid, "flid" => $galleryfield->flid, "images" => $files_db_row));
                        $new_galleryfield->id = $galleryfield->id;
                        $new_galleryfield->created_at = $galleryfield->created_at;
                        $new_galleryfield->updated_at = $galleryfield->updated_at;
                        $new_galleryfield->save();
                    } catch (QueryException $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }
                else{
                    $this->ajax_error_list->push(trans('controller_backup.record').$galleryfield->rid.trans('controller_backup.galmissing'));
                }
            }
            // ModelField
            foreach($backup_data->modelfield as $modelfield){
                $files_db_row = $modelfield->model;
                $mf_filenames = $this->getRecordFileNames($modelfield->model);
                $files_present = $this->verifyMediaFilesExist($modelfield->rid, $modelfield->flid, $mf_filenames);

                if($files_present->count() < $mf_filenames->count()){
                    $files_db_row = $this->removeFilesFromDbRow($files_db_row,$files_present);
                    $this->ajax_error_list->push(trans('controller_backup.record').$modelfield->rid.trans('controller_backup.partrestored'));
                }


                if ($files_present->count() > 0) {
                    try {
                        $new_modelfield = new ModelField(array("rid" => $modelfield->rid, "flid" => $modelfield->flid, "model" => $files_db_row));
                        $new_modelfield->id = $modelfield->id;
                        $new_modelfield->created_at = $modelfield->created_at;
                        $new_modelfield->updated_at = $modelfield->updated_at;
                        $new_modelfield->save();
                    } catch (QueryException $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }
                else{
                    $this->ajax_error_list->push(trans('controller_backup.record').$modelfield->rid.trans('controller_backup.modmissing'));
                }
            }
            // PlaylistField
            foreach($backup_data->playlistfield as $playlistfield){
                $files_db_row = $playlistfield->audio;
                $pf_filenames = $this->getRecordFileNames($playlistfield->audio);
                $files_present = $this->verifyMediaFilesExist($playlistfield->rid,$playlistfield->flid,$pf_filenames);

                if($files_present->count() < $pf_filenames->count()){
                    $files_db_row = $this->removeFilesFromDbRow($files_db_row,$files_present);
                    $this->ajax_error_list->push(trans('controller_backup.admin').$playlistfield->rid.trans('controller_backup.partrestored'));
                }

                if ($files_present->count() > 0) {
                    try {
                        $new_playlistfield = new PlaylistField(array("rid" => $playlistfield->rid, "flid" => $playlistfield->flid, "audio" => $playlistfield->audio));
                        $new_playlistfield->id = $playlistfield->id;
                        $new_playlistfield->created_at = $playlistfield->created_at;
                        $new_playlistfield->updated_at = $playlistfield->updated_at;
                        $new_playlistfield->save();
                    } catch (QueryException $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }
                else{
                    $this->ajax_error_list->push(trans('controller_backup.record').$playlistfield->rid.trans('controller_backup.notres'));
                }
            }
            // VideoField
            foreach($backup_data->videofield as $videofield){
                $files_db_row = $videofield->video;
                $vf_filenames = $this->getRecordFileNames($videofield->video);
                $files_present = $this->verifyMediaFilesExist($videofield->rid,$videofield->flid,$vf_filenames);

                if($files_present->count() < $vf_filenames->count()){
                    $files_db_row = $this->removeFilesFromDbRow($files_db_row,$files_present);
                    $this->ajax_error_list->push(trans('controller_backup.record').$videofield->rid.trans('controller_backup.partrestore'));
                }

                if ($files_present->count() > 0) {
                    try {
                        $new_videofield = new VideoField(array("rid" => $videofield->rid, "flid" => $videofield->flid, "video" => $videofield->video));
                        $new_videofield->id = $videofield->id;
                        $new_videofield->created_at = $videofield->created_at;
                        $new_videofield->updated_at = $videofield->updated_at;
                        $new_videofield->save();
                    } catch (QueryException $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }
                else{
                    $this->ajax_error_list->push(trans('controller_backup.record').$videofield->rid.trans('controller_backup.notres'));
                }
            }



            // Token
            foreach ($backup_data->tokens as $token) {
                try {
                    $new_token = new Token(array('token' => $token->token_data->token, 'type' => $token->token_data->type));
                    $new_token->id = $token->token_data->id;
                    $new_token->created_at = $token->token_data->created_at;
                    $new_token->updated_at = $token->token_data->updated_at;
                    $new_token->save();
                    foreach($token->project_data as $project_id){
                        $new_token->projects()->attach($project_id);
                    }
                } catch (QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }
            // OptionPreset
            foreach($backup_data->option_presets as $option_preset){
                try{
                    $new_option_preset = new OptionPreset();
                    $new_option_preset->id = $option_preset->id;
                    $new_option_preset->pid = $option_preset->pid;
                    $new_option_preset->type = $option_preset->type;
                    $new_option_preset->name = $option_preset->name;
                    $new_option_preset->preset = $option_preset->preset;
                    $new_option_preset->shared = $option_preset->shared;
                    $new_option_preset->created_at = $option_preset->created_at;
                    $new_option_preset->updated_at = $option_preset->updated_at;
                    $new_option_preset->save();
                }
                catch(QueryException $e){
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

            $this->copyMediaFiles($this->BACKUP_DIRECTORY."/files/".$this->backup_filename,"/",addcslashes($this->BACKUP_DIRECTORY . "/files/" . $this->backup_filename, "/"));

        }
		catch(\Exception $e){
                $this->ajax_error_list->push($e->getMessage());
                $this->ajaxResponse(false,trans('controller_backup.unknown'));
        }

        if(count($this->ajax_error_list) != 0){
            $this->ajaxResponse(false,trans('controller_backup.notalldata'));
        }
        else{
            $this->unlockUsers();
            $this->ajaxResponse(true,trans('controller_backup.success'));
        }

	}
    /*
     * This method accepts a boolean (status) and a string (message)
     * and it returns a JSON response for AJAX calls with php escape stringthe status, message,
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
            return response()->json($this->ajax_return_data,200);
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

    public function copyMediaFiles($source, $dest, $prefix)
        {
            $filesList = Storage::allFiles($source);
            if(count($filesList) > 0) {
                foreach ($filesList as $fileItem) {
                    //When restoring, parts of the path (/backups/$filename/files/) need to be removed
                    //When backing up, this doesn't exist to be removed, but gets added on the front with $dest
                    //Using Storage::copy() instead of File::copyDirectory and PHP functions allows the backup
                    //destination to be changed later to Dropbox or something hopefully without needing to update this.

                    //$pathPrefix = addcslashes($this->BACKUP_DIRECTORY . "/files/" . $this->backup_filename, "/");
                    $pathPrefix = $prefix;
                    $newFilePath = preg_replace("/$pathPrefix/", "", $fileItem);
                    try {
                        Storage::copy($fileItem, $dest . '/' . $newFilePath);
                    }
                    catch(\Exception $e){
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }
            }
            else{
                Storage::makeDirectory($dest.'/files');
            }
    }

    /**
     * Delete media files at the location $source
     * @param $source
     */
    public function deleteMediaFiles($source){
        Storage::deleteDirectory($source);
    }

    /**
     *
     * Generate a list of the media files and place it into $files_list
     * @param $media_dir
     * @param $files_list
     * @return mixed
     */
    public function verifyBackedUpMediaFiles($media_dir, &$files_list){
        $mediaIterator = new \RecursiveDirectoryIterator($media_dir);
        foreach($mediaIterator as $fs_entry){
            if($mediaIterator->hasChildren()){
                //$media_list->push($fsEntry);
                $this->verifyBackedUpMediaFiles($fs_entry,$files_list);
            }
            elseif(is_file($fs_entry)){
                //$path_prefix = ENV('BASE_PATH').'storage/app/'.$this->BACKUP_DIRECTORY."/files/".$this->backup_filename;
                $something = addcslashes($this->backup_media_files_path,"/");
                $file_path = preg_replace("/".$something."\/p\d+\/f\d+\//","",$fs_entry);
                $files_list->put($file_path,$fs_entry->getFilename());
               // $files_list->push(str_replace($this->backup_media_files_path.preg_match("/\/p[0-9]\/f[0-9]\//",$fs_entry),"",$fs_entry));
            }
        }

        return $files_list;
    }

    /**
     * Verify that media files actually exist with the correct $rid and $flid in their path
     * @param $rid
     * @param $flid
     * @param $filenames
     * @return Collection
     */
    public function verifyMediaFilesExist($rid, $flid, $filenames){
        $status = true;
        $mediaFilesPresent = new Collection();
        foreach($filenames as $filename) {
            $expected_filepath = "r" . $rid . "/fl" . $flid . "/" . $filename;
            try {
                if ($this->backup_file_list->has($expected_filepath)) {
                    $mediaFilesPresent->put($filename,true);
                    continue;
                } else {
                    $this->ajax_error_list->push(trans('controller_backup.notexist') . $expected_filepath);
                    $status = false;
                }
            } catch (\Exception $e) {
                $this->ajax_error_list->push($e->getMessage());
                $status = false;
            }
        }
        return $mediaFilesPresent;
    }

    /**
     * Extract record file names from a database row for the file/media fields
     * @param $db_row
     * @return Collection
     */
    public static function getRecordFileNames($db_row){
        $file_array = explode('[!]', $db_row);
        $file_name_array = new Collection();
        foreach($file_array as $file_info)
        {
            $file_name_array->push(explode('[Name]', $file_info)[1]);
        }
        return $file_name_array;
    }

    /**
     * Remove a file from a database row of backup JSON (so that a missing file doesn't get restored)
     * @param $db_row
     * @param $files_present
     * @return string
     */
    public function removeFilesFromDbRow($db_row, $files_present){
        $file_array = explode('[!]',$db_row);
        $corrected_file_array = new Collection();
        foreach($file_array as $file_info) {
            if($files_present->has(explode('[Name]',$file_info)[1])){
                $corrected_file_array->push($file_info);
            }
        }

        return implode("[!]",$corrected_file_array->toArray());
    }

    /**
     * Delete a restore point and its files
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request){

        $this->validate($request,[
            'backup_source'=>'required|in:server',
            'restore_point'=>'required',
            'backup_type'=>'required|in:system,project',
            'project_id'=>'required_if:backup_type,project'
        ]);

        if($request->input("backup_source") == "server"){
            $available_backups = $request->session()->get("restore_points_available"); //Same array as previous page (in case file was deleted and indices changed)
            try {
                $filename = $available_backups[$request->input("restore_point")]; //Using index in array so user can't provide weird or malicious file names
                $filename = explode("/",$filename);

                try{

                    if($request->input("backup_type") == "system") {
                        if (Storage::exists($this->BACKUP_DIRECTORY . "/" . $filename[1])) {
                            Storage::delete($this->BACKUP_DIRECTORY . "/" . $filename[1]);
                        }
                        if (Storage::exists($this->BACKUP_DIRECTORY . "/files/" . $filename[1])) {
                            Storage::deleteDirectory($this->BACKUP_DIRECTORY . "/files/" . $filename[1]);
                        }
                    }
                    elseif($request->input("backup_type") == "project"){
                        if (Storage::exists($this->BACKUP_DIRECTORY . "/" . $filename[1] . "/" . $filename[2])) {
                            Storage::delete($this->BACKUP_DIRECTORY . "/" . $filename[1] . "/" . $filename[2]);
                        }
                        if (Storage::exists($this->BACKUP_DIRECTORY . "/files/" . $filename[1])) {
                            Storage::deleteDirectory($this->BACKUP_DIRECTORY . "/files/" . $filename[1] . "/" .$filename[2]);
                        }
                    }

                }
                catch(\Exception $e){
                    return response()->json(["status"=>false,"message"=>"$e->getMessage()"]);
                }

                return response()->json(["status"=>true,"message"=>$filename]);

            }
            catch(\Exception $e){
                flash()->overlay(trans('controller_backup.badrestore'),trans('controller_backup.whoops')); //This can happen if another user deleted the backup or if the params were edited before POST
                return redirect()->back();
            }
            $request->session()->put("restore_file_path",$filename);
        }
        else{
            flash()->overlay(trans('controller_backup.badrestore'),trans('controller_backup.whoops'));
            return redirect()->back();
        }


    }


    public function projectIndex($pid, Request $request){

        $project = Project::find($pid);

        if(!Auth::user()->admin ||  !Auth::user()->isProjectAdmin($project)){
            flash()->overlay("You are not allowed to see this page","Whoops.");
            return redirect()->back();
        }

        $available_backups = Storage::files($this->BACKUP_DIRECTORY."/p".$pid."/");
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

        return view('backups.project',compact('saved_backups','project'));
    }


    /**
     * Save a project and all of its dependencies
     * @param $pid
     * @return \Illuminate\Support\Collection
     */
    public function saveProject($pid,$backup_label){

        $project = Project::find($pid);

        $project_data = new Collection();

        //Some info about the backup itself
        $backup_info = new Collection();
        $backup_info->put("version","1");   //In case a breaking change happens in the future (like new table added or a table removed)
        $backup_info->put("date",Carbon::now()->toDateTimeString()); //UTC time the backup started
        $backup_info->put("filename",$this->backup_filename);
        $backup_info->put("name",$backup_label); //A user-assigned name for the backup
        $backup_info->put("created_by",Auth::user()->email); //The email for the user that created it
        $backup_info->put("type","project_backup");
        $backup_info->put("pid",$project->pid);
        $project_data->put("kora3",$backup_info);

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
            $project_data->put("project", $individual_project_data);
         } catch (\Exception $e) {
            $this->ajax_error_list->push($e->getMessage());
        }

        $project_forms_data = new Collection();
        $project_formgroup_data = new Collection();
        $project_revisions_data = new Collection();
        foreach(Form::where('pid',$pid)->get() as $form){
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
                $project_forms_data->push($individual_form_data);

                foreach($form->groups()->get() as $formgroup){
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
                        $project_formgroup_data->push($individual_formgroup_data);
                    } catch (\Exception $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }

                foreach($form->revisions()->get() as $revision){
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
                        $project_revisions_data->push($individual_revision_data);
                    }catch (\Exception $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }

            } catch (\Exception $e) {
                $this->ajax_error_list->push($e->getMessage());
            }
        }
        $project_data->put("forms",$project_forms_data);
        $project_data->put("form_groups",$project_formgroup_data);

        $project_projectgroup_data = new Collection();
        foreach(ProjectGroup::where('pid',$pid)->get() as $projectgroup){
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
                $project_projectgroup_data->push($individual_projectgroup_data);
            } catch (\Exception $e) {
                $this->ajax_error_list->push($e->getMessage());
            }
        }
        $project_data->put("project_groups",$project_projectgroup_data);

        $project_fields_data = new Collection();
        foreach(Field::where('pid',$pid)->get() as $field){
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
                $project_fields_data->push($individual_field_data);
            } catch (\Exception $e) {
                $this->ajax_error_list->push($e->getMessage());
            }
        }
        $project_data->put("fields",$project_fields_data);

        $project_optionpresets_data = new Collection();
        foreach(OptionPreset::where('pid',$pid)->get() as $optionpreset){
            $individual_optionpresets_data = new Collection();
            $individual_optionpresets_data->put("id",$optionpreset->id);
            $individual_optionpresets_data->put("pid",$optionpreset->pid);
            $individual_optionpresets_data->put("type",$optionpreset->type);
            $individual_optionpresets_data->put("name",$optionpreset->name);
            $individual_optionpresets_data->put("preset",$optionpreset->preset);
            $individual_optionpresets_data->put("shared",$optionpreset->shared);
            $individual_optionpresets_data->put("created_at", $optionpreset->created_at->toDateTimeString());
            $individual_optionpresets_data->put("updated_at", $optionpreset->updated_at->toDateTimeString());

            $project_optionpresets_data->push($individual_optionpresets_data);


        }
        $project_data->put("option_presets",$project_optionpresets_data);

        $project_metadatas_data = new Collection();
        foreach(Metadata::where('pid',$pid)->get() as $metadatas){
            try {
                $individual_metadatas_data = new Collection();
                $individual_metadatas_data->put("flid", $metadatas->flid);
                $individual_metadatas_data->put("pid", $metadatas->pid);
                $individual_metadatas_data->put("fid", $metadatas->fid);
                $individual_metadatas_data->put("name", $metadatas->name);
                $individual_metadatas_data->put("created_at", $metadatas->created_at->toDateTimeString());
                $individual_metadatas_data->put("updated_at", $metadatas->updated_at->toDateTimeString());
                $individual_metadatas_data->push($individual_metadatas_data);
            } catch (\Exception $e) {
                $this->ajax_error_list->push($e->getMessage());
            }
        }
        $project_data->put("metadatas",$project_metadatas_data);


        $project_records_data = new Collection();
        $project_textfields_data = new Collection();
        $project_richtextfields_data = new Collection();
        $project_numberfields_data = new Collection();
        $project_listfields_data = new Collection();
        $project_generatedlistfields_data = new Collection();
        $project_multiselectlistfields_data = new Collection();
        $project_datefields_data = new Collection();
        $project_schedulefields_data = new Collection();
        $project_geolocatorfields_data = new Collection();
        $project_documentsfield_data = new Collection();
        $project_playlistfield_data = new Collection();
        $project_videofield_data = new Collection();
        $project_modelfield_data = new Collection();
        $project_galleryfield_data = new Collection();
        $project_combolistfield_data = new Collection();
        foreach(Record::where('pid',$pid)->get() as $record){
            try {
                $individual_record_data = new Collection();
                $individual_record_data->put("rid", $record->rid);
                $individual_record_data->put("kid", $record->kid);
                $individual_record_data->put("pid", $record->pid);
                $individual_record_data->put("fid", $record->fid);
                $individual_record_data->put("owner", $record->owner);
                $individual_record_data->put("created_at", $record->created_at->toDateTimeString());
                $individual_record_data->put("updated_at", $record->updated_at->toDateTimeString());
                $project_records_data->push($individual_record_data);

                // TextField
                foreach ($record->textfields()->get() as $textfield) {
                    try {
                        $individual_textfield_data = new Collection();
                        $individual_textfield_data->put("id", $textfield->id);
                        $individual_textfield_data->put("rid", $textfield->rid);
                        $individual_textfield_data->put("flid", $textfield->flid);
                        $individual_textfield_data->put("text", $textfield->text);
                        $individual_textfield_data->put("created_at", $textfield->created_at->toDateTimeString());
                        $individual_textfield_data->put("updated_at", $textfield->updated_at->toDateTimeString());
                        $project_textfields_data->push($individual_textfield_data);
                    } catch (\Exception $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }

                //  RichTextField
                foreach ($record->richtextfields()->get() as $richtextfield) {
                    try {
                        $individual_richtextfield_data = new Collection();
                        $individual_richtextfield_data->put("id", $richtextfield->id);
                        $individual_richtextfield_data->put("rid", $richtextfield->rid);
                        $individual_richtextfield_data->put("flid", $richtextfield->flid);
                        $individual_richtextfield_data->put("rawtext", $richtextfield->rawtext);
                        $individual_richtextfield_data->put("created_at", $richtextfield->created_at->toDateTimeString());
                        $individual_richtextfield_data->put("updated_at", $richtextfield->updated_at->toDateTimeString());
                        $project_richtextfields_data->push($individual_richtextfield_data);
                    } catch (\Exception $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }

                //  NumberField
                foreach ($record->numberfields()->get() as $numberfield) {
                    try {
                        $individual_numberfield_data = new Collection();
                        $individual_numberfield_data->put("id", $numberfield->id);
                        $individual_numberfield_data->put("rid", $numberfield->rid);
                        $individual_numberfield_data->put("flid", $numberfield->flid);
                        $individual_numberfield_data->put("number", $numberfield->number);
                        $individual_numberfield_data->put("created_at", $numberfield->created_at->toDateTimeString());
                        $individual_numberfield_data->put("updated_at", $numberfield->updated_at->toDateTimeString());
                        $project_numberfields_data->push($individual_numberfield_data);
                    } catch (\Exception $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }

                // ListField
                foreach ($record->listfields()->get() as $listfield) {
                    try {
                        $individual_listfield_data = new Collection();
                        $individual_listfield_data->put("id", $listfield->id);
                        $individual_listfield_data->put("rid", $listfield->rid);
                        $individual_listfield_data->put("flid", $listfield->flid);
                        $individual_listfield_data->put("option", $listfield->option);
                        $individual_listfield_data->put("created_at", $listfield->created_at->toDateTimeString());
                        $individual_listfield_data->put("updated_at", $listfield->updated_at->toDateTimeString());
                        $project_listfields_data->push($individual_listfield_data);
                    } catch (\Exception $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }

                // GeneratedListField
                foreach ($record->generatedlistfields()->get() as $generatedlistfield) {
                    try {
                        $individual_generatedlistfield_data = new Collection();
                        $individual_generatedlistfield_data->put("id", $generatedlistfield->id);
                        $individual_generatedlistfield_data->put("rid", $generatedlistfield->rid);
                        $individual_generatedlistfield_data->put("flid", $generatedlistfield->flid);
                        $individual_generatedlistfield_data->put("options", $generatedlistfield->options);
                        $individual_generatedlistfield_data->put("created_at", $generatedlistfield->created_at->toDateTimeString());
                        $individual_generatedlistfield_data->put("updated_at", $generatedlistfield->updated_at->toDateTimeString());
                        $project_generatedlistfields_data->push($individual_generatedlistfield_data);
                    } catch (\Exception $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }

                // MultiSelectListField
                foreach ($record->multiselectlistfields()->get() as $multiselectlistfield) {
                    try {
                        $individual_multiselectlistfield_data = new Collection();
                        $individual_multiselectlistfield_data->put("id", $multiselectlistfield->id);
                        $individual_multiselectlistfield_data->put("rid", $multiselectlistfield->rid);
                        $individual_multiselectlistfield_data->put("flid", $multiselectlistfield->flid);
                        $individual_multiselectlistfield_data->put("options", $multiselectlistfield->options);
                        $individual_multiselectlistfield_data->put("created_at", $multiselectlistfield->created_at->toDateTimeString());
                        $individual_multiselectlistfield_data->put("updated_at", $multiselectlistfield->updated_at->toDateTimeString());
                        $project_multiselectlistfields_data->push($individual_multiselectlistfield_data);
                    } catch (\Exception $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }

                // DateField
                foreach ($record->datefields()->get() as $datefield) {
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
                        $project_datefields_data->push($individual_datefield_data);
                    } catch (\Exception $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }

                // ScheduleField
                foreach ($record->schedulefields()->get() as $schedulefield) {
                    try {
                        $individual_schedulefield_data = new Collection();
                        $individual_schedulefield_data->put("id", $schedulefield->id);
                        $individual_schedulefield_data->put("rid", $schedulefield->rid);
                        $individual_schedulefield_data->put("flid", $schedulefield->flid);
                        $individual_schedulefield_data->put("events", $schedulefield->events);
                        $individual_schedulefield_data->put("created_at", $schedulefield->created_at->toDateTimeString());
                        $individual_schedulefield_data->put("updated_at", $schedulefield->updated_at->toDateTimeString());
                        $project_schedulefields_data->push($individual_schedulefield_data);
                    } catch (\Exception $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }

                //  GeolocatorField
                foreach ($record->geolocatorfields()->get() as $geolocatorfield) {
                    try {
                        $individual_geolocatorfield_data = new Collection();
                        $individual_geolocatorfield_data->put("id", $geolocatorfield->id);
                        $individual_geolocatorfield_data->put("rid", $geolocatorfield->rid);
                        $individual_geolocatorfield_data->put("flid", $geolocatorfield->flid);
                        $individual_geolocatorfield_data->put("locations",$geolocatorfield->locations);
                        $individual_geolocatorfield_data->put("created_at", $geolocatorfield->created_at->toDateTimeString());
                        $individual_geolocatorfield_data->put("updated_at", $geolocatorfield->updated_at->toDateTimeString());
                        $project_geolocatorfields_data->push($individual_geolocatorfield_data);
                    } catch (\Exception $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }

                // DocumentsField
                foreach($record->documentsfields()->get() as $documentsfield){
                    try{
                        $individual_documentsfield_data = new Collection();
                        $individual_documentsfield_data->put("id",$documentsfield->id);
                        $individual_documentsfield_data->put("rid",$documentsfield->rid);
                        $individual_documentsfield_data->put("flid",$documentsfield->flid);
                        $individual_documentsfield_data->put("documents",$documentsfield->documents);
                        $individual_documentsfield_data->put("created_at", $documentsfield->created_at->toDateTimeString());
                        $individual_documentsfield_data->put("updated_at", $documentsfield->updated_at->toDateTimeString());
                        $project_documentsfield_data->push($individual_documentsfield_data);
                    } catch(\Exception $e){
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }

                // PlaylistField
                foreach($record->playlistfields()->get() as $playlistfield){
                    try{
                        $individual_playlistfield_data = new Collection();
                        $individual_playlistfield_data->put("id",$playlistfield->id);
                        $individual_playlistfield_data->put("rid",$playlistfield->rid);
                        $individual_playlistfield_data->put("flid",$playlistfield->flid);
                        $individual_playlistfield_data->put("audio",$playlistfield->audio);
                        $individual_playlistfield_data->put("created_at", $playlistfield->created_at->toDateTimeString());
                        $individual_playlistfield_data->put("updated_at", $playlistfield->updated_at->toDateTimeString());
                        $project_playlistfield_data->push($individual_playlistfield_data);
                    } catch(\Exception $e){
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }

                // VideoField
                foreach($record->videofields()->get() as $videofield){
                    try{
                        $individual_videofield_data = new Collection();
                        $individual_videofield_data->put("id",$videofield->id);
                        $individual_videofield_data->put("rid",$videofield->rid);
                        $individual_videofield_data->put("flid",$videofield->flid);
                        $individual_videofield_data->put("video",$videofield->video);
                        $individual_videofield_data->put("created_at", $videofield->created_at->toDateTimeString());
                        $individual_videofield_data->put("updated_at", $videofield->updated_at->toDateTimeString());
                        $project_videofield_data->push($individual_videofield_data);
                    } catch(\Exception $e){
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }

                // ModelField
                foreach($record->modelfields()->get() as $modelfield){
                    try{
                        $individual_modelfield_data = new Collection();
                        $individual_modelfield_data->put("id",$modelfield->id);
                        $individual_modelfield_data->put("rid",$modelfield->rid);
                        $individual_modelfield_data->put("flid",$modelfield->flid);
                        $individual_modelfield_data->put("model",$modelfield->model);
                        $individual_modelfield_data->put("created_at", $modelfield->created_at->toDateTimeString());
                        $individual_modelfield_data->put("updated_at", $modelfield->updated_at->toDateTimeString());
                        $project_modelfield_data->push($individual_modelfield_data);
                    } catch(\Exception $e){
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }

                // GallerylField
                foreach($record->galleryfields()->get() as $galleryfield){
                    try{
                        $individual_galleryfield_data = new Collection();
                        $individual_galleryfield_data->put("id",$galleryfield->id);
                        $individual_galleryfield_data->put("rid",$galleryfield->rid);
                        $individual_galleryfield_data->put("flid",$galleryfield->flid);
                        $individual_galleryfield_data->put("images",$galleryfield->images);
                        $individual_galleryfield_data->put("created_at", $galleryfield->created_at->toDateTimeString());
                        $individual_galleryfield_data->put("updated_at", $galleryfield->updated_at->toDateTimeString());
                        $project_galleryfield_data->push($individual_galleryfield_data);
                    } catch(\Exception $e){
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }

                //ComboList Field

                foreach($record->combolistfields()->get() as $combolistfield){
                    try{
                        $individual_combolistfield_data = new Collection();
                        $individual_combolistfield_data->put('id',$combolistfield->id);
                        $individual_combolistfield_data->put('rid',$combolistfield->rid);
                        $individual_combolistfield_data->put('flid',$combolistfield->flid);
                        $individual_combolistfield_data->put('options',$combolistfield->options);
                        $individual_combolistfield_data->put('ftype1',$combolistfield->ftype1);
                        $individual_combolistfield_data->put('ftype2',$combolistfield->ftype2);
                        $individual_combolistfield_data->put("created_at", $combolistfield->created_at->toDateTimeString());
                        $individual_combolistfield_data->put("updated_at", $combolistfield->updated_at->toDateTimeString());
                        $project_combolistfield_data->push($individual_combolistfield_data);

                    }catch(\Exception $e){
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }


            } catch (\Exception $e) {
                $this->ajax_error_list->push($e->getMessage());
            }
        }
        $project_data->put("records",$project_records_data);
        $project_data->put("textfields", $project_textfields_data);
        $project_data->put("richtextfields",$project_richtextfields_data);
        $project_data->put("numberfields",$project_numberfields_data);
        $project_data->put("listfields",$project_listfields_data);
        $project_data->put("generatedlistfields",$project_generatedlistfields_data);
        $project_data->put("multiselectlistfields",$project_multiselectlistfields_data);
        $project_data->put("datefields", $project_datefields_data);
        $project_data->put("schedulefields", $project_schedulefields_data);
        $project_data->put("geolocatorfields", $project_geolocatorfields_data);
        $project_data->put('documentsfield',$project_documentsfield_data);
        $project_data->put('playlistfield',$project_playlistfield_data);
        $project_data->put('videofield',$project_videofield_data);
        $project_data->put('modelfield',$project_modelfield_data);
        $project_data->put('galleryfield',$project_galleryfield_data);
        $project_data->put('combolistfield',$project_combolistfield_data);


        return $project_data;

    }

    public function backupProject($pid, Request $request){
        $this->validate($request,[
            'backup_label'=>'required|alpha_dash',
        ]);

        if(Project::find($pid) == null){
            flash()->overlay("The project you selected does not exist.","Whoops.");
            return redirect("/");
        }

        $backup_label = $request->input("backup_label");
        $request->session()->put("backup_new_label",$backup_label);
        $request->session()->put("backup_pid",$pid);
        $type = "project";
        return view('backups.backup',compact('backup_label','type'));
    }

    public function startRestoreProject(Request $request){
        $this->validate($request,[
            'backup_source'=>'required_in:server',
            'restore_point'=>'required_if:backup_source,server',
            'upload_file'=>'required_if:backup_source,upload'
        ]);
        $type = "project";
        if(true){
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

        return view('backups.restore',compact('type'));
    }

    public function restoreProject(Request $request){

        $this->json_file = null;
        $this->decoded_json = null;
        $pid =  null;

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
            return $this->ajaxResponse(false,"The backup file couldn't be opened.  Make sure it still exists and the permissions are correct.");
        }
        try {
            $this->decoded_json = json_decode($this->json_file);
            $this->decoded_json->kora3;

            if($this->decoded_json->kora3->type != "project_backup") {
                return $this->ajaxResponse(false, "The backup type is not valid, you are trying to restore a non-project backup.");
            }
            $pid = $this->decoded_json->kora3->pid;
        }
        catch(\Exception $e){
            return $this->ajaxResponse(false,"The backup file contains invalid JSON data, it may be corrupt or damaged.  Check the file or try another one.  The restore did not start, so data already in the database was not deleted.");
        }
        try{
            $media_file_location = $this->decoded_json->kora3->filename;
            Storage::get($this->BACKUP_DIRECTORY."/files/"."p$pid/".$media_file_location."/files");
        }
        catch(\Exception $e){
           return $this->ajaxResponse(false,"Sorry, the required media files could not be found at $this->BACKUP_DIRECTORY/$media_file_location/files.  Place the files in that location, or create an empty directory with that name to proceed without them.  The existing database and records were not deleted, it should be safe to unlock users.");
        }

        $backup_data = $this->decoded_json;

        //Delete all existing data
        try {
            $project = Project::findOrFail($pid);
            $project->active = 0;
            $project->save();

            foreach ($project->forms()->get() as $Form) {
                foreach($Form->records()->get() as $Record){
                    $Record->delete();
                }
                $Form->delete();
            }
            foreach (Metadata::where('pid','=',$pid) as $Metadata) {
                $Metadata->delete();
            }
            foreach(FormGroup::all() as $FormGroup){
                $FormGroup->delete();
            }
            foreach(ProjectGroup::all() as $ProjectGroup){
                $ProjectGroup->delete();
            }



        }catch(\Exception $e){
            $project->active = 1;
            $project->save();
            return $this->ajaxResponse(false, "There was a problem when attempting to remove existing information from the
            database, the database user may not have permission to do this or the database may be in use.");
        }
        try{
            //$this->copyMediaFiles(ENV('BASE_PATH').'storage/app/backups/files/'.$backup_data->kora3->filename,ENV('BASE_PATH')."storage/app/".$this->MEDIA_DIRECTORY);
            //$this->deleteMediaFiles(ENV('BASE_PATH').'storage/app/backups/files/'.$backup_data->kora3->filename);
            $project->active = 1;
            $project->save();
            $this->deleteMediaFiles($this->MEDIA_DIRECTORY);

        }catch(\Exception $e){
            $project->active = 1;
            $project->save();
            $this->ajax_error_list->push($e->getMessage());
            return $this->ajaxResponse(false,"There was a problem when attempting to remove existing media files, make sure
            the permissions are correct and the files are not in use.");
        }

        $this->backup_media_files_path = ENV('BASE_PATH') . 'storage/app/' . $this->BACKUP_DIRECTORY . "/files/p$pid/" . $backup_data->kora3->filename . "/files";
        $this->backup_filename = $backup_data->kora3->filename; //Don't rename Kora3 backups
        $this->backup_file_list = new Collection();
        try {
            //Prepare a list
            $this->verifyBackedUpMediaFiles($this->backup_media_files_path, $this->backup_file_list);
        }
        catch(\Exception $e){
            $project->active = 1;
            $project->save();
            $this->ajax_error_list->push($e->getMessage());
            return $this->ajaxResponse(false,"There is a problem with the media files for Documents/Gallery/Video/Model fields.");
        }
        try { //This try-catch is for non-QueryExceptions, like if a table is missing entirely from the JSON data
            // User
            // Project
           /* foreach ($backup_data->projects as $project) {
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
            } */

            try{
                $project = Project::findOrFail($pid);
                $project->name = $backup_data->project->name;
                $project->slug = $backup_data->project->slug;
                $project->description = $backup_data->project->description;
                $project->adminGID = $backup_data->project->adminGID;
                //The other attributes shouldn't be changed
            } catch(\Exception $e){
                $this->ajax_error_list->push($e->getMessage());
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
            foreach ($backup_data->form_groups as $formgroup) {
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
            foreach ($backup_data->project_groups as $projectgroup) {
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

            // ComboListField
            foreach($backup_data->combolistfield as $combolistfield){
                try{
                    $new_combolistfield = new ComboListField(array("rid"=>$combolistfield->rid,"flid"=>$combolistfield->flid,"options"=>$combolistfield->options,"ftype1"=>$combolistfield->ftype1,"ftype2"=>$combolistfield->ftype2));
                    $new_combolistfield->id = $combolistfield->id;
                    $new_combolistfield->created_at = $combolistfield->created_at;
                    $new_combolistfield->updated_at = $combolistfield->updated_at;
                    $new_combolistfield->save();
                }
                catch(QueryException $e){
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
                } catch(QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // GeolocatorField
            foreach($backup_data->geolocatorfields as $geolocatorfield){
                try{
                    $new_geolocatorfield = new GeolocatorField(array("rid"=>$geolocatorfield->rid,"flid"=>$geolocatorfield->flid,"locations"=>$geolocatorfield->locations));
                    $new_geolocatorfield->id = $geolocatorfield->id;
                    $new_geolocatorfield->created_at = $geolocatorfield->created_at;
                    $new_geolocatorfield->updated_at = $geolocatorfield->updated_at;
                    $new_geolocatorfield->save();
                }catch(QueryException $e){
                    $this->ajax_error_list->push($e->getMessage());
                }
            }

            // DocumentsField (other file/media fields have slightly different names but are the same
            foreach($backup_data->documentsfield as $documentsfield){
                $files_db_row = $documentsfield->documents;  //This is the database row with filenames/info
                $df_filenames = $this->getRecordFileNames($documentsfield->documents); //get the file names only from the row
                $files_present = $this->verifyMediaFilesExist($documentsfield->rid, $documentsfield->flid, $df_filenames); //check that the files exist at the expected location

                //If there are less files than there should be, remove them from the database row before restoring it
                if($files_present->count() < $df_filenames->count()){
                    $files_db_row = $this->removeFilesFromDbRow($files_db_row,$files_present);
                    $this->ajax_error_list->push("Record ".$documentsfield->rid." is missing files, and was only partially restored.  Locate the missing files and run the restore process again.");
                }

                //Only create a databse row if at least SOME files were restored, but not if none
                if ($files_present->count() > 0) {
                    try {
                        $new_documentsfield = new DocumentsField(array("rid" => $documentsfield->rid, "flid" => $documentsfield->flid, "documents" => $files_db_row));
                        $new_documentsfield->id = $documentsfield->id;
                        $new_documentsfield->created_at = $documentsfield->created_at;
                        $new_documentsfield->updated_at = $documentsfield->updated_at;
                        $new_documentsfield->save();
                    } catch (QueryException $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }
                else{
                    $this->ajax_error_list->push("Record ".$documentsfield->rid." Documents field was not restored because it is missing all required files.");
                }
            }
            // GalleryField
            foreach($backup_data->galleryfield as $galleryfield){
                $files_db_row = $galleryfield->images;
                $gf_filenames = $this->getRecordFileNames($galleryfield->images);
                $files_present = $this->verifyMediaFilesExist($galleryfield->rid, $galleryfield->flid, $gf_filenames);

                if($files_present->count() < $gf_filenames->count()){
                    $files_db_row = $this->removeFilesFromDbRow($files_db_row,$files_present);
                    $this->ajax_error_list->push("Record ".$galleryfield->rid." is missing files, and was only partially restored.  Locate the missing files and run the restore process again.");
                }

                if ($files_present->count() > 0) {

                    try {
                        $new_galleryfield = new GalleryField(array("rid" => $galleryfield->rid, "flid" => $galleryfield->flid, "images" => $files_db_row));
                        $new_galleryfield->id = $galleryfield->id;
                        $new_galleryfield->created_at = $galleryfield->created_at;
                        $new_galleryfield->updated_at = $galleryfield->updated_at;
                        $new_galleryfield->save();
                    } catch (QueryException $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }
                else{
                    $this->ajax_error_list->push("Record ".$galleryfield->rid." Gallery field was not restored because it is missing files.");
                }
            }
            // ModelField
            foreach($backup_data->modelfield as $modelfield){
                $files_db_row = $modelfield->model;
                $mf_filenames = $this->getRecordFileNames($modelfield->model);
                $files_present = $this->verifyMediaFilesExist($modelfield->rid, $modelfield->flid, $mf_filenames);

                if($files_present->count() < $mf_filenames->count()){
                    $files_db_row = $this->removeFilesFromDbRow($files_db_row,$files_present);
                    $this->ajax_error_list->push("Record ".$modelfield->rid." is missing files, and was only partially restored.  Locate the missing files and run the restore process again.");
                }


                if ($files_present->count() > 0) {
                    try {
                        $new_modelfield = new ModelField(array("rid" => $modelfield->rid, "flid" => $modelfield->flid, "model" => $files_db_row));
                        $new_modelfield->id = $modelfield->id;
                        $new_modelfield->created_at = $modelfield->created_at;
                        $new_modelfield->updated_at = $modelfield->updated_at;
                        $new_modelfield->save();
                    } catch (QueryException $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }
                else{
                    $this->ajax_error_list->push("Record ".$modelfield->rid." Model field $modelfield->flid was not restored because it is missing files.");
                }
            }
            // PlaylistField
            foreach($backup_data->playlistfield as $playlistfield){
                $files_db_row = $playlistfield->audio;
                $pf_filenames = $this->getRecordFileNames($playlistfield->audio);
                $files_present = $this->verifyMediaFilesExist($playlistfield->rid,$playlistfield->flid,$pf_filenames);

                if($files_present->count() < $pf_filenames->count()){
                    $files_db_row = $this->removeFilesFromDbRow($files_db_row,$files_present);
                    $this->ajax_error_list->push("Record ".$playlistfield->rid." is missing files, and was only partially restored.  Locate the missing files and run the restore process again.");
                }

                if ($files_present->count() > 0) {
                    try {
                        $new_playlistfield = new PlaylistField(array("rid" => $playlistfield->rid, "flid" => $playlistfield->flid, "audio" => $playlistfield->audio));
                        $new_playlistfield->id = $playlistfield->id;
                        $new_playlistfield->created_at = $playlistfield->created_at;
                        $new_playlistfield->updated_at = $playlistfield->updated_at;
                        $new_playlistfield->save();
                    } catch (QueryException $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }
                else{
                    $this->ajax_error_list->push("Record ".$playlistfield->rid." was not restored because it is missing files.");
                }
            }
            // VideoField
            foreach($backup_data->videofield as $videofield){
                $files_db_row = $videofield->video;
                $vf_filenames = $this->getRecordFileNames($videofield->video);
                $files_present = $this->verifyMediaFilesExist($videofield->rid,$videofield->flid,$vf_filenames);

                if($files_present->count() < $vf_filenames->count()){
                    $files_db_row = $this->removeFilesFromDbRow($files_db_row,$files_present);
                    $this->ajax_error_list->push("Record ".$videofield->rid." is missing files, and was only partially restored.  Locate the missing files and run the restore process again.");
                }

                if ($files_present->count() > 0) {
                    try {
                        $new_videofield = new VideoField(array("rid" => $videofield->rid, "flid" => $videofield->flid, "video" => $videofield->video));
                        $new_videofield->id = $videofield->id;
                        $new_videofield->created_at = $videofield->created_at;
                        $new_videofield->updated_at = $videofield->updated_at;
                        $new_videofield->save();
                    } catch (QueryException $e) {
                        $this->ajax_error_list->push($e->getMessage());
                    }
                }
                else{
                    $this->ajax_error_list->push("Record ".$videofield->rid." was not restored because it is missing files.");
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
           /* // Revision
            foreach ($backup_data->revisions as $revision) {
                try {
                    $new_revision = new Revision(array('id' => $revision->id, 'fid' => $revision->fid, 'rid' => $revision->rid, 'userId' => $revision->userId, 'type' => $revision->type, 'data' => $revision->data, 'oldData' => $revision->oldData, 'rollback' => $revision->rollback));
                    $new_revision->created_at = $revision->created_at;
                    $new_revision->updated_at = $revision->updated_at;
                    $new_revision->save();
                } catch (QueryException $e) {
                    $this->ajax_error_list->push($e->getMessage());
                }
            } */

            $this->copyMediaFiles($this->BACKUP_DIRECTORY."/files/p$pid/".$this->backup_filename,"/",addcslashes($this->BACKUP_DIRECTORY . "/files/p$pid/" . $this->backup_filename, "/"));

        }
        catch(\Exception $e){
            $project->active = 1;
            $project->save();
            $this->ajax_error_list->push($e->getMessage());
            return $this->ajaxResponse(false,"An unknown error prevented the restore from completing.
                You can try restoring from a different restore point.");
        }

        if(count($this->ajax_error_list) != 0){
            $project->active = 1;
            $project->save();
            return $this->ajaxResponse(false,"Not all of your data was restored, check the errors below for details.
            The errors are in the order that they occurred, if you can resolve the first error, it will often correct
            one or more of the errors below it.");
        }
        else{
            //$this->unlockUsers();
            $project->active = 1;
            $project->save();
            return $this->ajaxResponse(true,"The restore completed successfully.");
        }
    }

    public function createProject(Request $request){

        if($request->session()->has("backup_pid")){
            $pid = $request->session()->get("backup_pid");
            $request->session()->forget("backup_pid");
        }
        else{
            return $this->ajaxResponse(false,"The project could not be backed up, because you didn't specify a project");
        }

        //locate project
        try{
            $project = Project::findorFail($pid);
        }
        catch(\Exception $e){
            return $this->ajaxResponse(false,"The project could not be backed up, because the project you selected couldn't be found, make sure it still exists");
        }

        //verify permissions

        if(!Auth::user()->admin && !Auth::user()->isProjectAdmin($project)){
            return $this->ajaxResponse(false,"Only a project admin can backup a project");
        }

        //mark project inactive

        $project->active = 0;
        $project->save();

        //retrieve backup label
        if($request->session()->has("backup_new_label")){
            $backup_label = $request->session()->get("backup_new_label");
            $request->session()->forget("backup_new_label");
        }
        else{
            $backup_label = "";
        }

        //set backup filename
        $this->backup_filename = Carbon::now()->format("Y-m-d_H:i:s"). ".kora3_backup";

        //get backup JSON data
        $this->backup_data = $this->saveProject($pid,$backup_label);

        //Copy the media files
        $this->copyMediaFiles($this->MEDIA_DIRECTORY."/p".$project->pid,$this->BACKUP_DIRECTORY."/files/p".$project->pid."/".$this->backup_filename,addcslashes($this->BACKUP_DIRECTORY . "/files/" . $this->backup_filename, "/"));

        $this->backup_filepath = $this->BACKUP_DIRECTORY."/p".$project->pid."/".$this->backup_filename;

        $backup_files_list = new Collection();

        $project->active = 1;
        $project->save();

        if(Storage::exists($this->backup_filepath)){
            return $this->ajaxResponse(false,"Could not create the backup, a file with that name already exists.");
        }
        else{
            try {
                Storage::put($this->backup_filepath, $this->backup_data);
            }
            catch(\Exception $e){
                $this->ajax_error_list->push($e->getMessage());
                return $this->ajaxResponse(false,"The backup failed, unable to save the backup file.");
            }
        }
        if($this->ajax_error_list->count() >0){
            //$request->session()->put("backup_file_name",$this->backup_filename);
            return $this->ajaxResponse(false,"The backup completed with errors, the backup may be corrupted.  You can try downloading the file, if one was created.");
        }
        else{
            //$request->session()->put("backup_file_name",$this->backup_filename);
            return $this->ajaxResponse(true,"The backup completed successfully");
        }

    }

}



