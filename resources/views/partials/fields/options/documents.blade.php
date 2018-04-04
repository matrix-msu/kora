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
        <label for="filetype">Allowed File Types (<a target="_blank" class="field-meme-link underline-middle-hover" href="https://en.wikipedia.org/wiki/MIME">MIME</a>): </label>
        <?php
            $values = array();
            foreach(explode('[!]',\App\Http\Controllers\FieldController::getFieldOption($field, "FileTypes")) as $opt){
                $values[$opt] = $opt;
            }
        ?>
        {!! Form::select('filetype'.'[]',\App\FileTypeField::getMimeTypesClean(),
            explode('[!]',\App\Http\Controllers\FieldController::getFieldOption($field, "FileTypes")),
            ['class' => 'multi-select', 'Multiple', 'data-placeholder' => 'Search and Select the file types allowed here']) !!}
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Documents');
@stop