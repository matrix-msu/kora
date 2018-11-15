@extends('email')

@section('main-text')
    <span class="green">{{ $sender->first_name }} {{ $sender->last_name }}</span> has invited you to join them on Kora!
@endsection

@section('sub-text')
    "{{ $personal_message }}"
@endsection

@section('button-link')
    {{ action('Auth\UserController@activate', ['token' => $token]) }}
@endsection

@section('button-text')
    Accept Invite
@endsection

@section('footer-text')
    <span class="green">Kora</span> is an open-source, database-driven, online digital repository application for complex digital objects. Kora allows you to store, manage, and publish digital objects, each with corresponding metadata into a single record, enhancing the research and educational value of each.
@endsection

@section('footer-email')
    You have been invited by <span class="green">{{ $sender->first_name }} {{ $sender->last_name }}</span><br>
    ({{ $sender->username }}, {{ $sender->email }})
@endsection



{{--
{{trans('emails_batch-activation.welcome')}} Kora 3! <br/>
{{trans('emails_batch-activation.clickhere')}}: <a href="{{action('Auth\UserController@activate', ['token' => $token])}}">{{trans('emails_batch-activation.activate')}}</a>. <br/>
{{trans('emails_batch-activation.token')}}: {{$token}}. <br/>
{{trans('emails_batch-activation.user')}}: {{$username}}. <br/>
{{trans('emails_batch-activation.pass')}}: {{$password}}.  {{trans('emails_batch-activation.temp')}}!<br/>
--}}
