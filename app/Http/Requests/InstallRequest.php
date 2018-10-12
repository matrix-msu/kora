<?php namespace App\Http\Requests;

class InstallRequest extends Request {

    /*
    |--------------------------------------------------------------------------
    | Install Request
    |--------------------------------------------------------------------------
    |
    | This request handles validation of request inputs for Installation
    |
    */

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
            'db_host'=>'required',
            'db_database'=>'required|alpha_dash',
            'db_username'=>'required',
            'db_password'=>'required',
            'db_prefix'=>'required|alpha_dash',
            'user_username'=>'required|alpha_dash',
            'user_email'=>'required|email',
            'user_password'=>'required|same:user_confirmpassword',
            'user_confirmpassword'=>'required',
            'user_firstname'=>'required',
            'user_lastname'=>'required',
            'user_organization'=>'required',
            'user_language'=>'required',
            'user_profile'=>'',
            'mail_host'=>'required',
            'mail_from_address'=>'required|email',
            'mail_from_name'=>'required',
            'mail_username'=>'required',
            'mail_password'=>'required',
            'recaptcha_public_key'=>'required',
            'recaptcha_private_key'=>'required'
        ];
    }
}
