<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class ProjectRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
            'nextForm' => 'required|numeric',
            'name' => 'required|min:3',
            'slug' => 'required|alpha_num',
            'description' => 'required',
            'adminId' => 'required|numeric',
            'active' => 'required',
		];
	}

}
