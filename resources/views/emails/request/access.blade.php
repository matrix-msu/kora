@extends('email')

@section('main-text')
    <span class="bold-highlight">{{\Auth::user()->first_name}} {{\Auth::user()->last_name}}</span> is requesting access to the following Kora Project:
@endsection

@section('project-text')
    {{$project->name}}
@endsection

@section('sub-text')
    As an admin of {{$project->name}}, you may add them to a permissions group within the project.
@endsection

@section('button-link')
    {{action('ProjectGroupController@index', ['pid'=>$project->pid])}}
@endsection

@section('button-text')
    Go to Project Permissions Page
@endsection

@section('footer-text')
    Permissions are being requested by {{\Auth::user()->first_name}} {{\Auth::user()->last_name}}
@endsection

@section('footer-email')
    ({{\Auth::user()->username}}, <a class="bold-highlight" href="mailto:{{\Auth::user()->email}}">{{\Auth::user()->email}}</a>)
@endsection