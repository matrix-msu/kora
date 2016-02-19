@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $proj->pid])
@stop

@section('content')
    <span><h1>{{trans('projects_show.import')}}</h1></span>

    <hr>

    {!! Form::open(['url' => action('ImportController@importForm', ['pid' => $proj->pid]),'enctype' => 'multipart/form-data']) !!}
    <div class="form-group">
        {!! Form::label('form', 'Form (.form): ') !!}
        {!! Form::file('form', ['class' => 'form-control', 'accept' => '.form']) !!}
    </div>

    <div class="form-group">
        <button class="form-control btn btn-primary">{{trans('projects_show.importsubmit')}}</button>
    </div>

    {!! Form::close() !!}
@stop

@section('footer')

@stop