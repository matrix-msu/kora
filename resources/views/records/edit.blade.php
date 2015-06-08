@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
@stop

@section('content')
    <h1>Edit Record</h1>

    <hr/>

    {!! Form::model($record,  ['method' => 'PATCH', 'action' => ['RecordController@update',$form->pid, $form->fid, $record->rid]]) !!}
    @include('records.form-edit',['submitButtonText' => 'Update Record', 'form' => $form])
    {!! Form::close() !!}

    @include('errors.list')
@stop