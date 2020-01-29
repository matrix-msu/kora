<?php namespace App\Http\Requests;

class FormRequest extends Request {

    /*
    |--------------------------------------------------------------------------
    | Form Request
    |--------------------------------------------------------------------------
    |
    | This request handles validation of request inputs for Forms
    |
    */

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'project_id' => 'required|numeric',
            'name' => 'required|min:3|max:60|regex:/^[a-zA-Z0-9\s]+$/',
            'description' => 'required|max:1000',
        ];
    }

}