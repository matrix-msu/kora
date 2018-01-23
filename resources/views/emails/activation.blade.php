@extends('email')

@section('main-text')
    Welcome, to Kora! Click below to activate your account and get started:
@endsection

@section('button-link')
    {{action('Auth\UserController@activate', ['token' => \Auth::user()->regtoken])}}
@endsection

@section('button-text')
    Activate Account
@endsection

@section('post-action-text')
    If the link does not work, you may manually activate within Kora using the token ({{\Auth::user()->regtoken}}) at
    the following url: {{ config('app.url') }}auth/activate
@endsection