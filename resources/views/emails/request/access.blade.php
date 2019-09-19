@extends('email')

@section('main-text')
{{ \Auth::user()->getFullName() }} from {{ \Auth::user()->preferences['organization'] }} is requesting access to the following kora Project: {{$project->name}}
<br/><br/>
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
<br/>
({{ \Auth::user()->username }}, <a href="mailto:{{ \Auth::user()->email }}">{{ \Auth::user()->email }}</a>)
@endsection