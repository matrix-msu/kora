<?php namespace App\Http\Requests;

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
        $messages = [
            'email.required' => 'We need to know your e-mail address!',
        ];

        return [
            'pid' => 'required|numeric',
            'name' => 'required|min:3',
            'slug' => 'required|alpha_num|min:3',
            'description' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'slug.required' => 'The reference name field is required.',
            'slug.alpha_num' => 'The reference name may only contain letters and numbers.',
            'slug.min' => 'The reference name must be at least 3 characters.'
        ];
    }

}