@extends('email')

@section('main-text')
    The form ({{$myProj->name}} | {{$myForm->name}}), is requesting associator access to the following form:
@endsection

@section('project-text')
<div class="project-text">
    {{$theirProj->name}} | {{$theirForm->name}}
</div>
@endsection

@section('button-link')
    {{action('AssociationController@index', ['pid'=>$theirForm->id,'fid'=>$theirForm->id])}}
@endsection

@section('button-text')
    Go to Form Association Page
@endsection

@section('post-action-text')
    Visit the Form Associations page for “{{$theirProj->name}} | {{$theirForm->name}}” and Create a new Form Association to the “{{$myProj->name}} | {{$myForm->name}}” form.
@endsection