<?php namespace App\Http\Requests;

class FieldRequest extends Request {

    /*
    |--------------------------------------------------------------------------
    | Field Request
    |--------------------------------------------------------------------------
    |
    | This request handles validation of request inputs for Fields
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
            'pid' => 'required|numeric',
            'fid' => 'required|numeric',
            'type' => 'required',
            'name' => 'required|min:3|max:60|regex:/^[a-zA-Z0-9\s]+$/',
            'altName' => 'min:3|max:60|regex:/^[a-zA-Z0-9\s]+$/',
            'desc' => 'required|max:500',
            'cfname1' =>'required_if:type,Combo List',
            'cfname2' =>'required_if:type,Combo List'
        ];
    }

}