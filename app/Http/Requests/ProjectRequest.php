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
            'name' => 'required|min:3',
            'slug' => 'required|alpha_num|min:3|unique:projects',
            'description' => 'required',
            'active' => 'required',
		];
	}

	public function messages()
	{
		return [
			'slug.required' => trans('request_all.req'),
			'slug.alpha_num' => trans('request_all.alpha'),
			'slug.min' => trans('request_all.minimum'),
            'slug.unique' => trans('request_all.unique')
		];
	}

}
