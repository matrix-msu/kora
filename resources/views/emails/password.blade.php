@extends('email')

@section('main-text')
You have requested a link to reset your password.
To reset your password, click the following link and follow the instructions:
@endsection

@section('button-link')
{{url('password/reset/'.$token)}}
@endsection

@section('button-text')
Reset Password
@endsection