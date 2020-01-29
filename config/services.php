<?php

try {
    $url = url('').'/';
} catch (Error $e) {
    $url = '/';
}

return [

	/*
	|--------------------------------------------------------------------------
	| Third Party Services
	|--------------------------------------------------------------------------
	|
	| This file is for storing the credentials for third party services such
	| as Stripe, Mailgun, Mandrill, and others. This file provides a sane
	| default location for this type of information, allowing packages
	| to have a conventional place to find your various credentials.
	|
	*/

	'mailgun' => [
		'domain' => '',
		'secret' => '',
	],

	'mandrill' => [
		'secret' => '',
	],

	'ses' => [
		'key' => '',
		'secret' => '',
		'region' => 'us-east-1',
	],

	'stripe' => [
		'model'  => 'User',
		'secret' => '',
	],

    'gitlab' => [
        'client' => env('GITLAB_CLIENT',''),
        'client_id' => env('GITLAB_CLIENT_ID',''),
        'client_secret' => env('GITLAB_CLIENT_SECRET',''),
        'redirect' => $url.'login/gitlab/callback',
    ],

];
