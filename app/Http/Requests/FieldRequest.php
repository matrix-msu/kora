<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class FieldRequest extends Request {

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
            'pid' => 'required|numeric',
            'fid' => 'required|numeric',
            'order' => 'required',
            'type' => 'required',
            'name' => 'required|min:3',
            'slug' => 'alpha_num',
            'description' => 'required',
            'required' => 'required'
        ];
    }

}