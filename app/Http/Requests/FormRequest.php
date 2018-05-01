<?php namespace App\Http\Requests;

use App\Http\Controllers\FormController;

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
        $id = $this->route('fid');
        $form = FormController::getForm($id);

        switch($this->method()) {
            case 'POST':
                return [
                    'pid' => 'required|numeric',
                    'name' => 'required|min:3',
                    'slug' => 'required|alpha_dash|min:3|unique:forms',
                    'description' => 'required|max:255',
                ];
            case 'PATCH':
                return [
                    'pid' => 'required|numeric',
                    'name' => 'required|min:3',
                    'slug' => 'required|alpha_dash|min:3|unique:forms,slug,'.$form->fid.',fid',
                    'description' => 'required|max:255',
                ];
            default:
                break;
        }
    }

    /**
     * Get the custom error messages for Forms.
     *
     * @return array
     */
    public function messages() {
        return [
            'slug.required' => "The unique ID field is required.",
            'slug.alpha_dash' => "The unique ID may only contain letters, numbers, underscores, and hyphens.",
            'slug.min' => "The unique ID must be at least 3 characters.",
            'slug.unique' => "The unique ID already exists. Please try another one."
        ];
    }

}