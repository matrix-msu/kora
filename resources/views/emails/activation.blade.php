@extends('email')

@section('main-text')
    Welcome to kora! Click below to activate your account and get started:
@endsection

@section('button-link')
    {{action('Auth\UserController@activate', ['token' => \Auth::user()->regtoken])}}
@endsection

@section('button-text')
    Activate Account
@endsection

@section('footer-text')
    If the link does not work, you may manually activate within kora using the token ({{\Auth::user()->regtoken}}) at
    the following url: <a href="{{ url('auth/activate') }}">{{ url('auth/activate') }}</a>
@endsection