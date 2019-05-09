@extends('email')

@section('main-text')
    You have been removed from the following Kora Project:
@endsection

@section('project-text')
<div class="project-text">
    {{$project->name}}
</div>
@endsection

@section('button-link')
    {{action('ProjectController@index')}}
@endsection

@section('button-text')
    View Projects Page
@endsection

@section('footer-text')
    Your permissions have been updated by {{ \Auth::user()->getFullName() }}
@endsection

@section('footer-email')
    ({{\Auth::user()->username}}, <a class="bold-highlight" href="mailto:{{\Auth::user()->email}}">{{\Auth::user()->email}}</a>)
@endsection