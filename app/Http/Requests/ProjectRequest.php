<?php namespace App\Http\Requests;

use App\Http\Controllers\ProjectController;

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
        $id = $this->route('projects');
        $project = ProjectController::getProject($id);

        switch($this->method())  {
            case 'POST':
                return [
                    'name' => 'required|min:3',
                    'slug' => 'required|alpha_num|min:3|unique:projects',
                    'description' => 'required',
                    'active' => 'required',
                ];
            case 'PATCH':
                return [
                    'name' => 'required|min:3',
                    'slug' => 'required|alpha_num|min:3|unique:projects,slug,'.$project->pid.',pid',
                    'description' => 'required',
                    'active' => 'required',
                ];
            default:
                break;
        }
	}

    /**
     * Get the custom error messages for Projects.
     *
     * @return array
     */
	public function messages() {
		return [
			'slug.required' => trans('request_all.req'),
			'slug.alpha_num' => trans('request_all.alpha'),
			'slug.min' => trans('request_all.minimum'),
            'slug.unique' => trans('request_all.unique')
		];
	}

}
