@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
@stop

@section('content')
    <h1>Create a New Record for {{ $form->name }}</h1>

    <hr/>

    {!! Form::model($record = new \App\Record, ['url' => 'projects/'.$form->pid.'/forms/'.$form->fid.'/records']) !!}
        @include('records.form',['submitButtonText' => 'Create Record', 'form' => $form])
    {!! Form::close() !!}

    @include('errors.list')
@stop