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
                    'slug' => 'required|alpha_dash|min:3|unique:projects',
                    'description' => 'required|max:255',
                ];
            case 'PATCH':
                return [
                    'name' => 'required|min:3',
                    'slug' => 'required|alpha_dash|min:3|unique:projects,slug,'.$project->pid.',pid',
                    'description' => 'required|max:255',
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
			'slug.required' => "The reference name field is required.",
			'slug.alpha_dash' => "The reference name may only contain letters, numbers, underscores, and hyphens.",
			'slug.min' => "The reference name must be at least 3 characters.",
            'slug.unique' => "The reference name already exists. Please try another one."
		];
	}

}
