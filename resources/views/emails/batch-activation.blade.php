@extends('email')

@section('main-text')
    <span class="green">{{ $sender->first_name }} {{ $sender->last_name }}</span> 
    has invited you to join them on 
    @if (isset($project))
        the following Kora Project: <br>
        <span class="green project-title">{{ $project->name }}</span>
    @else
        Kora!
    @endif
@endsection

@section('sub-text')
    "{{ $personal_message }}"
@endsection

@section('button-link')
    {{ action('Auth\UserController@activateFromInvite', ['token' => $token]) }}
@endsection

@section('button-text')
    Accept Invite
@endsection

@if (isset($project))
    @section('post-action-text')
        Once you accept, you'll be added to the {{ $projectGroup->name }} permissions group.  This means you'll be able to:
        <ul>
            <li>View the Project</li>
            {{ $projectGroup->create == '1' ? '<li>Create Forms</li>' : null }}
            {{ $projectGroup->edit == '1' ? '<li>Edit Forms</li>' : null }}
            {{ $projectGroup->delete == '1' ? '<li>Delete Forms</li>' : null }}
        </ul>
    @endsection
@endif

@section('pre-footer-text')
    <span class="green">Kora</span> is an open-source, database-driven, online digital repository application for complex digital objects. Kora allows you to store, manage, and publish digital objects, each with corresponding metadata into a single record, enhancing the research and educational value of each.
@endsection

@section('footer-text')
    You have been invited by {{ $sender->first_name }} {{ $sender->last_name }}
@endsection

@section('footer-email')
    ({{ $sender->username }}, <span class="green-nolink"><a href="#" class="green-nolink">{{ $sender->email }}</a></span>)
@endsection

{{--
{{trans('emails_batch-activation.welcome')}} Kora 3! <br/>
{{trans('emails_batch-activation.clickhere')}}: <a href="{{action('Auth\UserController@activate', ['token' => $token])}}">{{trans('emails_batch-activation.activate')}}</a>. <br/>
{{trans('emails_batch-activation.token')}}: {{$token}}. <br/>
{{trans('emails_batch-activation.user')}}: {{$username}}. <br/>
{{trans('emails_batch-activation.pass')}}: {{$password}}.  {{trans('emails_batch-activation.temp')}}!<br/>
--}}
