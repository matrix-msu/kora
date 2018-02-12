@extends('fields.show')

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('filesize','Max File Size (kb): ') !!}
        <input type="number" name="filesize" class="text-input" step="1"
               value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "FieldSize") }}" min="0">
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('maxfiles','Max File Amount: ') !!}
        <input type="number" name="maxfiles" class="text-input" step="1"
               value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "MaxFiles") }}" min="0">
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('filetype','Allowed File Types: ') !!}
        {!! Form::select('filetype'.'[]',['video/mp4' => 'MP4','video/ogg' => 'OGV'],
            explode('[!]',\App\Http\Controllers\FieldController::getFieldOption($field, "FileTypes")),
            ['class' => 'multi-select', 'Multiple', 'data-placeholder' => 'Search and Select the file types allowed here']) !!}
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Video');
@stop