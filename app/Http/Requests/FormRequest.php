<?php namespace App\Http\Requests;

use App\Http\Controllers\FormController;
use App\Http\Requests\Request;

class FormRequest extends Request {

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
        $id = $this->route('fid');
        $form = FormController::getForm($id);

        switch($this->method())
        {
            case 'POST':
            {
                return [
                    'pid' => 'required|numeric',
                    'name' => 'required|min:3',
                    'slug' => 'required|alpha_num|min:3|unique:forms',
                    'description' => 'required',
                ];
            }
            case 'PATCH':
            {
                return [
                    'pid' => 'required|numeric',
                    'name' => 'required|min:3',
                    'slug' => 'required|alpha_num|min:3|unique:forms,slug,'.$form->fid.',fid',
                    'description' => 'required',
                ];
            }
            default:break;
        }
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