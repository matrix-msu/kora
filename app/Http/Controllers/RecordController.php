<?php namespace App\Http\Controllers;

use App\AssociatorField;
use App\ComboListField;
use App\DateField;
use App\DocumentsField;
use App\GalleryField;
use App\GeneratedListField;
use App\ModelField;
use App\PlaylistField;
use App\RecordPreset;
use App\GeolocatorField;
use App\Revision;
use App\ScheduleField;
use App\User;
use App\Form;
use App\Field;
use App\Record;
use App\TextField;
use App\NumberField;
use App\RichTextField;
use App\ListField;
use App\MultiSelectListField;
use App\VideoField;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

class RecordController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Record Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles record creation and manipulation
    |
    */

    /**
     * @var int - Number of allowed records per page
     */
    const RECORDS_PER_PAGE = 10;

    /**
     * Constructs controller and makes sure user is authenticated.
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * Gets the all records view.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
	public function index($pid, $fid) {
        if(!FormController::validProjForm($pid,$fid)) {
            return redirect('projects');
        }

        if(!self::checkPermissions($fid)) {
            return redirect('projects/' . $pid . '/forms/' . $fid);
        }

        $form = FormController::getForm($fid);
        $filesize = self::getFormFilesize($fid);
        $records = Record::where('fid', '=', $fid)->paginate(self::RECORDS_PER_PAGE);
        $records->setPath(env('BASE_URL').'projects/'.$pid.'/forms/'.$fid.'/records');

        return view('records.index', compact('form', 'filesize', 'records'));
	}

    /**
     * Gets the new record view.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
	public function create($pid, $fid) {
        if(!FormController::validProjForm($pid,$fid)) {
            return redirect('projects');
        }

        if(!self::checkPermissions($fid, 'ingest')) {
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        $form = FormController::getForm($fid);
        $presets = array();

        foreach(RecordPreset::where('fid', '=', $fid)->get() as $preset)
            $presets[] = ['id' => $preset->id, 'name' => $preset->name];

        $fields = array(); //array of field ids
        foreach($form->fields()->get() as $field)
            $fields[] = $field->flid;

        return view('records.create', compact('form', 'presets', 'fields'));
	}

    /**
     * Saves a new record in Kora.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return Redirect
     */
	public function store($pid, $fid, Request $request) {
        if(!FormController::validProjForm($pid,$fid)) {
            return redirect('projects');
        }

        foreach($request->all() as $key => $value) {
            if(!is_numeric($key))
                continue;
            $message = Field::validateField($key, $value, $request);
            if($message != '') {
                flash()->error($message);

                $arrayed_keys = array();

                foreach($request->all() as $akey => $avalue) {
                    if(is_array($avalue)) {
                        array_push($arrayed_keys,$akey);
                    }
                }

                return redirect()->back()->withInput($request->except($arrayed_keys));
            }
        }

        if($request->mass_creation == "on")
            $numRecs = $request->mass_creation_num;
        else
            $numRecs = 1;

        //safeguard
        if($numRecs>1000)
            $numRecs = 1000;

        for($i = 0; $i < $numRecs ; $i++) {
            $record = new Record();
            $record->pid = $pid;
            $record->fid = $fid;
            $record->owner = $request->userId;
            $record->save(); //need to save to create rid needed to make kid
            $record->kid = $pid . '-' . $fid . '-' . $record->rid;
            $record->save();

            foreach($request->all() as $key => $value) {
                if(!is_numeric($key))
                    continue;
                $field = FieldController::getField($key);
                //TODO::modular
                if($field->type == 'Text') {
                    if (!empty($value) && !is_null($value)) {
                        $tf = new TextField();
                        $tf->flid = $field->flid;
                        $tf->rid = $record->rid;
                        $tf->fid = $fid;
                        $tf->text = $value;
                        $tf->save();
                    }
                } else if ($field->type == 'Rich Text') {
                    if (!empty($value) && !is_null($value)) {
                        $rtf = new RichTextField();
                        $rtf->flid = $field->flid;
                        $rtf->rid = $record->rid;
                        $rtf->fid = $fid;
                        $rtf->rawtext = $value;
                        $rtf->save();
                    }
                } else if ($field->type == 'Number') {
                    if (!empty($value) && !is_null($value)) {
                        $nf = new NumberField();
                        $nf->flid = $field->flid;
                        $nf->rid = $record->rid;
                        $nf->fid = $fid;
                        $nf->number = $value;
                        $nf->save();
                    }
                } else if ($field->type == 'List') {
                    $lf = new ListField();
                    $lf->flid = $field->flid;
                    $lf->rid = $record->rid;
                    $lf->fid = $fid;
                    $lf->option = $value;
                    $lf->save();
                } else if ($field->type == 'Multi-Select List') {
                    $mslf = new MultiSelectListField();
                    $mslf->flid = $field->flid;
                    $mslf->rid = $record->rid;
                    $mslf->fid = $fid;
                    $mslf->options = implode("[!]",$value);
                    $mslf->save();
                } else if ($field->type == 'Generated List') {
                    $glf = new GeneratedListField();
                    $glf->flid = $field->flid;
                    $glf->rid = $record->rid;
                    $glf->fid = $fid;
                    $glf->options = implode("[!]",$value);
                    $glf->save();
                } else if($field->type == 'Combo List' && $request->input($field->flid.'_val') != null){
                    $clf = new ComboListField();
                    $clf->flid = $field->flid;
                    $clf->rid = $record->rid;
                    $clf->fid = $fid;
                    $clf->save();

                    $type_1 = ComboListField::getComboFieldType($field, 'one');
                    $type_2 = ComboListField::getComboFieldType($field, 'two');

                    // Add combo data to support table.
                    $clf->addData($request->input($field->flid.'_val'), $type_1, $type_2);
                } else if ($field->type == 'Date' && $request->input('year_' . $field->flid) != '') {
                    $df = new DateField();
                    $df->flid = $field->flid;
                    $df->rid = $record->rid;
                    $df->fid = $fid;
                    $df->circa = $request->input('circa_' . $field->flid, '');
                    $df->month = $request->input('month_' . $field->flid);
                    $df->day = $request->input('day_' . $field->flid);
                    $df->year = $request->input('year_' . $field->flid);
                    $df->era = $request->input('era_' . $field->flid, 'CE');
                    $df->save();
                } else if ($field->type == 'Schedule') {
                    $sf = new ScheduleField();
                    $sf->flid = $field->flid;
                    $sf->rid = $record->rid;
                    $sf->fid = $fid;
                    $sf->save();

                    $sf->addEvents($value);
                } else if ($field->type == 'Geolocator') {
                    $gf = new GeolocatorField();
                    $gf->flid = $field->flid;
                    $gf->rid = $record->rid;
                    $gf->fid = $fid;
                    $gf->save();

                    $gf->addLocations($value);
                } else if ($field->type == 'Documents' && glob(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/*.*') != false) {
                    $df = new DocumentsField();
                    $df->flid = $field->flid;
                    $df->rid = $record->rid;
                    $df->fid = $fid;
                    $infoString = '';
                    $infoArray = array();
                    $newPath = env('BASE_PATH') . 'storage/app/files/p' . $pid . '/f' . $fid . '/r' . $record->rid . '/fl' . $field->flid;
                    mkdir($newPath, 0775, true);
                    if (file_exists(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value)) {
                        $types = DocumentsField::getMimeTypes();
                        foreach (new \DirectoryIterator(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value) as $file) {
                            if ($file->isFile()) {
                                if (!array_key_exists($file->getExtension(), $types))
                                    $type = 'application/octet-stream';
                                else
                                    $type = $types[$file->getExtension()];
                                $info = '[Name]' . $file->getFilename() . '[Name][Size]' . $file->getSize() . '[Size][Type]' . $type . '[Type]';
                                $infoArray[$file->getFilename()] = $info;
                                copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/' . $file->getFilename(),
                                    $newPath . '/' . $file->getFilename());
                            }
                        }
                        foreach($request->input('file'.$field->flid) as $fName){
                            if($fName!=''){
                                if ($infoString == '') {
                                    $infoString = $infoArray[$fName];
                                } else {
                                    $infoString .= '[!]' . $infoArray[$fName];
                                }
                            }
                        }
                    }
                    $df->documents = $infoString;
                    $df->save();
                } else if ($field->type == 'Gallery' && glob(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/*.*') != false) {
                    $gf = new GalleryField();
                    $gf->flid = $field->flid;
                    $gf->rid = $record->rid;
                    $gf->fid = $fid;
                    $infoString = '';
                    $infoArray = array();
                    $newPath = env('BASE_PATH') . 'storage/app/files/p' . $pid . '/f' . $fid . '/r' . $record->rid . '/fl' . $field->flid;
                    //make the three directories
                    mkdir($newPath, 0775, true);
                    mkdir($newPath . '/thumbnail', 0775, true);
                    mkdir($newPath . '/medium', 0775, true);
                    if (file_exists(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value)) {
                        $types = DocumentsField::getMimeTypes();
                        foreach (new \DirectoryIterator(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value) as $file) {
                            if ($file->isFile()) {
                                if (!array_key_exists($file->getExtension(), $types))
                                    $type = 'application/octet-stream';
                                else
                                    $type = $types[$file->getExtension()];
                                $info = '[Name]' . $file->getFilename() . '[Name][Size]' . $file->getSize() . '[Size][Type]' . $type . '[Type]';
                                $infoArray[$file->getFilename()] = $info;
                                copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/' . $file->getFilename(),
                                    $newPath . '/' . $file->getFilename());
                                copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/thumbnail/' . $file->getFilename(),
                                    $newPath . '/thumbnail/' . $file->getFilename());
                                copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/medium/' . $file->getFilename(),
                                    $newPath . '/medium/' . $file->getFilename());
                            }
                        }
                        foreach($request->input('file'.$field->flid) as $fName){
                            if($fName!=''){
                                if ($infoString == '') {
                                    $infoString = $infoArray[$fName];
                                } else {
                                    $infoString .= '[!]' . $infoArray[$fName];
                                }
                            }
                        }
                    }
                    $gf->images = $infoString;
                    $gf->save();
                } else if ($field->type == 'Playlist' && glob(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/*.*') != false) {
                    $pf = new PlaylistField();
                    $pf->flid = $field->flid;
                    $pf->rid = $record->rid;
                    $pf->fid = $fid;
                    $infoString = '';
                    $infoArray = array();
                    $newPath = env('BASE_PATH') . 'storage/app/files/p' . $pid . '/f' . $fid . '/r' . $record->rid . '/fl' . $field->flid;
                    mkdir($newPath, 0775, true);
                    if (file_exists(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value)) {
                        $types = DocumentsField::getMimeTypes();
                        foreach (new \DirectoryIterator(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value) as $file) {
                            if ($file->isFile()) {
                                if (!array_key_exists($file->getExtension(), $types))
                                    $type = 'application/octet-stream';
                                else
                                    $type = $types[$file->getExtension()];
                                $info = '[Name]' . $file->getFilename() . '[Name][Size]' . $file->getSize() . '[Size][Type]' . $type . '[Type]';
                                $infoArray[$file->getFilename()] = $info;
                                copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/' . $file->getFilename(),
                                    $newPath . '/' . $file->getFilename());
                            }
                        }
                        foreach($request->input('file'.$field->flid) as $fName){
                            if($fName!=''){
                                if ($infoString == '') {
                                    $infoString = $infoArray[$fName];
                                } else {
                                    $infoString .= '[!]' . $infoArray[$fName];
                                }
                            }
                        }
                    }
                    $pf->audio = $infoString;
                    $pf->save();
                } else if ($field->type == 'Video' && glob(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/*.*') != false) {
                    $vf = new VideoField();
                    $vf->flid = $field->flid;
                    $vf->rid = $record->rid;
                    $vf->fid = $fid;
                    $infoString = '';
                    $infoArray = array();
                    $newPath = env('BASE_PATH') . 'storage/app/files/p' . $pid . '/f' . $fid . '/r' . $record->rid . '/fl' . $field->flid;
                    mkdir($newPath, 0775, true);
                    if (file_exists(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value)) {
                        $types = DocumentsField::getMimeTypes();
                        foreach (new \DirectoryIterator(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value) as $file) {
                            if ($file->isFile()) {
                                if (!array_key_exists($file->getExtension(), $types))
                                    $type = 'application/octet-stream';
                                else
                                    $type = $types[$file->getExtension()];
                                $info = '[Name]' . $file->getFilename() . '[Name][Size]' . $file->getSize() . '[Size][Type]' . $type . '[Type]';
                                $infoArray[$file->getFilename()] = $info;
                                copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/' . $file->getFilename(),
                                    $newPath . '/' . $file->getFilename());
                            }
                        }
                        foreach($request->input('file'.$field->flid) as $fName){
                            if($fName!=''){
                                if ($infoString == '') {
                                    $infoString = $infoArray[$fName];
                                } else {
                                    $infoString .= '[!]' . $infoArray[$fName];
                                }
                            }
                        }
                    }
                    $vf->video = $infoString;
                    $vf->save();
                } else if ($field->type == '3D-Model' && glob(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/*.*') != false) {
                    $mf = new ModelField();
                    $mf->flid = $field->flid;
                    $mf->rid = $record->rid;
                    $mf->fid = $fid;
                    $infoString = '';
                    $infoArray = array();
                    $newPath = env('BASE_PATH') . 'storage/app/files/p' . $pid . '/f' . $fid . '/r' . $record->rid . '/fl' . $field->flid;
                    mkdir($newPath, 0775, true);
                    if (file_exists(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value)) {
                        $types = DocumentsField::getMimeTypes();
                        foreach (new \DirectoryIterator(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value) as $file) {
                            if ($file->isFile()) {
                                if (!array_key_exists($file->getExtension(), $types))
                                    $type = 'application/octet-stream';
                                else
                                    $type = $types[$file->getExtension()];
                                $info = '[Name]' . $file->getFilename() . '[Name][Size]' . $file->getSize() . '[Size][Type]' . $type . '[Type]';
                                $infoArray[$file->getFilename()] = $info;
                                copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/' . $file->getFilename(),
                                    $newPath . '/' . $file->getFilename());
                            }
                        }
                        foreach($request->input('file'.$field->flid) as $fName){
                            if($fName!=''){
                                if ($infoString == '') {
                                    $infoString = $infoArray[$fName];
                                } else {
                                    $infoString .= '[!]' . $infoArray[$fName];
                                }
                            }
                        }
                    }
                    $mf->model = $infoString;
                    $mf->save();
                } else if ($field->type == 'Associator') {
                    $af = new AssociatorField();
                    $af->flid = $field->flid;
                    $af->rid = $record->rid;
                    $af->fid = $fid;
                    $af->save();
                    $af->addRecords($value);
                }
            }

            //
            // Only create a revision if the record was not mass created.
            // This prevents clutter from an operation that the user
            // will obviously not want to undo using revisions.
            //
            if(!$request->mass_creation == "on")
                RevisionController::storeRevision($record->rid, 'create');
        }

        if($request->api) {
            return $record->kid;
        } else {
            flash()->overlay(trans('controller_record.created'), trans('controller_record.goodjob'));

            return redirect('projects/' . $pid . '/forms/' . $fid . '/records');
        }
	}

    /**
     * Gets the individual record view.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @return View
     */
	public function show($pid, $fid, $rid) {
        if(!self::validProjFormRecord($pid, $fid, $rid)) {
            return redirect('projects');
        }

        if(!self::checkPermissions($fid)) {
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        $form = FormController::getForm($fid);
        $record = self::getRecord($rid);
        $owner = User::where('id', '=', $record->owner)->first();

        return view('records.show', compact('record', 'form', 'pid', 'owner'));
	}

    /**
     * Get the edit record view.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @return View
     */
	public function edit($pid, $fid, $rid) {
        if(!self::validProjFormRecord($pid, $fid, $rid)) {
            return redirect('projects');
        }

        if(!\Auth::user()->isOwner(self::getRecord($rid)) && !self::checkPermissions($fid, 'modify')) {
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        $form = FormController::getForm($fid);
        $record = self::getRecord($rid);

        return view('records.edit', compact('record', 'form'));
	}

    /**
     * Gets record to be cloned and throws its data into the new record view.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @return View
     */
    public function cloneRecord($pid, $fid, $rid) {
        if(!self::validProjFormRecord($pid, $fid, $rid)) {
            return redirect('projects');
        }

        $form = FormController::getForm($fid);

        $rpc = new RecordPresetController();
        $cloneArray = $rpc->getRecordArray($rid);

        $presets = array();

        foreach(RecordPreset::where('fid', '=', $fid)->get() as $preset)
            $presets[] = ['id' => $preset->id, 'name' => $preset->name];

        $fields = array(); //array of field ids
        foreach($form->fields()->get() as $field)
            $fields[] = $field->flid;

        return view('records.create', compact('form', 'rid', 'presets', 'fields', 'cloneArray'));
    }

    /**
     * Removes record files from the system for records that no longer exist. This will prevent the possiblity of
     *  rolling back these records.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return array - The records that were removed
     */
    public function cleanUp($pid, $fid) {
        if(!FormController::validProjForm($pid,$fid)) {
            return redirect('projects');
        }

        if(!\Auth::user()->isFormAdmin(FormController::getForm($fid))) {
            flash()->overlay(trans('controller_record.noperm'), trans('controller_record.whoops'));
        }

        //
        // Using revisions, if a record's most recent change is a deletion,
        // we remove the file directory associated with that record.
        // More specifically, if the record no longer exists we
        // intend to clean up the files associated with it.
        //
        $all_revisions = Revision::where('fid', '=', $fid)->get();
        $rids = array();

        foreach($all_revisions as $revision) {
            $rids[] = $revision->rid;
        }
        $rids = array_unique($rids);

        $revisions = array(); // To be filled with revisions with records that do not exist.
        foreach($rids as $rid) {
            // If a record's most recent revision is a deletion...
            $revision = Revision::where('rid', '=', $rid)->orderBy('created_at', 'desc')->first();
            if($revision->type == Revision::DELETE)
                $revisions[] = $revision; // ... add to the array.
        }

        $base_path = env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid;

        //
        // For each revision, delete it's associated record's files.
        //
        foreach($revisions as $revision) {
            $path = $base_path . "/r" . $revision->rid;
            if(is_dir($path)) {
                $it = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
                $files = new RecursiveIteratorIterator($it,
                    RecursiveIteratorIterator::CHILD_FIRST);
                foreach($files as $file) {
                    if($file->isDir())
                        rmdir($file->getRealPath());
                    else
                        unlink($file->getRealPath());
                }
                rmdir($path);
            }
        }

        return $revisions;
    }


    /**
     * Update a record with new data.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @param  Request $request
     * @return Redirect
     */
	public function update($pid, $fid, $rid, Request $request) {
        if(!FormController::validProjForm($pid,$fid)) {
            return redirect('projects');
        }
        foreach($request->all() as $key => $value) {
            if(!is_numeric($key))
                continue;
            $message = Field::validateField($key, $value, $request);
            if($message != '') {
                flash()->error($message);
                return redirect()->back()->withInput();
            }
        }

        $record = Record::where('rid', '=', $rid)->first();
        $record->updated_at = Carbon::now();
        $record->save();

        $revision = RevisionController::storeRevision($record->rid, 'edit');

        $form_fields_expected = Form::find($fid)->fields()->get();

        foreach($form_fields_expected as $expected_field) {
            $key = $expected_field->flid;

            if($request->has($key))
                $value = $request->input($key);
            else
                $value = null;

            $field = FieldController::getField($key);
            //TODO::modular
            if($field->type=='Text'){
                //we need to check if the field exist first
                $tf  = TextField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                if(!is_null($tf) && !is_null($value)){
                    $tf->text = $value;
                    $tf->save();
                }
                elseif(!is_null($tf) && is_null($value)){
                    $tf->delete();
                }
                elseif(is_null($tf) && !empty($value)){
                    $tf = new TextField();
                    $tf->flid = $field->flid;
                    $tf->rid = $record->rid;
                    $tf->fid = $record->fid;
                    $tf->text = $value;
                    $tf->save();
                }
            } else if($field->type=='Rich Text'){
                //we need to check if the field exist first
                $rtf = RichTextField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                if(!is_null($rtf) && !is_null($value)){
                    $rtf->rawtext = $value;
                    $rtf->save();
                }elseif(!is_null($rtf) && is_null($value)){
                    $rtf->delete();
                }
                else {
                    if(!empty($value)) {
                        $rtf = new RichTextField();
                        $rtf->flid = $field->flid;
                        $rtf->rid = $record->rid;
                        $rtf->fid = $record->fid;
                        $rtf->rawtext = $value;
                        $rtf->save();
                    }
                }
            } else if($field->type=='Number'){
                //we need to check if the field exist first
                $nf = NumberField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                if(!is_null($nf) && !is_null($value)){
                    $nf->number = $value;
                    $nf->save();
                }
                else if(!is_null($nf) && is_null($value)){
                    $nf->delete();
                }
                else {
                    if (!empty($value)) {
                        $nf = new NumberField();
                        $nf->flid = $field->flid;
                        $nf->rid = $record->rid;
                        $nf->fid = $record->fid;
                        $nf->number = $value;
                        $nf->save();
                    }
                }
            } else if($field->type=='List'){
                //we need to check if the field exist first
                $lf = ListField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                if(!is_null($lf) && !is_null($value)){
                    $lf->option = $value;
                    $lf->save();
                }
                else if(!is_null($lf) && is_null($value)){
                    $lf->delete();
                }
                else {
                    if (!empty($value)) {
                        $lf = new ListField();
                        $lf->flid = $field->flid;
                        $lf->rid = $record->rid;
                        $lf->fid = $record->fid;
                        $lf->option = $value;
                        $lf->save();
                    }
                }
            } else if($field->type=='Multi-Select List'){
                //we need to check if the field exist first
                $mslf = MultiSelectListField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();

                if(!is_null($mslf) && !is_null($value)){
                   // $mslf = MultiSelectListField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                    $mslf->options = implode("[!]",$value);
                    $mslf->save();
                }
                elseif(!is_null($mslf) && is_null($value)){
                    $mslf->delete();
                }
                elseif(is_null($mslf) && !is_null($value)){
                    $mslf = new MultiSelectListField();
                    $mslf->flid = $field->flid;
                    $mslf->rid = $record->rid;
                    $mslf->fid = $record->fid;
                    $mslf->options = implode("[!]",$value);
                    $mslf->save();
                }
            } else if($field->type=='Generated List'){
                //we need to check if the field exist first
                $glf = GeneratedListField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                if(!is_null($glf) && !is_null($value)){
                    //$glf = GeneratedListField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                    $glf->options = implode("[!]",$value);
                    $glf->save();
                }elseif(!is_null($glf) && is_null($value)){
                    $glf->delete();
                }
                elseif(is_null($glf) && !is_null($value)) {
                    $glf = new GeneratedListField();
                    $glf->flid = $field->flid;
                    $glf->rid = $record->rid;
                    $glf->fid = $record->fid;
                    $glf->options = implode("[!]",$value);
                    $glf->save();
                }
            } else if($field->type=='Combo List'){
                //we need to check if the field exist first
                $clf = ComboListField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                if(!is_null($clf) && !is_null($request->input($field->flid.'_val'))){
                    $type_1 = ComboListField::getComboFieldType($field, 'one');
                    $type_2 = ComboListField::getComboFieldType($field, 'two');

                    $clf->updateData($_REQUEST[$field->flid.'_val'], $type_1, $type_2);
                }elseif(!is_null($clf) && is_null($request->input($field->flid.'_val'))){
                    $clf->delete();
                    $clf->deleteData();
                }
                elseif(is_null($clf) && !is_null($request->input($field->flid.'_val'))) {
                    $clf = new ComboListField();
                    $clf->flid = $field->flid;
                    $clf->rid = $record->rid;
                    $clf->fid = $record->fid;
                    $clf->save();

                    $type_1 = ComboListField::getComboFieldType($field, 'one');
                    $type_2 = ComboListField::getComboFieldType($field, 'two');

                    $clf->addData($_REQUEST[$field->flid.'_val'], $type_1, $type_2);
                }
            }else if($field->type=='Date'){
                //we need to check if the field exist first
                $df = DateField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                if(!is_null($df) && !(empty($request->input('month_'.$key)) && empty($request->input('day_'.$key)) && empty($request->input('year_'.$key)))){
                    $df->circa = $request->input('circa_'.$field->flid, '');
                    $df->month = $request->input('month_'.$field->flid);
                    $df->day = $request->input('day_'.$field->flid);
                    $df->year = $request->input('year_'.$field->flid);
                    $df->era = $request->input('era_'.$field->flid, 'CE');
                    $df->save();
                }
                elseif(!is_null($df) && (empty($request->input('month_'.$key)) && empty($request->input('day_'.$key)) && empty($request->input('year_'.$key)))){
                    $df->delete();
                }
                elseif(is_null($df) && !(empty($request->input('month_'.$key)) && empty($request->input('day_'.$key)) && empty($request->input('year_'.$key)))){
                    $df = new DateField();
                    $df->flid = $field->flid;
                    $df->rid = $record->rid;
                    $df->fid = $record->fid;
                    $df->circa = $request->input('circa_'.$field->flid, '');
                    $df->month = $request->input('month_'.$field->flid);
                    $df->day = $request->input('day_'.$field->flid);
                    $df->year = $request->input('year_'.$field->flid);
                    $df->era = $request->input('era_'.$field->flid, 'CE');
                    $df->save();
                }
            } else if($field->type=='Schedule'){
                //we need to check if the field exist first
                $sf = ScheduleField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                if(!is_null($sf) && !is_null($value)){
                    $sf->updateEvents($value);
                }
                elseif(!is_null($sf) && is_null($value)){
                    $sf->delete();
                    $sf->deleteEvents();
                }
                elseif(is_null($sf) && !is_null($value)) {
                    $sf = new ScheduleField();
                    $sf->flid = $field->flid;
                    $sf->rid = $record->rid;
                    $sf->fid = $record->fid;
                    $sf->save();

                    $sf->addEvents($value);
                }
            } else if($field->type=='Geolocator'){
                //we need to check if the field exist first
                $gf = GeolocatorField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                if(!is_null($gf) && !is_null($value)){
                    $gf->updateLocations($value);
                }
                elseif(!is_null($gf) && is_null($value)){
                    $gf->delete();
                    $gf->deleteLocations();
                }
                elseif(is_null($gf) && !is_null($value)) {
                    $gf = new GeolocatorField();
                    $gf->flid = $field->flid;
                    $gf->rid = $record->rid;
                    $gf->fid = $record->fid;
                    $gf->save();

                    $gf->addLocations($value);
                }
            } else if($field->type=='Documents'
                    && (DocumentsField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first() != null
                    | glob(env('BASE_PATH').'storage/app/tmpFiles/'.$value.'/*.*') != false)){


                $doc_files_exist = false; // if this remains false, then the files were deleted and row should be removed from table


                //we need to check if the field exist first
                if(DocumentsField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first() != null){
                    $df = DocumentsField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                }else {
                    $df = new DocumentsField();
                    $df->flid = $field->flid;
                    $df->rid = $record->rid;
                    $df->fid = $record->fid;
                    $newPath = env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid;

                    if(!file_exists($newPath)) {
                        mkdir($newPath, 0775, true);
                    }
                }
                //clear the old files before moving the update over
                //we only want to remove files that are being replaced by new versions
                //we keep old files around for revision purposes
                $newNames = array();
                //scan the tmpFile as these will be the "new ones"
                if(file_exists(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value)) {
                    foreach (new \DirectoryIterator(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value) as $file) {
                        array_push($newNames,$file->getFilename());
                    }
                }
                //actually clear them
                foreach (new \DirectoryIterator(env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid) as $file) {
                    if ($file->isFile() and in_array($file->getFilename(),$newNames)) {
                        unlink(env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid.'/'.$file->getFilename());
                    }
                }
                //build new stuff
                $infoString = '';
                $infoArray = array();
                if(file_exists(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value)) {
                    $types = DocumentsField::getMimeTypes();
                    foreach (new \DirectoryIterator(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value) as $file) {
                        if ($file->isFile()) {
                            if(!array_key_exists($file->getExtension(),$types))
                                $type = 'application/octet-stream';
                            else
                                $type =  $types[$file->getExtension()];
                            $info = '[Name]' . $file->getFilename() . '[Name][Size]' . $file->getSize() . '[Size][Type]' . $type . '[Type]';
                            $infoArray[$file->getFilename()] = $info;
                            copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/' . $file->getFilename(),
                                env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid . '/' . $file->getFilename());

                            $doc_files_exist = true;
                        }
                    }
                    foreach($_REQUEST['file'.$field->flid] as $fName){
                        if($fName!=''){
                            if ($infoString == '') {
                                $infoString = $infoArray[$fName];
                            } else {
                                $infoString .= '[!]' . $infoArray[$fName];
                            }
                        }
                    }
                }
                $df->documents = $infoString;
                $df->save();

                if(!$doc_files_exist){
                    $df->delete();
                }


            } else if($field->type=='Gallery'
                    && (GalleryField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first() != null
                    | glob(env('BASE_PATH').'storage/app/tmpFiles/'.$value.'/*.*') != false)){


                $gal_files_exist = false; // if this remains false, then the files were deleted and row should be removed from table
                $gfcount = 0;


                //we need to check if the field exist first
                if(GalleryField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first() != null){
                    $gf = GalleryField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                }else {
                    $gf = new GalleryField();
                    $gf->flid = $field->flid;
                    $gf->rid = $record->rid;
                    $gf->fid = $record->fid;
                    $newPath = env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid;

                    if(!file_exists($newPath)) {
                        mkdir($newPath, 0775, true);
                        mkdir($newPath.'/thumbnail',0775,true);
                        mkdir($newPath.'/medium',0775,true);

                    }
                }
                //clear the old files before moving the update over
                //we only want to remove files that are being replaced by new versions
                //we keep old files around for revision purposes
                $newNames = array();
                //scan the tmpFile as these will be the "new ones"
                if(file_exists(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value)) {
                    foreach (new \DirectoryIterator(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value) as $file) {
                        array_push($newNames,$file->getFilename());
                    }
                }
                //actually clear them
                foreach (new \DirectoryIterator(env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid) as $file) {
                    if ($file->isFile() and in_array($file->getFilename(),$newNames)) {
                        unlink(env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid.'/'.$file->getFilename());
                        unlink(env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid.'/thumbnail/'.$file->getFilename());
                        unlink(env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid.'/medium/'.$file->getFilename());
                    }
                }
                //build new stuff
                $infoString = '';
                $infoArray = array();
                if(file_exists(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value)) {
                    $types = DocumentsField::getMimeTypes();
                    foreach (new \DirectoryIterator(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value) as $file) {
                        if ($file->isFile()) {
                            if(!array_key_exists($file->getExtension(),$types))
                                $type = 'application/octet-stream';
                            else
                                $type =  $types[$file->getExtension()];
                            $info = '[Name]' . $file->getFilename() . '[Name][Size]' . $file->getSize() . '[Size][Type]' . $type . '[Type]';
                            $infoArray[$file->getFilename()] = $info;
                            copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/' . $file->getFilename(),
                                env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid . '/' . $file->getFilename());
                            copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/thumbnail/' . $file->getFilename(),
                                env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid . '/thumbnail/' . $file->getFilename());
                            copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/medium/' . $file->getFilename(),
                                env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid . '/medium/' . $file->getFilename());

                            $gal_files_exist = true;
                            //$gfcount += 1;
                        }
                    }
                    foreach($_REQUEST['file'.$field->flid] as $fName){
                        if($fName!=''){
                            if ($infoString == '') {
                                $infoString = $infoArray[$fName];
                            } else {
                                $infoString .= '[!]' . $infoArray[$fName];
                            }
                        }
                    }
                }
                $gf->images = $infoString;
                $gf->save();


                if(!$gal_files_exist){
                    $gf->delete();
                }


            } else if($field->type=='Playlist'
                && (PlaylistField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first() != null
                    | glob(env('BASE_PATH').'storage/app/tmpFiles/'.$value.'/*.*') != false)){

                $pla_files_exist = false; // if this remains false, then the files were deleted and row should be removed from table

                //we need to check if the field exist first
                if(PlaylistField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first() != null){
                    $pf = PlaylistField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                }else {
                    $pf = new PlaylistField();
                    $pf->flid = $field->flid;
                    $pf->rid = $record->rid;
                    $pf->fid = $record->fid;
                    $newPath = env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid;
                    if(!file_exists($newPath)) {
                        mkdir($newPath, 0775, true);
                    }
                }
                //clear the old files before moving the update over
                //we only want to remove files that are being replaced by new versions
                //we keep old files around for revision purposes
                $newNames = array();
                //scan the tmpFile as these will be the "new ones"
                if(file_exists(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value)) {
                    foreach (new \DirectoryIterator(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value) as $file) {
                        array_push($newNames,$file->getFilename());
                    }
                }
                //actually clear them
                foreach (new \DirectoryIterator(env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid) as $file) {
                    if ($file->isFile() and in_array($file->getFilename(),$newNames)) {
                        unlink(env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid.'/'.$file->getFilename());
                    }
                }
                //build new stuff
                $infoString = '';
                $infoArray = array();
                if(file_exists(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value)) {
                    $types = DocumentsField::getMimeTypes();
                    foreach (new \DirectoryIterator(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value) as $file) {
                        if ($file->isFile()) {
                            if(!array_key_exists($file->getExtension(),$types))
                                $type = 'application/octet-stream';
                            else
                                $type =  $types[$file->getExtension()];
                            $info = '[Name]' . $file->getFilename() . '[Name][Size]' . $file->getSize() . '[Size][Type]' . $type . '[Type]';
                            $infoArray[$file->getFilename()] = $info;
                            copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/' . $file->getFilename(),
                                env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid . '/' . $file->getFilename());
                            $pla_files_exist = true;
                        }
                    }
                    foreach($_REQUEST['file'.$field->flid] as $fName){
                        if($fName!=''){
                            if ($infoString == '') {
                                $infoString = $infoArray[$fName];
                            } else {
                                $infoString .= '[!]' . $infoArray[$fName];
                            }
                        }
                    }
                }
                $pf->audio = $infoString;
                $pf->save();

                if(!$pla_files_exist){
                    $pf->delete();
                    flash()->overlay(trans('controller_record.nofile'));
                }

            } else if($field->type=='Video'
                && (VideoField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first() != null
                    | glob(env('BASE_PATH').'storage/app/tmpFiles/'.$value.'/*.*') != false)){

                $vid_files_exist = false; // if this remains false, then the files were deleted and row should be removed from table

                //we need to check if the field exist first
                if(VideoField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first() != null){
                    $vf = VideoField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                }else {
                    $vf = new VideoField();
                    $vf->flid = $field->flid;
                    $vf->rid = $record->rid;
                    $vf->fid = $record->fid;
                    $newPath = env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid;
                    if(!file_exists($newPath)) {
                        mkdir($newPath, 0775, true);
                    }
                }
                //clear the old files before moving the update over
                //we only want to remove files that are being replaced by new versions
                //we keep old files around for revision purposes
                $newNames = array();
                //scan the tmpFile as these will be the "new ones"
                if(file_exists(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value)) {
                    foreach (new \DirectoryIterator(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value) as $file) {
                        array_push($newNames,$file->getFilename());
                    }
                }
                //actually clear them
                foreach (new \DirectoryIterator(env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid) as $file) {
                    if ($file->isFile() and in_array($file->getFilename(),$newNames)) {
                        unlink(env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid.'/'.$file->getFilename());
                    }
                }
                //build new stuff
                $infoString = '';
                $infoArray = array();
                if(file_exists(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value)) {
                    $types = DocumentsField::getMimeTypes();
                    foreach (new \DirectoryIterator(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value) as $file) {
                        if ($file->isFile()) {
                            if(!array_key_exists($file->getExtension(),$types))
                                $type = 'application/octet-stream';
                            else
                                $type =  $types[$file->getExtension()];
                            $info = '[Name]' . $file->getFilename() . '[Name][Size]' . $file->getSize() . '[Size][Type]' . $type . '[Type]';
                            $infoArray[$file->getFilename()] = $info;
                            copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/' . $file->getFilename(),
                                env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid . '/' . $file->getFilename());
                                $vid_files_exist = true;
                        }

                    }
                    foreach($_REQUEST['file'.$field->flid] as $fName){
                        if($fName!=''){
                            if ($infoString == '') {
                                $infoString = $infoArray[$fName];
                            } else {
                                $infoString .= '[!]' . $infoArray[$fName];
                            }
                        }
                    }
                }
                $vf->video = $infoString;
                $vf->save();

                if(!$vid_files_exist){
                    $vf->delete();
                }

            } else if($field->type=='3D-Model'
                && (ModelField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first() != null
                    | glob(env('BASE_PATH').'storage/app/tmpFiles/'.$value.'/*.*') != false)){

                $mod_files_exist = false; // if this remains false, then the files were deleted and row should be removed from table

                //we need to check if the field exist first
                if(Modelfield::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first() != null){
                    $mf = Modelfield::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                }else {
                    $mf = new Modelfield();
                    $mf->flid = $field->flid;
                    $mf->rid = $record->rid;
                    $mf->fid = $record->fid;
                    $newPath = env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid;
                    if(!file_exists($newPath)) {
                        mkdir($newPath, 0775, true);
                    }
                }
                //clear the old files before moving the update over
                //we only want to remove files that are being replaced by new versions
                //we keep old files around for revision purposes
                $newNames = array();
                //scan the tmpFile as these will be the "new ones"
                if(file_exists(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value)) {
                    foreach (new \DirectoryIterator(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value) as $file) {
                        array_push($newNames,$file->getFilename());
                    }
                }
                //actually clear them
                foreach (new \DirectoryIterator(env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid) as $file) {
                    if ($file->isFile() and in_array($file->getFilename(),$newNames)) {
                        unlink(env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid.'/'.$file->getFilename());
                    }
                }
                //build new stuff
                $infoString = '';
                $infoArray = array();
                if(file_exists(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value)) {
                    $types = DocumentsField::getMimeTypes();
                    foreach (new \DirectoryIterator(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value) as $file) {
                        if ($file->isFile()) {
                            if(!array_key_exists($file->getExtension(),$types))
                                $type = 'application/octet-stream';
                            else
                                $type =  $types[$file->getExtension()];
                            $info = '[Name]' . $file->getFilename() . '[Name][Size]' . $file->getSize() . '[Size][Type]' . $type . '[Type]';
                            $infoArray[$file->getFilename()] = $info;
                            copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/' . $file->getFilename(),
                                env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid.'/r'.$record->rid.'/fl'.$field->flid . '/' . $file->getFilename());
                             $mod_files_exist = true;
                        }
                    }
                    foreach($_REQUEST['file'.$field->flid] as $fName){
                        if($fName!=''){
                            if ($infoString == '') {
                                $infoString = $infoArray[$fName];
                            } else {
                                $infoString .= '[!]' . $infoArray[$fName];
                            }
                        }
                    }
                }
                $mf->model = $infoString;
                $mf->save();

                if(!$mod_files_exist){
                    $mf->delete();
                }
            } else if($field->type=='Associator'){

                //we need to check if the field exist first
                $af = AssociatorField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                if(!is_null($af) && !is_null($value)){
                    $af->updateRecords($value);
                }
                elseif(!is_null($af) && is_null($value)){
                    $af->delete();
                    $af->deleteRecords();
                }
                elseif(is_null($af) && !is_null($value)) {
                    $af = new AssociatorField();
                    $af->flid = $field->flid;
                    $af->rid = $record->rid;
                    $af->fid = $record->fid;
                    $af->save();

                    $af->addRecords($value);
                }
            }
        }

        $revision->oldData = RevisionController::buildDataArray($record);
        $revision->save();

        RecordPresetController::updateIfExists($record->rid);

        if(!$request->api) {
            flash()->overlay(trans('controller_record.updated'), trans('controller_record.goodjob'));

            return redirect('projects/' . $pid . '/forms/' . $fid . '/records/' . $rid);
        }
	}


    /**
     * Delete a record from Kora3.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @param  bool $mass - Is deleting mass records
     */
    public function destroy($pid, $fid, $rid, $mass = false) {
        if(!self::validProjFormRecord($pid, $fid, $rid)) {
            return redirect('projects/'.$pid.'forms/');
        }

        if(!\Auth::user()->isOwner(self::getRecord($rid)) && !self::checkPermissions($fid, 'destroy') ) {
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        $record = self::getRecord($rid);

        if(!$mass)
            RevisionController::storeRevision($record->rid, 'delete');

        $record->delete();

        flash()->overlay(trans('controller_record.deleted'), trans('controller_record.goodjob'));
	}

    /**
     * Delete all records from a form.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     */
    public function deleteAllRecords($pid, $fid) {
        $form = FormController::getForm($fid);

        if(!\Auth::user()->isFormAdmin($form)) {
            flash()->overlay(trans('controller_record.noperm'), trans('controller_record.whoops'));
        } else {
            Record::where("fid", "=", $fid)->delete();

            flash()->overlay(trans('controller_record.alldelete'), trans('controller_record.success'));
        }
    }

    /**
     * Gets the view for the record import process.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
    public function importRecordsView($pid,$fid) {
        if(!FormController::validProjForm($pid,$fid)) {
            return redirect('projects');
        }

        if(!self::checkPermissions($fid, 'ingest')) {
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        $form = FormController::getForm($fid);

        return view('records.import',compact('form','pid','fid'));
    }

    /**
     * Get a record back by RID.
     *
     * @param  int $rid - Record ID
     * @return Record - Requested record
     */
    public static function getRecord($rid) {
        $record = Record::where('rid', '=', $rid)->first();

        return $record;
    }

    /**
     * Get a record back by KID.
     *
     * @param  int $kid - Kora ID
     * @return Record - Requested record
     */
    public static function getRecordByKID($kid) {
        $record = Record::where('kid', '=', $kid)->first();

        return $record;
    }

    /**
     * Determines if record exists.
     *
     * @param  int $rid - Record ID
     * @return bool - Does exist
     */
    public static function exists($rid) {
        return !is_null(Record::where('rid','=',$rid)->first());
    }

    /**
     * Determines if the project, form, record ID combos are valid.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @return bool - Valid pairs
     */
    public static function validProjFormRecord($pid, $fid, $rid) {
        $record = self::getRecord($rid);
        $form = FormController::getForm($fid);
        $proj = ProjectController::getProject($pid);

        if(!FormController::validProjForm($pid, $fid))
            return false;
        if(is_null($record) || is_null($form) || is_null($proj))
            return false;
        else if ($record->fid == $form->fid)
            return true;
        else
            return false;
    }

    /**
     * Checks users abilities to create, edit, delete records.
     *
     * @param  int $fid - Form ID
     * @param  string $permission - Permission to search for
     * @return bool - Has permissions
     */
    private function checkPermissions($fid, $permission='') {
        switch($permission) {
            case 'ingest':
                if(!(\Auth::user()->canIngestRecords(FormController::getForm($fid)))) {
                    flash()->overlay(trans('controller_record.createper'), trans('controller_record.whoops'));
                    return false;
                }
                return true;
            case 'modify':
                if(!(\Auth::user()->canModifyRecords(FormController::getForm($fid)))) {
                    flash()->overlay(trans('controller_record.editper'), trans('controller_record.whoops'));
                    return false;
                }
                return true;
            case 'destroy':
                if(!(\Auth::user()->canDestroyRecords(FormController::getForm($fid)))) {
                    flash()->overlay(trans('controller_record.deleteper'), trans('controller_record.whoops'));
                    return false;
                }
                return true;
            default: // "Read Only"
                if(!(\Auth::user()->inAFormGroup(FormController::getForm($fid)))) {
                    flash()->overlay(trans('controller_record.viewper'), trans('controller_record.whoops'));
                    return false;
                }
                return true;
        }
    }

    /**
     * Get collective file size of the record files in a form.
     *
     * @param  int $fid - Form ID
     * @return string - File size
     */
    public function getFormFilesize($fid) {
        $form = FormController::getForm($fid);
        $pid = $form->pid;
        $filesize = 0;

        $basedir = env( "BASE_PATH" ) . "storage/app/files/p".$pid."/f".$fid;
        $filesize += self::dirCrawl($basedir);

        $filesize = self::fileSizeConvert($filesize);

        return $filesize;
    }

    /**
     * Scans a form's file directory to get the total filesize.
     *
     * @param  string $dir - Directory to scan
     * @return int - Size in bytes
     */
    private function dirCrawl($dir) {
        $filesize = 0;

        if(file_exists($dir)) {
            foreach(new \DirectoryIterator($dir) as $file) {
                if($file->isDir() && $file->getFilename() != '.' && $file->getFilename() != '..') {
                    // If the file is a valid directory, call dirCrawl and access its child directory(s)
                    $filesize += self::dirCrawl($file->getPathname());
                } else if($file->isFile()) {
                    // If the file is indeed a file, add its size
                    $filesize += $file->getSize();
                }
            }
        }

        return $filesize;
    }

    /**
     * Converts the directory size in bytes to the most readable form.
     *
     * @param  int $bytes - Size in bytes
     * @return string - The readable size value
     */
    private function fileSizeConvert($bytes) {
        $result = "0 B";
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

        foreach($arBytes as $arItem) {
            if($bytes >= $arItem["VALUE"]) {
                $result = $bytes / $arItem["VALUE"];
                $result = strval(round($result, 2))." ".$arItem["UNIT"];
                break;
            }
        }
        return $result;
    }

    /**
     * Gets the view for mass assigning records.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
    public function showMassAssignmentView($pid,$fid) {
        if(!FormController::validProjForm($pid,$fid)) {
            return redirect('projects');
        }

        if(!$this->checkPermissions($fid,'modify')) {
            return redirect()->back();
        }

        $form = FormController::getForm($fid);
        $all_fields = $form->fields()->get();
        $fields = new Collection();
        foreach($all_fields as $field) {
            $type = $field->type;
            if($type == "Documents" || $type == "Gallery" || $type == "Playlist" || $type == "3D-Model" || $type == 'Video')
                continue;
            else
                $fields->push($field);
        }
        return view('records.mass-assignment',compact('form','fields','pid','fid'));
    }

    /**
     * Mass assigns a value to a field in all records.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return Redirect
     */
    public function massAssignRecords($pid, $fid, Request $request) {
        if(!$this->checkPermissions($fid,'modify')) {
            return redirect()->back();
        }

        $flid = $request->input("field_selection");
        if(!is_numeric($flid)) {
            flash()->overlay(trans('controller_record.notvalid'));
            return redirect()->back();
        }

        if($request->has($flid)) {
            $form_field_value = $request->input($flid); //Note this only works when there is one form element being submitted, so if you have more, check Date
        } else {
            flash()->overlay(trans('controller_record.provide'),trans('controller_record.whoops'));
            return redirect()->back();
        }

        if ($request->has("overwrite"))
            $overwrite = $request->input("overwrite"); //Overwrite field in all records, even if it has data
        else
            $overwrite = 0;

        $field = Field::find($flid);
        foreach(Form::find($fid)->records()->get() as $record) {
            //TODO::modular
            if ($field->type == "Text") {
                $matching_record_fields = $record->textfields()->where("flid", '=', $flid)->get();
                $record->updated_at = Carbon::now();
                $record->save();
                if ($matching_record_fields->count() > 0) {
                    $textfield = $matching_record_fields->first();
                    if ($overwrite == true || $textfield->text == "" || is_null($textfield->text)) {
                        $revision = RevisionController::storeRevision($record->rid, 'edit');
                        $textfield->text = $form_field_value;
                        $textfield->save();
                        $revision->oldData = RevisionController::buildDataArray($record);
                        $revision->save();
                    } else {
                        continue;
                    }
                } else {
                    $tf = new TextField();
                    $revision = RevisionController::storeRevision($record->rid, 'edit');
                    $tf->flid = $field->flid;
                    $tf->rid = $record->rid;
                    $tf->text = $form_field_value;
                    $tf->save();
                    $revision->oldData = RevisionController::buildDataArray($record);
                    $revision->save();
                }
            } elseif ($field->type == "Rich Text") {
                $matching_record_fields = $record->richtextfields()->where("flid", '=', $flid)->get();
                $record->updated_at = Carbon::now();
                $record->save();
                if ($matching_record_fields->count() > 0) {
                    $richtextfield = $matching_record_fields->first();
                    if ($overwrite == true || $richtextfield->rawtext == "" || is_null($richtextfield->rawtext)) {
                        $revision = RevisionController::storeRevision($record->rid, 'edit');
                        $richtextfield->rawtext = $form_field_value;
                        $richtextfield->save();
                        $revision->oldData = RevisionController::buildDataArray($record);
                        $revision->save();
                    } else {
                        continue;
                    }
                } else {
                    $rtf = new RichTextField();
                    $revision = RevisionController::storeRevision($record->rid, 'edit');
                    $rtf->flid = $field->flid;
                    $rtf->rid = $record->rid;
                    $rtf->rawtext = $form_field_value;
                    $rtf->save();
                    $revision->oldData = RevisionController::buildDataArray($record);
                    $revision->save();
                }
            } elseif ($field->type == "Number") {
                $matching_record_fields = $record->numberfields()->where("flid", '=', $flid)->get();
                $record->updated_at = Carbon::now();
                $record->save();
                if ($matching_record_fields->count() > 0) {
                    $numberfield = $matching_record_fields->first();
                    if ($overwrite == true || $numberfield->number == "" || is_null($numberfield->number)) {
                        $revision = RevisionController::storeRevision($record->rid, 'edit');
                        $numberfield->number = $form_field_value;
                        $numberfield->save();
                        $revision->oldData = RevisionController::buildDataArray($record);
                        $revision->save();
                    } else {
                        continue;
                    }
                } else {
                    $nf = new NumberField();
                    $revision = RevisionController::storeRevision($record->rid, 'edit');
                    $nf->flid = $field->flid;
                    $nf->rid = $record->rid;
                    $nf->number = $form_field_value;
                    $nf->save();
                    $revision->oldData = RevisionController::buildDataArray($record);
                    $revision->save();
                }
            } elseif ($field->type == "List") {
                $matching_record_fields = $record->listfields()->where("flid", '=', $flid)->get();
                $record->updated_at = Carbon::now();
                $record->save();
                if ($matching_record_fields->count() > 0) {
                    $listfield = $matching_record_fields->first();
                    if ($overwrite == true || $listfield->option == "" || is_null($listfield->option)) {
                        $revision = RevisionController::storeRevision($record->rid, 'edit');
                        $listfield->option = $form_field_value;
                        $listfield->save();
                        $revision->oldData = RevisionController::buildDataArray($record);
                        $revision->save();
                    } else {
                        continue;
                    }
                } else {
                    $lf = new ListField();
                    $revision = RevisionController::storeRevision($record->rid, 'edit');
                    $lf->flid = $field->flid;
                    $lf->rid = $record->rid;
                    $lf->option = $form_field_value;
                    $lf->save();
                    $revision->oldData = RevisionController::buildDataArray($record);
                    $revision->save();
                }
            } elseif ($field->type == "Multi-Select List") {
                $matching_record_fields = $record->multiselectlistfields()->where("flid", '=', $flid)->get();
                $record->updated_at = Carbon::now();
                $record->save();
                if ($matching_record_fields->count() > 0) {
                    $multiselectlistfield = $matching_record_fields->first();
                    if ($overwrite == true || $multiselectlistfield->options == "" || is_null($multiselectlistfield->options)) {
                        $revision = RevisionController::storeRevision($record->rid, 'edit');
                        $multiselectlistfield->options = implode("[!]", $form_field_value);
                        $multiselectlistfield->save();
                        $revision->oldData = RevisionController::buildDataArray($record);
                        $revision->save();
                    } else {
                        continue;
                    }
                } else {
                    $mslf = new MultiSelectListField();
                    $revision = RevisionController::storeRevision($record->rid, 'edit');
                    $mslf->flid = $field->flid;
                    $mslf->rid = $record->rid;
                    $mslf->options = implode("[!]", $form_field_value);
                    $mslf->save();
                    $revision->oldData = RevisionController::buildDataArray($record);
                    $revision->save();
                }
            } elseif ($field->type == "Generated List") {
                $matching_record_fields = $record->generatedlistfields()->where("flid", '=', $flid)->get();
                $record->updated_at = Carbon::now();
                $record->save();
                if ($matching_record_fields->count() > 0) {
                    $generatedlistfield = $matching_record_fields->first();
                    if ($overwrite == true || $generatedlistfield->options == "" || is_null($generatedlistfield->options)) {
                        $revision = RevisionController::storeRevision($record->rid, 'edit');
                        $generatedlistfield->options = implode("[!]", $form_field_value);
                        $generatedlistfield->save();
                        $revision->oldData = RevisionController::buildDataArray($record);
                        $revision->save();
                    } else {
                        continue;
                    }
                } else {
                    $glf = new GeneratedListField();
                    $revision = RevisionController::storeRevision($record->rid, 'edit');
                    $glf->flid = $field->flid;
                    $glf->rid = $record->rid;
                    $glf->options = implode("[!]", $form_field_value);
                    $glf->save();
                    $revision->oldData = RevisionController::buildDataArray($record);
                    $revision->save();
                }
            } elseif($field->type == "Combo List"){
                $matching_record_fields = $record->combolistfields()->where('flid','=',$flid)->get();
                $record->updated_at = Carbon::now();
                $record->save();

                if($matching_record_fields->count() > 0){
                    $combolistfield = $matching_record_fields->first();
                    if($overwrite == true || $combolistfield->options == "" || is_null($combolistfield->options)){
                        $revision = RevisionController::storeRevision($record->rid,'edit');

                        $combolistfield->updateData($_REQUEST[$flid . "_val"]);

                        $revision->oldData = RevisionController::buildDataArray($record);
                        $revision->save();
                    }
                    else{
                        continue;
                    }
                } else{
                    $clf = new ComboListField();
                    $revision = RevisionController::storeRevision($record->rid,'edit');
                    $clf->flid = $flid;
                    $clf->rid = $record->rid;
                    $clf->options = $_REQUEST[$flid.'_val'][0];
                    for($i=1;$i<sizeof($_REQUEST[$flid.'_val']);$i++){
                        $clf->options .= '[!val!]'.$_REQUEST[$flid.'_val'][$i];
                    }
                    $clf->save();
                    $revision->oldData = RevisionController::buildDataArray($record);
                    $revision->save();
                }


            }
            elseif ($field->type == "Date") {
                $matching_record_fields = $record->datefields()->where("flid", '=', $flid)->get();
                $record->updated_at = Carbon::now();
                $record->save();
                if ($matching_record_fields->count() > 0) {
                    $datefield = $matching_record_fields->first();
                    if ($overwrite == true || $datefield->month == "" || is_null($datefield->month)) {
                        $revision = RevisionController::storeRevision($record->rid, 'edit');
                        $datefield->circa = $request->input('circa_' . $flid, '');
                        $datefield->month = $request->input('month_' . $flid);
                        $datefield->day = $request->input('day_' . $flid);
                        $datefield->year = $request->input('year_' . $flid);
                        $datefield->era = $request->input('era_' . $flid, 'CE');
                        $datefield->save();
                        $revision->oldData = RevisionController::buildDataArray($record);
                        $revision->save();
                    } else {
                        continue;
                    }
                } else {
                    $df = new DateField();
                    $revision = RevisionController::storeRevision($record->rid, 'edit');
                    $df->circa = $request->input('circa_' . $flid, '');
                    $df->month = $request->input('month_' . $flid);
                    $df->day = $request->input('day_' . $flid);
                    $df->year = $request->input('year_' . $flid);
                    $df->era = $request->input('era_' . $flid, 'CE');
                    $df->rid = $record->rid;
                    $df->flid = $flid;
                    $df->save();
                    $revision->oldData = RevisionController::buildDataArray($record);
                    $revision->save();
                }
            } elseif ($field->type == "Schedule") {
                $matching_record_fields = $record->schedulefields()->where("flid", '=', $flid)->get();
                $record->updated_at = Carbon::now();
                $record->save();
                if ($matching_record_fields->count() > 0) {
                    $schedulefield = $matching_record_fields->first();
                    if ($overwrite == true || $schedulefield->hasEvents()) {
                        $revision = RevisionController::storeRevision($record->rid, 'edit');
                        $schedulefield->updateEvents($form_field_value);
                        $schedulefield->save();
                        $revision->oldData = RevisionController::buildDataArray($record);
                        $revision->save();
                    } else {
                        continue;
                    }
                } else {
                    $sf = new ScheduleField();
                    $revision = RevisionController::storeRevision($record->rid, 'edit');
                    $sf->flid = $field->flid;
                    $sf->rid = $record->rid;
                    $sf->save();

                    $sf->addEvents($form_field_value);

                    $revision->oldData = RevisionController::buildDataArray($record);
                    $revision->save();
                }
            }
            elseif($field->type == "Geolocator"){
                $matching_record_fields = $record->geolocatorfields()->where("flid", '=', $flid)->get();
                $record->updated_at = Carbon::now();
                $record->save();
                if ($matching_record_fields->count() > 0) {
                    $geolocatorfield = $matching_record_fields->first();
                    if ($overwrite == true || ! $geolocatorfield->hasLocations()) {
                        $revision = RevisionController::storeRevision($record->rid, 'edit');
                        $geolocatorfield->updateLocations($form_field_value);
                        $revision->oldData = RevisionController::buildDataArray($record);
                        $revision->save();
                    } else {
                        continue;
                    }
                } else {
                    $gf = new GeolocatorField();
                    $revision = RevisionController::storeRevision($record->rid, 'edit');
                    $gf->flid = $field->flid;
                    $gf->rid = $record->rid;
                    $gf->save();

                    $gf->addLocations($form_field_value);

                    $revision->oldData = RevisionController::buildDataArray($record);
                    $revision->save();
                }
            }
        }

        flash()->overlay(trans('controller_record.recupdate'),trans('controller_record.goodjob'));
        return redirect()->action('RecordController@index',compact('pid','fid'));
    }

    /**
     * Creates several test records in a form for testing purposes.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return Redirect
     */
    public function createTest($pid, $fid, Request $request) {
        $numRecs = $request->test_records_num;

        if(!FormController::validProjForm($pid,$fid)) {
            return redirect('projects');
        }

        $form = FormController::getForm($fid);
        $fields = $form->fields()->get();

        for($i = 0; $i < $numRecs ; $i++) {
            $record = new Record();
            $record->pid = $pid;
            $record->fid = $fid;
            $record->owner = Auth::user()->id;
            $record->save(); //need to save to create rid needed to make kid
            $record->kid = $pid . '-' . $fid . '-' . $record->rid;
            $record->save();

            foreach($fields as $field) {
                //TODO::modular
                if ($field->type == 'Text') {
                    $tf = new TextField();
                    $tf->flid = $field->flid;
                    $tf->rid = $record->rid;
                    $tf->fid = $fid;
                    $tf->text = 'K3TR: This is a test record';
                    $tf->save();
                } else if ($field->type == 'Rich Text') {
                    $rtf = new RichTextField();
                    $rtf->flid = $field->flid;
                    $rtf->rid = $record->rid;
                    $rtf->fid = $fid;
                    $rtf->rawtext = '<b>K3TR</b>: This is a <i>test</i> record';
                    $rtf->save();
                } else if ($field->type == 'Number') {
                    $nf = new NumberField();
                    $nf->flid = $field->flid;
                    $nf->rid = $record->rid;
                    $nf->fid = $fid;
                    $nf->number = 1337;
                    $nf->save();
                } else if ($field->type == 'List') {
                    $lf = new ListField();
                    $lf->flid = $field->flid;
                    $lf->rid = $record->rid;
                    $lf->fid = $fid;
                    $lf->option = 'K3TR';
                    $lf->save();
                } else if ($field->type == 'Multi-Select List') {
                    $mslf = new MultiSelectListField();
                    $mslf->flid = $field->flid;
                    $mslf->rid = $record->rid;
                    $mslf->fid = $fid;
                    $mslf->options = 'K3TR[!]1337[!]Test[!]Record';
                    $mslf->save();
                } else if ($field->type == 'Generated List') {
                    $glf = new GeneratedListField();
                    $glf->flid = $field->flid;
                    $glf->rid = $record->rid;
                    $glf->fid = $fid;
                    $glf->options = 'K3TR[!]1337[!]Test[!]Record';
                    $glf->save();
                } else if($field->type == 'Combo List'){
                    $clf = new ComboListField();
                    $clf->flid = $field->flid;
                    $clf->rid = $record->rid;
                    $clf->fid = $fid;
                    $val1 = '';
                    $val2 = '';
                    $type1 = ComboListField::getComboFieldType($field,'one');
                    $type2 = ComboListField::getComboFieldType($field,'two');
                    switch($type1){
                        case 'Text':
                            $val1 = 'K3TR: This is a test record';
                            break;
                        case 'List':
                            $val1 = 'K3TR';
                            break;
                        case 'Number':
                            $val1 = 1337;
                            break;
                        case 'Multi-Select List'||'Generated List':
                            $val1 = 'K3TR[!]1337[!]Test[!]Record';
                            break;
                    }
                    switch($type2){
                        case 'Text':
                            $val2 = 'K3TR: This is a test record';
                            break;
                        case 'List':
                            $val2 = 'K3TR';
                            break;
                        case 'Number':
                            $val2 = 1337;
                            break;
                        case 'Multi-Select List'||'Generated List':
                            $val2 = 'K3TR[!]1337[!]Test[!]Record';
                            break;
                    }
                    $clf->save();

                    $clf->addData(["[!f1!]".$val1."[!f1!][!f2!]".$val2."[!f2!]", "[!f1!]".$val1."[!f1!][!f2!]".$val2."[!f2!]"], $type1, $type2);
                } else if ($field->type == 'Date') {
                    $df = new DateField();
                    $df->flid = $field->flid;
                    $df->rid = $record->rid;
                    $df->fid = $fid;
                    $df->circa = 1;
                    $df->month = 1;
                    $df->day = 3;
                    $df->year = 1937;
                    $df->era = 'CE';
                    $df->save();
                } else if ($field->type == 'Schedule') {
                    $sf = new ScheduleField();
                    $sf->flid = $field->flid;
                    $sf->rid = $record->rid;
                    $sf->fid = $fid;
                    $sf->save();

                    $sf->addEvents(['K3TR: 01/03/1937 - 01/03/1937']);
                } else if ($field->type == 'Geolocator') {
                    $gf = new GeolocatorField();
                    $gf->flid = $field->flid;
                    $gf->rid = $record->rid;
                    $gf->fid = $fid;
                    $gf->save();

                    $gf->addLocations(['[Desc]K3TR[Desc][LatLon]13,37[LatLon][UTM]37P:283077.41182513,1437987.6443346[UTM][Address] Appelstra�e Hanover Lower Saxony[Address]']);
                } else if ($field->type == 'Documents') {
                    $df = new DocumentsField();
                    $df->flid = $field->flid;
                    $df->rid = $record->rid;
                    $df->fid = $fid;
                    $infoArray = array();
                    $maxfiles = FieldController::getFieldOption($field,'MaxFiles');
                    if($maxfiles==0){$maxfiles=1;}
                    $newPath = env('BASE_PATH') . 'storage/app/files/p' . $pid . '/f' . $fid . '/r' . $record->rid . '/fl' . $field->flid;
                    mkdir($newPath, 0775, true);
                    for ($q=0;$q<$maxfiles;$q++) {
                        $types = DocumentsField::getMimeTypes();
                        if (!array_key_exists('txt', $types))
                            $type = 'application/octet-stream';
                        else
                            $type = $types['txt'];
                        $info = '[Name]documents' . $q . '.txt[Name][Size]24[Size][Type]' . $type . '[Type]';
                        $infoArray['documents' . $q . '.txt'] = $info;
                        copy(env('BASE_PATH') . 'public/testFiles/documents.txt',
                            $newPath . '/documents' . $q . '.txt');
                    }
                    $infoString = implode('[!]',$infoArray);
                    $df->documents = $infoString;
                    $df->save();
                } else if ($field->type == 'Gallery') {
                    $gf = new GalleryField();
                    $gf->flid = $field->flid;
                    $gf->rid = $record->rid;
                    $gf->fid = $fid;
                    $infoArray = array();
                    $maxfiles = FieldController::getFieldOption($field,'MaxFiles');
                    if($maxfiles==0){$maxfiles=1;}
                    $newPath = env('BASE_PATH') . 'storage/app/files/p' . $pid . '/f' . $fid . '/r' . $record->rid . '/fl' . $field->flid;
                    //make the three directories
                    mkdir($newPath, 0775, true);
                    mkdir($newPath . '/thumbnail', 0775, true);
                    mkdir($newPath . '/medium', 0775, true);
                    for ($q=0;$q<$maxfiles;$q++) {
                        $types = DocumentsField::getMimeTypes();
                        if (!array_key_exists('png', $types))
                            $type = 'application/octet-stream';
                        else
                            $type = $types['png'];
                        $info = '[Name]gallery' . $q . '.png[Name][Size]54827[Size][Type]' . $type . '[Type]';
                        $infoArray['gallery' . $q . '.png'] = $info;
                        copy(env('BASE_PATH') . 'public/testFiles/gallery.png',
                            $newPath . '/gallery' . $q . '.png');
                        copy(env('BASE_PATH') . 'public/testFiles/medium/gallery.png',
                            $newPath . '/medium/gallery' . $q . '.png');
                        copy(env('BASE_PATH') . 'public/testFiles/thumbnail/gallery.png',
                            $newPath . '/thumbnail/gallery' . $q . '.png');
                    }
                    $infoString = implode('[!]',$infoArray);
                    $gf->images = $infoString;
                    $gf->save();
                } else if ($field->type == 'Playlist') {
                    $pf = new PlaylistField();
                    $pf->flid = $field->flid;
                    $pf->rid = $record->rid;
                    $pf->fid = $fid;
                    $infoArray = array();
                    $maxfiles = FieldController::getFieldOption($field,'MaxFiles');
                    if($maxfiles==0){$maxfiles=1;}
                    $newPath = env('BASE_PATH') . 'storage/app/files/p' . $pid . '/f' . $fid . '/r' . $record->rid . '/fl' . $field->flid;
                    mkdir($newPath, 0775, true);
                    for ($q=0;$q<$maxfiles;$q++) {
                        $types = DocumentsField::getMimeTypes();
                        if (!array_key_exists('mp3', $types))
                            $type = 'application/octet-stream';
                        else
                            $type = $types['mp3'];
                        $info = '[Name]playlist' . $q . '.mp3[Name][Size]198658[Size][Type]' . $type . '[Type]';
                        $infoArray['playlist' . $q . '.mp3'] = $info;
                        copy(env('BASE_PATH') . 'public/testFiles/playlist.mp3',
                            $newPath . '/playlist' . $q . '.mp3');
                    }
                    $infoString = implode('[!]',$infoArray);
                    $pf->audio = $infoString;
                    $pf->save();
                } else if ($field->type == 'Video') {
                    $vf = new VideoField();
                    $vf->flid = $field->flid;
                    $vf->rid = $record->rid;
                    $vf->fid = $fid;
                    $infoArray = array();
                    $maxfiles = FieldController::getFieldOption($field,'MaxFiles');
                    if($maxfiles==0){$maxfiles=1;}
                    $newPath = env('BASE_PATH') . 'storage/app/files/p' . $pid . '/f' . $fid . '/r' . $record->rid . '/fl' . $field->flid;
                    mkdir($newPath, 0775, true);
                    for ($q=0;$q<$maxfiles;$q++) {
                        $types = DocumentsField::getMimeTypes();
                        if (!array_key_exists('mp4', $types))
                            $type = 'application/octet-stream';
                        else
                            $type = $types['mp4'];
                        $info = '[Name]video' . $q . '.mp4[Name][Size]1055736[Size][Type]' . $type . '[Type]';
                        $infoArray['video' . $q . '.mp4'] = $info;
                        copy(env('BASE_PATH') . 'public/testFiles/video.mp4',
                            $newPath . '/video' . $q . '.mp4');
                    }
                    $infoString = implode('[!]',$infoArray);
                    $vf->video = $infoString;
                    $vf->save();
                } else if ($field->type == '3D-Model') {
                    $mf = new ModelField();
                    $mf->flid = $field->flid;
                    $mf->rid = $record->rid;
                    $mf->fid = $fid;
                    $newPath = env('BASE_PATH') . 'storage/app/files/p' . $pid . '/f' . $fid . '/r' . $record->rid . '/fl' . $field->flid;
                    mkdir($newPath, 0775, true);

                    $types = DocumentsField::getMimeTypes();
                    if (!array_key_exists('stl', $types))
                        $type = 'application/octet-stream';
                    else
                        $type = $types['stl'];
                    $infoString = '[Name]model' . $q . '.stl[Name][Size]9484[Size][Type]' . $type . '[Type]';
                    copy(env('BASE_PATH') . 'public/testFiles/model.stl',
                        $newPath . '/model' . $q . '.stl');

                    $mf->model = $infoString;
                    $mf->save();
                } else if ($field->type == 'Associator') {
                    $af = new AssociatorField();
                    $af->flid = $field->flid;
                    $af->rid = $record->rid;
                    $af->fid = $fid;
                    $af->save();

                    $af->addRecords(array('1-3-37','1-3-37','1-3-37','1-3-37'));
                }
            }
        }

        flash()->overlay('Created test records.',trans('controller_record.goodjob'));
        return redirect()->action('RecordController@index',compact('pid','fid'));
    }
}
