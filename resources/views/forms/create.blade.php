@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->pid])
@stop

@section('content')
    <h1>{{trans('forms_create.new')}} {{ $project->name }}</h1>

    <hr/>

    {!! Form::model($form = new \App\Form, ['url' => 'projects/'.$project->pid]) !!}
        @include('forms.form',['submitButtonText' => trans('forms_create.create'), 'pid' => $project->pid])
    {!! Form::close() !!}

    @include('errors.list')
@stop

@section('footer')
    <script>
        $('#admins').select2();
        $('#presets').select2({
            placeholder: '{{trans('forms_create.select')}}',
            allowClear: true
        });
    </script>
@stop