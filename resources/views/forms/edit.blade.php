@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
@stop

@section('content')
    <h1>{{trans('forms_edit.edit')}}</h1>

    <hr/>

    {!! Form::model($form,  ['method' => 'PATCH', 'action' => ['FormController@update',$form->pid, $form->fid]]) !!}
    @include('forms.form',['submitButtonText' => trans('forms_edit.update'), 'pid' => $form->pid])
    {!! Form::close() !!}

    @include('errors.list')
@stop