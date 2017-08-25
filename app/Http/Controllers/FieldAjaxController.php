<?php namespace App\Http\Controllers;

use App\ComboListField;
use App\Field;
use App\FileTypeField;
use App\GeolocatorField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
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
    public function validateComboListOpt($pid, $fid, $flid, Request $request) {
        if(!FieldController::validProjFormField($pid, $fid, $flid))
            return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_error', 'field_invalid');

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
     * @param  int $flid - File field that record file will be loaded to
     * @param  Request $request
     */
    public function saveTmpFile($flid) {
        FileTypeField::saveTmpFile($flid);
    }

    /**
     * Removes a temporary file for a particular field.
     *
     * @param  int $flid - File field to clear temp files for
     * @param  string $name - Name of the file to delete
     * @param  Request $request
     */
    public function delTmpFile($flid, $filename) {
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
    public function getFileDownload($rid, $flid, $filename) {
        return FileTypeField::getFileDownload($rid, $flid, $filename);
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
    public function getImgDisplay($rid, $flid, $filename, $type){
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

        $type = $request->type;

        return view(Field::getTypedFieldStatic($type)->getAdvancedFieldOptionsView(), compact('field', 'form', 'proj','presets'));
    }

    /**
     * Update the options for a particular field.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @param  Request $request
     * @return Redirect
     */
    public function updateOptions($pid, $fid, $flid, Request $request){
        if(!FieldController::validProjFormField($pid, $fid, $flid))
            return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_error', 'field_invalid');

        $field = FieldController::getField($flid);
        return $field->getTypedField()->updateOptions($field, $request);
    }
}
