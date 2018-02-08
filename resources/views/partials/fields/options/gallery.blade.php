@extends('fields.show')

@section('fieldOptions')
    <?php
        $thumbSmCurr = explode('x',\App\Http\Controllers\FieldController::getFieldOption($field, "ThumbSmall"));
        $thumbLrgCurr = explode('x',\App\Http\Controllers\FieldController::getFieldOption($field, "ThumbLarge"));
    ?>

    <div class="form-group">
        {!! Form::label('filesize','Max File Size (kb): ') !!}
        <input type="number" name="filesize" class="text-input" step="1"
               value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "FieldSize") }}" min="0">
    </div>

    <div class="form-group mt-xl half pr-m">
        {!! Form::label('small_x','Small Thumbnail (X): ') !!}
        <input type="number" name="small_x" class="text-input" step="any" value="{{$thumbSmCurr[0]}}" min="50" max="700">
    </div>

    <div class="form-group mt-xl half pl-m">
        {!! Form::label('small_y','Small Thumbnail (Y): ') !!}
        <input type="number" name="small_y" class="text-input" step="any" value="{{$thumbSmCurr[1]}}" min="50" max="700">
    </div>

    <div class="form-group">
        {{--This is a fake spacer for handling multiple half inputs in a row--}}
    </div>

    <div class="form-group mt-xl half pr-m">
        {!! Form::label('large_x','Large Thumbnail (X): ') !!}
        <input type="number" name="large_x" class="text-input" step="1" value="{{$thumbLrgCurr[0]}}" min="50" max="700">
    </div>

    <div class="form-group mt-xl half pl-m">
        {!! Form::label('large_y','Large Thumbnail (Y): ') !!}
        <input type="number" name="large_y" class="text-input" step="1" value="{{$thumbLrgCurr[1]}}" min="50" max="700">
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('maxfiles','Max File Amount: ') !!}
        <input type="number" name="maxfiles" class="text-input" step="1"
               value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "MaxFiles") }}" min="0">
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('filetype','Allowed File Types: ') !!}
        {!! Form::select('filetype'.'[]',['image/jpeg' => 'Jpeg','image/gif' => 'Gif','image/png' => 'Png','image/bmp' => 'Bmp'],
            explode('[!]',\App\Http\Controllers\FieldController::getFieldOption($field, "FileTypes")), ['class' => 'multi-select', 'Multiple']) !!}
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Gallery');
@stop