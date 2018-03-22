@extends('fields.show')

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('filesize','Max File Size (kb): ') !!}
        <input type="number" name="filesize" class="text-input" step="1"
               value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "FieldSize") }}" min="0">
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('filetype','Allowed File Types (MIME): ') !!}
        {!! Form::select('filetype'.'[]',['obj' => 'OBJ','stl' => 'STL','image/jpeg' => 'JPEG Texture',
            'image/png' => 'PNG Texture','application/octet-stream' => 'Other'],
            explode('[!]',\App\Http\Controllers\FieldController::getFieldOption($field, "FileTypes")),
            ['class' => 'multi-select', 'Multiple']) !!}
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('color','Model Color: ') !!}
        <input type="color" name="color" class="text-input color-input"
               value="{{\App\Http\Controllers\FieldController::getFieldOption($field, "ModelColor")}}">
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('backone','Background Color One: ') !!}
        <input type="color" name="backone" class="text-input color-input"
               value="{{\App\Http\Controllers\FieldController::getFieldOption($field, "BackColorOne")}}">
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('backtwo','Background Color Two: ') !!}
        <input type="color" name="backtwo" class="text-input color-input"
               value="{{\App\Http\Controllers\FieldController::getFieldOption($field, "BackColorTwo")}}">
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('3D-Model');
@stop