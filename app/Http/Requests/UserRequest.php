<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
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
    public function rules()
    {
        switch($this->method())  {
            case 'POST':
                return [
                    'username' => 'required|max:255|unique:users',
                    'email' => 'required|email|max:255|unique:users',
                    'password' => 'required|confirmed|min:6',
                    'language'=> 'required|alpha|max:2',
                    'first_name'=> 'required',
                    'last_name'=> 'required',
                    'organization'=> 'required',
                ];
            case 'PATCH':
                return [
                    'password' => 'confirmed|min:6',
                    'language'=> 'required|alpha|max:2',
                    'first_name'=> 'required',
                    'last_name'=> 'required',
                    'organization'=> 'required',
                ];
            default:
                break;
        }
    }
}
