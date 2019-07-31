@extends('email')

@section('main-text')
    {{ $sender->getFullName() }} has invited you to join them on the following kora Project: @if(isset($project)) {{ $project->name }} @else kora! @endif
    @if(!empty($personal_message))
        <br>
        "{{ $personal_message }}"
    @endif
@endsection

@section('button-link')
    {{ action('Auth\UserController@activateFromInvite', ['token' => $token]) }}
@endsection

@section('button-text')
    Accept Invite
@endsection

@section('footer-text')
    @if(!is_null($project))
        Once you accept, you'll be added to the {{ $projectGroup->name }} permissions group. This means you'll be able to:
        <br/>
        - View the Project
        @if($projectGroup->create == '1') <br/>- Create Forms @endif
        @if($projectGroup->edit == '1') <br/>- Edit Forms @endif
        @if($projectGroup->delete == '1') <br/>- Delete Forms @endif
        <br/><br/>
    @endif
    kora is an open-source, database-driven, online digital repository application for complex digital objects. kora allows you to store, manage, and publish digital objects, each with corresponding metadata into a single record, enhancing the research and educational value of each.
    <br/><br/>
    You have been invited by {{ $sender->getFullName() }}
    <br/>
    ({{ $sender->username }}, <a href="mailto:{{ $sender->email }}">{{ $sender->email }}</a>)
@endsection
