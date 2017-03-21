@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $proj->pid])
@stop

@section('content')
    <span><h1 style="background-color:#cc9933">{{trans('forms_importk2.import')}}</h1></span>

    <hr>

    <div class="form-group">
        {{trans('forms_importk2.text')}}
    </div>
    <div class="form-group">
        {{trans('forms_importk2.text2')}}
    </div>

    {!! Form::open(['url' => action('ImportController@importFormK2', ['pid' => $proj->pid]),'enctype' => 'multipart/form-data', 'id' => 'k2_form']) !!}
    <div class="form-group" style="background-color:#7A96BD">
        {!! Form::label('form', trans('forms_importk2.schemexml').': ') !!}
        {!! Form::file('form', ['class' => 'form-control', 'accept' => '.xml']) !!}
    </div>

    <div class="form-group" style="background-color:#7A96BD">
        {!! Form::label('records', trans('forms_importk2.recordxml').': ') !!}
        {!! Form::file('records', ['class' => 'form-control', 'accept' => '.xml']) !!}
    </div>

    <div class="form-group" style="background-color:#7A96BD">
        {!! Form::label('files', trans('forms_importk2.filezip').': ') !!}
        {!! Form::file('files', ['class' => 'form-control', 'accept' => '.zip']) !!}
    </div>

    <div class="form-group" id="k2_submit">
        <button class="form-control btn btn-primary">{{trans('forms_importk2.importsubmit')}}</button>
    </div>

    <div style="display:none;" id="search_progress" class="progress">
        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
            {{trans('update_index.loading')}}
        </div>
    </div>

    {!! Form::close() !!}
@stop

@section('footer')
    <script>
        $("#k2_form").submit(function(e) { $("#search_progress").slideDown(200); $("#k2_submit").slideUp(200);});
    </script>
@stop