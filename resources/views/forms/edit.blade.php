@extends('app')

@section('leftNavLinks')
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ $projName }}<b class="caret"></b></a>
        <ul class="dropdown-menu">
            <li><a href="{{ url('/projects/'.$form->pid) }}">Project Home</a></li>
        </ul>
    </li>
@stop

@section('content')
    <h1>Edit Form</h1>

    <hr/>

    {!! Form::model($form,  ['method' => 'PATCH', 'action' => ['FormController@update',$form->pid, $form->fid]]) !!}
    @include('forms.form',['submitButtonText' => 'Update Form', 'pid' => $form->pid])
    {!! Form::close() !!}

    @include('errors.list')
@stop