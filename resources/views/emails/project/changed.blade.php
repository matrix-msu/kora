@extends('email')

@section('main-text')
You permissions have been updated for the following kora Project: {{$project->name}}
@endsection

@section('button-link')
{{action('ProjectController@show', ['id'=>$project->id])}}
@endsection

@section('button-text')
View Project
@endsection

@section('footer-text')
@php
if($group->name == $project->name. ' Default Group')
$gName = 'Default Group';
else if($group->name == $project->name. ' Admin Group')
$gName = 'Admin Group';
else
$gName = $group->name;
@endphp
You are a member of the “{{ $gName }}” permissions group. This means you can:
<br/>
- View the Project
@if($group->create == '1') <br/>- Create Forms @endif
@if($group->edit == '1') <br/>- Edit Forms @endif
@if($group->delete == '1') <br/>- Delete Forms @endif
<br/><br/>
Your permissions have been updated by {{ \Auth::user()->getFullName() }}
<br/>
({{ \Auth::user()->username }}, <a href="mailto:{{ \Auth::user()->email }}">{{ \Auth::user()->email }}</a>)
@endsection