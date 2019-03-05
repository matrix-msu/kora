<?php namespace App\Http\Controllers;

use App\KoraFields\FileTypeField;
use App\KoraFields\GeolocatorField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
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
    public function validateAddress(Request $request) {
        return GeolocatorField::validateAddress($request);
    }

    /**
     * Converts provide lat/long, utm, or geo coordinates into the other types.
     *
     * @param  Request $request
     * @return string - Geolocator formatted string of the converted coordinates
     */
    public function geoConvert(Request $request) {
        return GeolocatorField::geoConvert($request);
    }

    /**
     * Saves a temporary version of an uploaded file.
     *
     * @param  int $fid - Form ID
     * @param  int $flid - File field that record file will be loaded to
     * @param  Request $request
     */
    public function saveTmpFile($fid, $flid) {
        FileTypeField::saveTmpFile($fid, $flid);
    }

    /**
     * Removes a temporary file for a particular field.
     *
     * @param  int $fid - Form ID
     * @param  int $flid - File field to clear temp files for
     * @param  string $name - Name of the file to delete
     * @param  Request $request
     */
    public function delTmpFile($fid, $flid, $filename) {
        FileTypeField::delTmpFile($fid, $flid, $filename);
    }

    /**
     * Downloads a file from a particular record field.
     *
     * @param  int $kid - Record Kora ID
     * @param  string $filename - Name of the file
     * @return string - html for the file download
     */
    public function getFileDownload($kid, $filename) {
        return FileTypeField::getFileDownload($kid, $filename);
    }

    public function getZipDownload($kid, $filename) {
        return FileTypeField::getZipDownload($kid, $filename);
    }

    /**
     * Gets the image associated with the Gallery Field of a particular record.
     *
     * @param  int $kid - Record Kora ID
     * @param  int $flid - Field ID
     * @param  string $filename - Name of image file
     * @param  string $type - Get either the full image or a thumbnail of the image
     * @return string - html for the file download
     */
    public function getImgDisplay($kid, $flid, $filename, $type){
        $record = RecordController::getRecord($kid);
        $field = FieldController::getField($flid,$record->form_id);

        $form = FormController::getForm($record->form_id);
        $galleryField = $form->getFieldModel($field['type']);

        return $galleryField->getImgDisplay($record, $filename, $type);
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
     * @param  string $filename - Image filename
     * @return Redirect
     */
    public function singleResource($pid, $fid, $rid, $filename) {
        $relative_src = "files/$pid/$fid/$rid/$filename";
        $src = url('app/'.$relative_src);

        if(!file_exists(storage_path('app/'.$relative_src))) {
            // File does not exist
            dd($filename . ' not found');
        }

        $mime = Storage::mimeType($relative_src);

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
        $ext = \File::extension($src);

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
        } else {
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
     * @param  View - The geo view
     * @return Redirect
     */
    public function singleGeolocator($pid, $fid, $rid, Request $request) {
        $form = FormController::getForm($fid);
        $flid = $request->flid;
        $field = FieldController::getField($flid,$fid);
        $record = RecordController::getRecord($pid.'-'.$fid.'-'.$rid);
        $value = $record->{$flid};
        $typedField = $form->getFieldModel($field['type']);

        return view('fields.singleGeolocator', compact('field', 'record','value','flid','typedField'));
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
        $record = RecordController::getRecord($pid.'-'.$fid.'-'.$rid);
        $typedField = $field->getTypedFieldFromRID($rid);

        return view('fields.singleRichtext', compact('field', 'record', 'typedField'));
    }
}
