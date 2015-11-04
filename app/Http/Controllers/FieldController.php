<?php namespace App\Http\Controllers;

use App\Field;
use App\FieldHelpers\gPoint;
use App\Http\Requests;
use App\Http\Requests\FieldRequest;
use App\Http\Controllers\Controller;
use App\FieldHelpers\FieldDefaults;
use App\FieldHelpers\UploadHandler;

use Geocoder\HttpAdapter\CurlHttpAdapter;
use Geocoder\Provider\NominatimProvider;
use Geocoder\Provider\YandexProvider;
use Geocoder\Tests\HttpAdapter\CurlHttpAdapterTest;
use Illuminate\Http\Request;
use Toin0u\Geocoder\Facade\Geocoder;

class FieldController extends Controller {


    /**
     * User must be logged in to access views in this controller.
     */
       public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @param $pid
     * @param $fid
     * @return Response
     */
	public function create($pid, $fid)
	{
        if(!FormController::validProjForm($pid, $fid)){
            return redirect('projects');
        }

        if(!FieldController::checkPermissions($fid, 'create')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

		$form = FormController::getForm($fid);
        return view('fields.create', compact('form'));
	}

    /**
     * Store a newly created resource in storage.
     *
     * @param FieldRequest $request
     * @return Response
     */
	public function store(FieldRequest $request)
    {
        $field = Field::Create($request->all());
        $field->options = FieldDefaults::getOptions($field->type);
        $field->default = FieldDefaults::getDefault($field->type);
        $field->save();

        //need to add field to layout xml
        $form = FormController::getForm($field->fid);
        $layout = explode('</LAYOUT>',$form->layout);
        $form->layout = $layout[0].'<ID>'.$field->flid.'</ID></LAYOUT>';
        $form->save();

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($form->fid);

        flash()->overlay('Your field has been successfully created!', 'Good Job');

        return redirect('projects/'.$field->pid.'/forms/'.$field->fid);
	}

    /**
     * Display the specified resource.
     *
     * @param $pid
     * @param $fid
     * @param $flid
     * @return Response
     * @internal param int $id
     */
	public function show($pid, $fid, $flid)
	{
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        if(!FieldController::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = FieldController::getField($flid);
        $form = FormController::getForm($fid);
        $proj = ProjectController::getProject($pid);

        $presets = OptionPresetController::getPresetsSupported($pid,$field);

        if($field->type=="Text") {
            return view('fields.options.text', compact('field', 'form', 'proj','presets'));
        }else if($field->type=="Rich Text") {
            return view('fields.options.richtext', compact('field', 'form', 'proj'));
        }else if($field->type=="Number") {
            return view('fields.options.number', compact('field', 'form', 'proj'));
        }else if($field->type=="List") {
            return view('fields.options.list', compact('field', 'form', 'proj','presets'));
        }else if($field->type=="Multi-Select List") {
            return view('fields.options.mslist', compact('field', 'form', 'proj','presets'));
        }else if($field->type=="Generated List") {
            return view('fields.options.genlist', compact('field', 'form', 'proj','presets'));
        }else if($field->type=="Date") {
            return view('fields.options.date', compact('field', 'form', 'proj'));
        }else if($field->type=="Schedule") {
            return view('fields.options.schedule', compact('field', 'form', 'proj','presets'));
        }else if($field->type=="Geolocator") {
            return view('fields.options.geolocator', compact('field', 'form', 'proj','presets'));
        }else if($field->type=="Documents") {
            return view('fields.options.documents', compact('field', 'form', 'proj'));
        }else if($field->type=="Gallery") {
            return view('fields.options.gallery', compact('field', 'form', 'proj'));
        }else if($field->type=="Playlist") {
            return view('fields.options.playlist', compact('field', 'form', 'proj'));
        }else if($field->type=="Video") {
            return view('fields.options.video', compact('field', 'form', 'proj'));
        }else if($field->type=="3D-Model") {
            return view('fields.options.3dmodel', compact('field', 'form', 'proj'));
        }else if($field->type=="Associator") {
            return view('fields.options.associator', compact('field', 'form', 'proj'));
        }
	}

    /**
     * Show the form for editing the specified resource.
     *
     * @param $pid
     * @param $fid
     * @param $flid
     * @return Response
     * @internal param int $id
     */
	public function edit($pid, $fid, $flid)
	{
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        if(!FieldController::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = FieldController::getField($flid);

        return view('fields.edit', compact('field', 'fid', 'pid','presets'));
	}

    /**
     * Update the specified resource in storage.
     *
     * @param $flid
     * @param FieldRequest $request
     * @return Response
     * @internal param int $id
     */
	public function update($pid, $fid, $flid, FieldRequest $request)
	{
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        if(!FieldController::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

		$field = FieldController::getField($flid);

        $field->update($request->all());

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($fid);

        flash()->overlay('Your field has been successfully updated!', 'Good Job!');

        return redirect('projects/'.$pid.'/forms/'.$fid);
	}

    public function updateRequired($pid, $fid, $flid, FieldRequest $request)
    {
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        if(!FieldController::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = FieldController::getField($flid);

        $field->required = $request->required;
        $field->save();

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($fid);

        flash()->success('Option updated!');

        return redirect('projects/'.$pid.'/forms/'.$fid.'/fields/'.$flid.'/options');
    }

    public function updateDefault($pid, $fid, $flid, Request $request)
    {
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        if(!FieldController::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = FieldController::getField($flid);

        if(($field->type=='Multi-Select List' | $field->type=='Generated List' | $field->type=='Associator') && !is_null($request->default)){
            $reqDefs = $request->default;
            $def = $reqDefs[0];
            for($i=1;$i<sizeof($reqDefs);$i++){
                $def .= '[!]'.$reqDefs[$i];
            }
            $field->default = $def;
        }else if ($field->type=='Date'){
            if(FieldController::validateDate($request->default_month,$request->default_day,$request->default_year))
                $field->default = '[M]'.$request->default_month.'[M][D]'.$request->default_day.'[D][Y]'.$request->default_year.'[Y]';
            else{
                flash()->error('Invalid date. Either day given w/ no month provided, or day and month are impossible.');

                return redirect('projects/'.$pid.'/forms/'.$fid.'/fields/'.$flid.'/options');
            }
        }else{
            $field->default = $request->default;
        }

        $field->save();

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($fid);

        flash()->success('Option updated!');

        return redirect('projects/'.$pid.'/forms/'.$fid.'/fields/'.$flid.'/options');
    }

    public function updateOptions($pid, $fid, $flid, Request $request)
    {
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        if(!FieldController::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = FieldController::getField($flid);

        FieldController::setFieldOptions($field, $request->option, $request->value);

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($fid);

        if($request->option != 'SearchForms') {
            flash()->success('Option updated!');

            return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $pid
     * @param $fid
     * @param $flid
     * @return Response
     * @internal param int $id
     */
	public function destroy($pid, $fid, $flid)
	{
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects/'.$pid.'forms/');
        }

        if(!FieldController::checkPermissions($fid, 'delete')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = FieldController::getField($flid);
        $field->delete();

        $form = FormController::getForm($fid);
        $layout = explode('<ID>'.$field->flid.'</ID>',$form->layout);
        $form->layout = $layout[0].$layout[1];
        $form->save();

        RevisionController::wipeRollbacks($form->fid);

        flash()->overlay('Your field has been successfully deleted!', 'Good Job!');
	}

    /**
     * Get field object for use in controller.
     *
     * @param $flid
     * @return mixed
     */
    public static function getField($flid)
    {
        $field = Field::where('flid', '=', $flid)->first();
        if(is_null($field)){
            $field = Field::where('slug','=',$flid)->first();
        }

        return $field;
    }

    /**
     * Validate that a field belongs to a form and project.
     *
     * @param $pid
     * @param $fid
     * @param $flid
     * @return bool
     */
    public static function validProjFormField($pid, $fid, $flid)
    {
        $field = FieldController::getField($flid);
        $form = FormController::getForm($fid);
        $proj = ProjectController::getProject($pid);

        if (!FormController::validProjForm($pid, $fid))
            return false;

        if (is_null($field) || is_null($form) || is_null($proj))
            return false;
        else if ($field->fid == $form->fid)
            return true;
        else
            return false;
    }

    public static function getFieldOption($field, $key){
        $options = $field->options;
        $tag = '[!'.$key.'!]';
        $value = explode($tag,$options)[1];

        return $value;
    }

    public static function setFieldOptions($field, $key, $value){
        $options = $field->options;
        $tag = '[!'.$key.'!]';
        $array = explode($tag,$options);

        if(($field->type=='Documents' | $field->type=='Gallery' | $field->type=='Playlist' | $field->type=='Video'
                | $field->type=='3D-Model') && $key=='FileTypes'){
            $valueString = $value[0];
            for($i=1;$i<sizeof($value);$i++){
                $valueString .= '[!]'.$value[$i];
            }
            $value = $valueString;
        }else if($field->type=='Gallery' && ($key=='ThumbSmall' | $key=='ThumbLarge')){
            $x = $_REQUEST['value_x'];
            $y = $_REQUEST['value_y'];

            $value = $x.'x'.$y;
        }

        $field->options = $array[0].$tag.$value.$tag.$array[2];
        $field->save();
    }

    /**
     * Checks if a user has a certain permission.
     * If no permission is provided checkPermissions simply decides if they are in any form group.
     * This acts as the "can read" permission level.
     *
     * @param $fid
     * @param string $permission
     * @return bool
     */
    private function checkPermissions($fid, $permission='')
    {
        switch($permission) {
            case 'create':
                if(!(\Auth::user()->canCreateFields(FormController::getForm($fid))))
                {
                    flash()->overlay('You do not have permission to create fields for that form.', 'Whoops.');
                    return false;
                }
                return true;
            case 'edit':
                if(!(\Auth::user()->canEditFields(FormController::getForm($fid))))
                {
                    flash()->overlay('You do not have permission to edit fields for that form.', 'Whoops.');
                    return false;
                }
                return true;
            case 'delete':
                if(!(\Auth::user()->canDeleteFields(FormController::getForm($fid))))
                {
                    flash()->overlay('You do not have permission to delete fields for that form.', 'Whoops.');
                    return false;
                }
                return true;
            default:
                if(!(\Auth::user()->inAFormGroup(FormController::getForm($fid))))
                {
                    flash()->overlay('You do not have permission to view that field.', 'Whoops.');
                    return false;
                }
                return true;
        }
    }


    /****************************************************************************************************
     *          THIS SECTION IS RESERVED FOR FUNCTIONS DEALING WITH SPECIFIC FIELD TYPES                 *
     ****************************************************************************************************/

    public function saveList($pid, $fid, $flid){
        if ($_REQUEST['action']=='SaveList') {
            if(isset($_REQUEST['options']))
                $options = $_REQUEST['options'];
            else
                $options = array();

            $dbOpt = '';

            if (sizeof($options) == 1) {
                $dbOpt = $options[0];
            } else if (sizeof($options) == 2) {
                $dbOpt = $options[0] . '[!]' . $options[1];
            } else if (sizeof($options) > 2) {
                $dbOpt = $options[0];
                for ($i = 1; $i < sizeof($options); $i++) {
                    $dbOpt .= '[!]' . $options[$i];
                }
            }

            $field = FieldController::getField($flid);

            //This line removes the default if it no longer exists
            if(!in_array($field->default,$options)){
                $field->default = '';
                $field->save();
            }

            FieldController::setFieldOptions($field, 'Options', $dbOpt);
        }
    }

    public static function getList($field, $blankOpt=false)
    {
        $dbOpt = FieldController::getFieldOption($field, 'Options');
        $options = array();

        if ($dbOpt == '') {
            //skip
        } else if (!strstr($dbOpt, '[!]')) {
            $options = [$dbOpt => $dbOpt];
        } else {
            $opts = explode('[!]', $dbOpt);
            foreach ($opts as $opt) {
                $options[$opt] = $opt;
            }
        }

        if ($blankOpt) {
            $options = array('' => '') + $options;
        }

        return $options;
    }

    public static function msListArrayToString($array){
        if(is_array($array)){
            $list = $array[0];
            for($i=1;$i<sizeof($array);$i++){
                $list .= '[!]'.$array[$i];
            }
            return $list;
        }

        return '';
    }

    public static function validateDate($m,$d,$y){
        if($d!='' && !is_null($d)) {
            if ($m == '' | is_null($m)) {
                return false;
            } else {
                return checkdate($m, $d, $y);
            }
        }

        return true;
    }

    public static function getDateList($field)
    {
        $def = $field->default;
        $options = array();

        if ($def == '') {
            //skip
        } else if (!strstr($def, '[!]')) {
            $options = [$def => $def];
        } else {
            $opts = explode('[!]', $def);
            foreach ($opts as $opt) {
                $options[$opt] = $opt;
            }
        }

        return $options;
    }

    public static function saveDateList($pid, $fid, $flid){
        if ($_REQUEST['action']=='SaveDateList') {
            if(isset($_REQUEST['options']))
                $options = $_REQUEST['options'];
            else
                $options = array();

            $dbOpt = '';

            if (sizeof($options) == 1) {
                $dbOpt = $options[0];
            } else if (sizeof($options) == 2) {
                $dbOpt = $options[0] . '[!]' . $options[1];
            } else if (sizeof($options) > 2) {
                $dbOpt = $options[0];
                for ($i = 1; $i < sizeof($options); $i++) {
                    $dbOpt .= '[!]' . $options[$i];
                }
            }

            $field = FieldController::getField($flid);

            $field->default = $dbOpt;
            $field->save();
        }
    }

    public function geoConvert(Request $request){
        if($request->type == 'latlon'){
            $lat = $request->lat;
            $lon = $request->lon;

            //to utm
            $con = new gPoint();
            $con->gPoint();
            $con->setLongLat($lon,$lat);
            $con->convertLLtoTM();
            $utm = $con->utmZone.':'.$con->utmEasting.','.$con->utmNorthing;

            //to address
            $con = new \Geocoder\Geocoder();
            $con->registerProviders([
                new NominatimProvider(
                    new CurlHttpAdapter(), 'http://nominatim.openstreetmap.org/', 'en'
                )
            ]);
            try {
                $res = $con->geocode($lat.', '.$lon);
                $addr = $res->getStreetNumber().' '.$res->getStreetName().' '.$res->getCity().' '.$res->getRegion();
            } catch(\Exception $e){
                $addr = 'null';
            }

            $result = '[LatLon]'.$lat.','.$lon.'[LatLon][UTM]'.$utm.'[UTM][Address]'.$addr.'[Address]';

            return $result;
        }else if($request->type == 'utm'){
            $zone = $request->zone;
            $east = $request->east;
            $north = $request->north;

            //to latlon
            $con = new gPoint();
            $con->gPoint();
            $con->setUTM($east,$north,$zone);
            $con->convertTMtoLL();
            $lat = $con->lat;
            $lon = $con->long;

            //to address
            $con = new \Geocoder\Geocoder();
            $con->registerProviders([
                new NominatimProvider(
                    new CurlHttpAdapter(), 'http://nominatim.openstreetmap.org/', 'en'
                )
            ]);
            try {
                $res = $con->geocode($lat.', '.$lon);
                $addr = $res->getStreetNumber().' '.$res->getStreetName().' '.$res->getCity().' '.$res->getRegion();
            } catch(\Exception $e){
                $addr = 'null';
            }

            $result = '[LatLon]'.$lat.','.$lon.'[LatLon][UTM]'.$zone.':'.$east.','.$north.'[UTM][Address]'.$addr.'[Address]';

            return $result;
        }else if($request->type == 'geo') {
            $addr = $request->addr;

            //to latlon
            $con = new \Geocoder\Geocoder();
            $con->registerProviders([
                new NominatimProvider(
                    new CurlHttpAdapter(), 'http://nominatim.openstreetmap.org/', 'en'
                )
            ]);
            try {
                $res = $con->geocode($addr);
                $lat = $res->getLatitude();
                $lon = $res->getLongitude();
            } catch(\Exception $e){
                $lat = 'null';
                $lon = 'null';
            }

            //to utm
            if($lat != 'null' && $lon != 'null') {
                $con = new gPoint();
                $con->gPoint();
                $con->setLongLat($lon,$lat);
                $con->convertLLtoTM();

                $utm = $con->utmZone.':'.$con->utmEasting.','.$con->utmNorthing;
            }else{
                $utm = 'null:null.null';
            }

            $result = '[LatLon]'.$lat.','.$lon.'[LatLon][UTM]'.$utm.'[UTM][Address]'.$addr.'[Address]';

            return $result;
        }
    }

    public function saveTmpFile($flid, Request $request){
        $field = FieldController::getField($flid);
        $uid = \Auth::user()->id;
        $dir = env('BASE_PATH').'storage/app/tmpFiles/f'.$flid.'u'.$uid;

        $maxFileNum = FieldController::getFieldOption($field, 'MaxFiles');
        $fileNumRequest = sizeof($_FILES['file'.$flid]['name']);
        if (glob($dir.'/*.*') != false)
        {
            $fileNumDisk = count(glob($dir.'/*.*'));
        }
        else
        {
            $fileNumDisk = 0;
        }

        $maxFieldSize = FieldController::getFieldOption($field, 'FieldSize')*1024; //conversion of kb to bytes
        $fileSizeRequest = 0;
        foreach($_FILES['file'.$flid]['size'] as $size){
            $fileSizeRequest += $size;
        }
        $fileSizeDisk = 0;
        if(file_exists($dir)) {
            foreach (new \DirectoryIterator($dir) as $file) {
                if ($file->isFile()) {
                    $fileSizeDisk += $file->getSize();
                }
            }
        }

        if($field->type=='Gallery') {
            $smThumbs = explode('x', FieldController::getFieldOption($field, 'ThumbSmall'));
            $lgThumbs = explode('x', FieldController::getFieldOption($field, 'ThumbLarge'));
        }

        $validTypes = true;
        $fileTypes = explode('[!]',FieldController::getFieldOption($field, 'FileTypes'));
        $fileTypesRequest = $_FILES['file'.$flid]['type'];
        if((sizeof($fileTypes)!=1 | $fileTypes[0]!='') && $field->type != '3D-Model') {
            foreach ($fileTypesRequest as $type) {
                if (!in_array($type,$fileTypes)){
                    $validTypes = false;
                }
            }
        }else if($field->type=='Gallery'){
            foreach ($fileTypesRequest as $type) {
                if (!in_array($type,['image/jpeg','image/gif','image/png'])){
                    $validTypes = false;
                }
            }
        }else if($field->type=='Playlist'){
            foreach ($fileTypesRequest as $type) {
                if (!in_array($type,['audio/mp3','audio/wav','audio/ogg'])){
                    $validTypes = false;
                }
            }
        }else if($field->type=='Video'){
            foreach ($fileTypesRequest as $type) {
                if (!in_array($type,['video/mp4','video/ogg'])){
                    $validTypes = false;
                }
            }
        }else if($field->type=='3D-Model'){
            foreach ($_FILES['file'.$flid]['name'] as $file) {
                $filetype = explode('.',$file);
                $type = array_pop($filetype);
                if (!in_array($type,['obj','stl'])){
                    $validTypes = false;
                }
            }
        }

        $options = array();
        $options['flid'] = 'f'.$flid.'u'.$uid;
        if($field->type=='Gallery') {
            $options['image_versions']['thumbnail']['max_width'] = $smThumbs[0];
            $options['image_versions']['thumbnail']['max_height'] = $smThumbs[1];
            $options['image_versions']['medium']['max_width'] = $lgThumbs[0];
            $options['image_versions']['medium']['max_height'] = $lgThumbs[1];
        }
        if(!$validTypes){
            echo 'InvalidType';
        } else if($maxFileNum !=0 && $fileNumRequest+$fileNumDisk>$maxFileNum){
            echo 'TooManyFiles';
        } else if($maxFieldSize !=0 && $fileSizeRequest+$fileSizeDisk>$maxFieldSize){
            echo 'MaxSizeReached';
        } else {
            $upload_handler = new UploadHandler($options);
        }
    }

    public function delTmpFile($flid, $filename, Request $request){
        $uid = \Auth::user()->id;
        $options = array();
        $options['flid'] = $flid;
        $options['filename'] = $filename;
        $upload_handler = new UploadHandler($options);
    }

    public static function getMimeTypes(){
        $types=array();
        foreach(@explode("\n",@file_get_contents('http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types'))as $x)
            if(isset($x[0])&&$x[0]!=='#'&&preg_match_all('#([^\s]+)#',$x,$out)&&isset($out[1])&&($c=count($out[1]))>1)
                for($i=1;$i<$c;$i++)
                    $types[$out[1][$i]]=$out[1][0];
        return $types;
    }

    public function getFileDownload($rid, $flid, $filename){
        $record = RecordController::getRecord($rid);
        $field = FieldController::getField($flid);

        // Check if file exists in app/storage/file folder
        $file_path = env('BASE_PATH').'storage/app/files/p'.$record->pid.'/f'.$record->fid.'/r'.$record->rid.'/fl'.$field->flid . '/' . $filename;
        if (file_exists($file_path))
        {
            // Send Download
            return response()->download($file_path, $filename, [
                'Content-Length: '. filesize($file_path)
            ]);
        }
        else
        {
            // Error
            exit('Requested file does not exist on our server!');
        }
    }

    public function getImgDisplay($rid, $flid, $filename, $type){
        $record = RecordController::getRecord($rid);
        $field = FieldController::getField($flid);
        if($type == 'thumbnail' | $type == 'medium'){
            $file_path = env('BASE_PATH').'storage/app/files/p'.$record->pid.'/f'.$record->fid.'/r'.$record->rid.'/fl'.$field->flid.'/'.$type.'/'. $filename;
        }else{
            $file_path = env('BASE_PATH').'storage/app/files/p'.$record->pid.'/f'.$record->fid.'/r'.$record->rid.'/fl'.$field->flid . '/' . $filename;

        }

        if (file_exists($file_path))
        {
            // Send Download
            return response()->download($file_path, $filename, [
                'Content-Length: '. filesize($file_path)
            ]);
        }
        else
        {
            // Error
            exit('Requested file does not exist on our server!');
        }
    }
}
