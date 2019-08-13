@extends('email')

@section('main-text')
    <span class="bold-highlight">{{ \Auth::user()->getFullName() }}</span> is requesting access to the following Kora Project:
@endsection

@section('project-text')
<div class="project-text">
    {{$project->name}}
</div>
@endsection

@section('sub-text')
    As an admin of {{$project->name}}, you may add them to a permissions group within the project.
@endsection

@section('button-link')
    {{action('ProjectGroupController@index', ['pid'=>$project->id])}}
@endsection

@section('button-text')
    Go to Project Permissions Page
@endsection

@section('footer-text')
    Permissions are being requested by {{ \Auth::user()->getFullName() }}
@endsection

@section('footer-email')
    ({{\Auth::user()->username}}, <a class="bold-highlight" href="mailto:{{\Auth::user()->email}}">{{\Auth::user()->email}}</a>)
@endsection