@extends('fields.show')

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('filesize','Max File Size (kb)') !!}
        <div class="number-input-container number-input-container-js">
            <input type="number" name="filesize" class="text-input" step="1"
               value="{{ $field['options']["FieldSize"] }}" min="0"
               placeholder="Enter max file size (kb) here">
        </div>
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('filetype','Allowed File Types (MIME)') !!}
        {!! Form::select('filetype'.'[]',['obj' => 'OBJ','stl' => 'STL','image/jpeg' => 'JPEG Texture',
            'image/png' => 'PNG Texture','application/octet-stream' => 'Other'], $field['options']["FileTypes"],
            ['class' => 'multi-select', 'Multiple']) !!}
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('color','Model Color') !!}
        <input type="color" name="color" class="text-input color-input"
               value="{{ $field['options']["ModelColor"] }}">
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('backone','Background Color One') !!}
        <input type="color" name="backone" class="text-input color-input"
               value="{{ $field['options']["BackColorOne"] }}">
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('backtwo','Background Color Two') !!}
        <input type="color" name="backtwo" class="text-input color-input"
               value="{{ $field['options']["BackColorTwo"] }}">
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('3D-Model');
@stop