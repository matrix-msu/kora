<?php namespace App\Http\Requests;

class ProjectRequest extends Request {

    /*
    |--------------------------------------------------------------------------
    | Project Request
    |--------------------------------------------------------------------------
    |
    | This request handles validation of request inputs for Projects
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
            'name' => 'required|min:3|regex:/^[a-zA-Z0-9\s]+$/',
            'description' => 'required|max:500',
        ];
	}

}
