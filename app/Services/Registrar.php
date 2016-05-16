<?php namespace App\Services;

use App\User;
use Validator;
use Illuminate\Contracts\Auth\Registrar as RegistrarContract;

class Registrar implements RegistrarContract {

	/**
	 * Get a validator for an incoming registration request.
	 *
	 * @param  array  $data
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
	public function validator(array $data)
	{
		return Validator::make($data, [
			'username' => 'required|max:255|unique:users', //Check to not contain 'a'
			'email' => 'required|email|max:255|unique:users',
			'password' => 'required|confirmed|min:6',
			'language'=> 'required|alpha|max:2',
		]);
	}

	/**
	 * Create a new user instance after a valid registration.
	 *
	 * @param  array  $data
	 * @return User
	 */
	public function create(array $data)
	{
		return User::create([
			'username' => $data['username'],
			'name' => $data['name'],
			'email' => $data['email'],
			'password' => bcrypt($data['password']),
			'organization' => $data['organization'],
            'regtoken' => $data['regtoken'],
			'language' => $data['language'],
		]);
	}

}
