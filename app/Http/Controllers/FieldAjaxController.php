<?php namespace App\Http\Controllers;

use App\ComboListField;
use App\Field;
use App\FileTypeField;
use App\GeolocatorField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FieldAjaxController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Field Ajax Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles ajax requests within Kora3/laravel to make Route
    | for specific field related functions. This class merely calls the requested
    | field function, and then returns its result. We do this so field classes can
    | maintain their functions, but since routing in laravel is used to call
    | Controllers instead of Models for best practice, we use this Controller as
    | the go between
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
     * Validates record data for a Combo List Field.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @param  Request $request
     * @return JsonResponse - Returns success/error message
     */
    public function validateComboListOpt($pid, $fid, $flid, Request $request) { //TODO::CASTLE
        if(!FieldController::validProjFormField($pid, $fid, $flid))
            return response()->json(["status"=>false,"message"=>"field_invalid"],500);

        return ComboListField::validateComboListOpt($flid, $request);
    }

    /**
     * Validates the address for a Geolocator field.
     *
     * @param  Request $request
     * @return bool - Result of address validity
     */
    public function validateAddress(Request $request) { //TODO::CASTLE
        return GeolocatorField::validateAddress($request);
    }

    /**
     * Converts provide lat/long, utm, or geo coordinates into the other types.
     *
     * @param  Request $request
     * @return string - Geolocator formatted string of the converted coordinates
     */
    public function geoConvert(Request $request) { //TODO::CASTLE
        return GeolocatorField::geoConvert($request);
    }

    /**
     * Saves a temporary version of an uploaded file.
     *
     * @param  int $flid - File field that record file will be loaded to
     * @param  Request $request
     */
    public function saveTmpFile($flid) { //TODO::CASTLE
        FileTypeField::saveTmpFile($flid);
    }

    /**
     * Removes a temporary file for a particular field.
     *
     * @param  int $flid - File field to clear temp files for
     * @param  string $name - Name of the file to delete
     * @param  Request $request
     */
    public function delTmpFile($flid, $filename) { //TODO::CASTLE
        FileTypeField::delTmpFile($flid, $filename);
    }

    /**
     * Downloads a file from a particular record field.
     *
     * @param  int $rid - Record ID
     * @param  int $flid - Field ID
     * @param  string $filename - Name of the file
     * @return string - html for the file download
     */
    public function getFileDownload($rid, $flid, $filename) { //TODO::CASTLE
        return FileTypeField::getFileDownload($rid, $flid, $filename);
    }

    public function getZipDownload($rid, $flid, $filename) { //TODO::CASTLE
        return FileTypeField::getZipDownload($rid, $flid, $filename);
    }

    /**
     * Gets the image associated with the Gallery Field of a particular record.
     *
     * @param  int $rid - Record ID
     * @param  int $flid - Field ID
     * @param  string $filename - Name of image file
     * @param  string $type - Get either the full image or a thumbnail of the image
     * @return string - html for the file download
     */
    public function getImgDisplay($rid, $flid, $filename, $type){ //TODO::CASTLE
        $field = FieldController::getField($flid);
        $galleryField = $field->getTypedFieldFromRID($rid);
        return $galleryField->getImgDisplay($field->pid, $filename, $type);
    }

    /**
     * Gets field form for advanced create view.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return View
     */
    public function getAdvancedOptionsPage($pid, $fid, Request $request) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $form = FormController::getForm($fid);
        $type = $request->type;

        return view($form->getFieldModel($type)->getAdvancedFieldOptionsView(), compact('fid'));
    }

    /**
     * View single image/video/audio/document from a record.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @param  int $flid - Field ID
     * @param  string $filename - Image filename
     * @return Redirect
     */
    public function singleResource($pid, $fid, $rid, $flid, $filename) { //TODO::CASTLE
        $relative_src = 'files/p'.$pid.'/f'.$fid.'/r'.$rid.'/fl'.$flid.'/'.$filename;
        $src = url('app/'.$relative_src);

        if(!file_exists(storage_path('app/'.$relative_src))) {
            // File does not exist
            dd($filename . ' not found');
        }

        $mime = Storage::mimeType('files/p'.$pid.'/f'.$fid.'/r'.$rid.'/fl'.$flid.'/'.$filename);

        if(strpos($mime, 'image') !== false || strpos($mime, 'jpeg') !== false || strpos($mime, 'png') !== false) {
            // Image
            return view('fields.singleImage', compact('filename', 'src'));
        } else if(strpos($mime, 'video') !== false || strpos($mime, 'mp4') !== false) {
            // Video
            return view('fields.singleVideo', compact('filename', 'src'));
        } else if(strpos($mime, 'audio') !== false || strpos($mime, 'mpeg') !== false || strpos($mime, 'mp3') !== false) {
            // Audio
            return view('fields.singleAudio', compact('filename', 'src'));
        }

        // Attempting to open generic document
        $ext = File::extension($src);

        if($ext=='pdf'){
            $content_types='application/pdf';
        } else if($ext=='doc') {
            $content_types='application/msword';
        } else if($ext=='docx') {
            $content_types='application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        } else if($ext=='xls') {
            $content_types='application/vnd.ms-excel';
        } else if($ext=='xlsx') {
            $content_types='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        } else if($ext=='txt') {
            $content_types='application/octet-stream';
        }

        return response()->file('app/'.$relative_src, [
            'Content-Type' => $content_types
        ]);
    }

    /**
     * View single image/video/audio/document from a record.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @param  int $flid - Field ID
     * @param  string $filename - Image filename
     * @return Redirect
     */
    public function singleGeolocator($pid, $fid, $rid, $flid) { //TODO::CASTLE
        $field = self::getField($flid);
        $record = RecordController::getRecord($rid);
        $typedField = $field->getTypedFieldFromRID($rid);

        return view('fields.singleGeolocator', compact('field', 'record', 'typedField'));
    }

    /**
     * View single image/video/audio/document from a record.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @param  int $flid - Field ID
     * @param  string $filename - Image filename
     * @return Redirect
     */
    public function singleRichtext($pid, $fid, $rid, $flid) { //TODO::CASTLE
        $field = self::getField($flid);
        $record = RecordController::getRecord($rid);
        $typedField = $field->getTypedFieldFromRID($rid);

        return view('fields.singleRichtext', compact('field', 'record', 'typedField'));
    }
}
