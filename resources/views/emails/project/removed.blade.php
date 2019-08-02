@extends('email')

@section('main-text')
You have been removed from the following kora Project: {{$project->name}}
@endsection

@section('button-link')
{{action('ProjectController@index')}}
@endsection

@section('button-text')
View Projects Page
@endsection

@section('footer-text')
Your permissions have been updated by {{ \Auth::user()->getFullName() }}
<br/>
({{ \Auth::user()->username }}, <a href="mailto:{{ \Auth::user()->email }}">{{ \Auth::user()->email }}</a>)
@endsection