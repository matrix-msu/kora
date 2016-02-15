@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
@stop

@section('content')
    <h1>{{trans('fields_create.new')}} {{ $form->name }}</h1>

    <hr/>

    {!! Form::model($field = new \App\Field, ['url' => 'projects/'.$form->pid.'/forms/'.$form->fid,'onsubmit' => 'selectAll()']) !!}
        @include('fields.form', ['submitButtonText' => trans('fields_create.create'), 'pid' => $form->pid, 'fid' => $form->fid])
    {!! Form::close() !!}

    @include('errors.list')
@stop