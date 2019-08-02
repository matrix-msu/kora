@extends('email')

@section('main-text')
The form ({{$myProj->name}} | {{$myForm->name}}), is requesting associator access to the following form: {{$thierProj->name}} | {{$thierForm->name}}
@endsection

@section('button-link')
{{action('AssociationController@index', ['pid'=>$thierForm->id,'fid'=>$thierForm->id])}}
@endsection

@section('button-text')
Go to Form Association Page
@endsection

@section('footer-text')
Visit the Form Associations page for “{{$thierProj->name}} | {{$thierForm->name}}” and Create a new Form Association to the “{{$myProj->name}} | {{$myForm->name}}” form.
@endsection