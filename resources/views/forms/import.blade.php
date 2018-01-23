@extends('app', ['page_title' => "Import Form Setup", 'page_class' => 'form-import-setup'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $proj->pid])
    @include('partials.menu.static', ['name' => 'Import Form Setup'])
@stop

@section('content')
    <span><h1>{{trans('projects_show.import')}}</h1></span>

    <hr>

    {!! Form::open(['url' => action('ImportController@importForm', ['pid' => $proj->pid]),'enctype' => 'multipart/form-data']) !!}
    <div class="form-group">
        {!! Form::label('form', 'Form (.k3Form): ') !!}
        {!! Form::file('form', ['class' => 'form-control', 'accept' => '.k3Form']) !!}
    </div>

    <div class="form-group">
        <button class="form-control btn btn-primary">{{trans('projects_show.importsubmit')}}</button>
    </div>

    {!! Form::close() !!}
@stop

@section('footer')

@stop