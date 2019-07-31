@extends('email')

@section('main-text')
    You permissions have been updated for the following Kora Project:
@endsection

@section('project-text')
<div class="project-text">
    {{$project->name}}
</div>
@endsection

@section('button-link')
    {{action('ProjectController@show', ['id'=>$project->id])}}
@endsection

@section('button-text')
    View Project
@endsection

@section('post-action-text')
    @php
        if($group->name == $project->name. ' Default Group')
            $gName = 'Default Group';
        else if($group->name == $project->name. ' Admin Group')
            $gName = 'Admin Group';
        else
            $gName = $group->name;
    @endphp
    You are a member of the “{{ $gName }}” permissions group. This means you can:
    <div class="top-list-item">&bull; View Project</div>
    @if($group->create)<div>&bull; Create new Forms</div>@endif
    @if($group->edit)<div>&bull; Edit Forms</div>@endif
    @if($group->delete)<div>&bull; Delete Forms</div>@endif
@endsection

@section('footer-text')
    Your permissions have been updated by {{ \Auth::user()->getFullName() }}
@endsection

@section('footer-email')
    ({{\Auth::user()->username}}, <a class="bold-highlight" href="mailto:{{\Auth::user()->email}}">{{\Auth::user()->email}}</a>)
@endsection