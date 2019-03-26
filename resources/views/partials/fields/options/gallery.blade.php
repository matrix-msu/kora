@extends('fields.show')

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('filesize','Max File Size (kb)') !!}
        <div class="number-input-container number-input-container-js">
            <input type="number" name="filesize" class="text-input" step="1"
               value="{{ $field['options']['FieldSize'] }}" min="0"
			   placeholder="Enter max file size (kb) here">
        </div>
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('maxfiles','Max File Amount') !!}
        <div class="number-input-container number-input-container-js">
            <input type="number" name="maxfiles" class="text-input" step="1"
               value="{{ $field['options']['MaxFiles'] }}" min="0"
			   placeholder="Enter max file amount here">
        </div>
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('filetype','Allowed File Types') !!}
        {!! Form::select('filetype'.'[]',['image/jpeg' => 'Jpeg','image/gif' => 'Gif','image/png' => 'Png','image/bmp' => 'Bmp'],
            $field['options']['FileTypes'], ['class' => 'multi-select', 'Multiple', 'data-placeholder' => 'Search and Select the file types allowed here']) !!}
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Inputs.Number();
    Kora.Fields.Options('Gallery');
@stop