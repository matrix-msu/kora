@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
@stop

@section('content')
    <h1>{{trans('records_edit.edit')}}</h1>

    <hr/>

    {!! Form::model($record,  ['method' => 'PATCH', 'action' => ['RecordController@update',$form->pid, $form->fid, $record->rid],
        'enctype' => 'multipart/form-data', 'id' => 'new_record_form']) !!}
    @include('records.form-edit',['submitButtonText' => trans('records_edit.update'), 'form' => $form])
    {!! Form::close() !!}

    @include('errors.list')
@stop