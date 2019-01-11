<?php namespace App\Services;

use App\User;
use Illuminate\Contracts\Auth\Registrar as RegistrarContract;
use Illuminate\Contracts\Validation\Validator;

class Registrar implements RegistrarContract {

    /*
    |--------------------------------------------------------------------------
    | Registrar
    |--------------------------------------------------------------------------
    |
    | This service handles creation and validation of new user
    |
    */

    /**
	 * Get a validator for an incoming registration request.
	 *
	 * @param  array $data - User data to validate
	 * @return Validator
	 */
	public function validator(array $data) {
		return Validator::make($data, [
			'username' => 'required|max:255|unique:users',
			'email' => 'required|email|max:255|unique:users',
			'password' => 'required|confirmed|min:6'
		]);
	}

	/**
	 * Create a new user instance after a valid registration.
	 *
	 * @param  array $data - New user data
	 * @return User
	 */
	public function create(array $data) {
		return User::create([
			'username' => $data['username'],
			'email' => $data['email'],
			'password' => bcrypt($data['password']),
            'regtoken' => $data['regtoken']
		]);
	}

}
