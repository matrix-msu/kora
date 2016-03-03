@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
@stop

@section('content')
    <span><h1>Import Records</h1></span>

    <hr>

    <div class="form-group">
        {!! Form::label('xml', 'Record XML: ') !!}
        {!! Form::file('xml', ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('zip', 'Record File Zip: ') !!}
        {!! Form::file('zip', ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        <button type="button" class="form-control btn btn-primary" id="submit_files">Submit Files</button>
    </div>
@stop

@section('footer')

@stop