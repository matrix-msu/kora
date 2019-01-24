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
        <label for="filetype">Allowed File Types (<a target="_blank" class="field-meme-link underline-middle-hover" href="https://en.wikipedia.org/wiki/MIME">MIME</a>)</label>
        @php
            $values = array();
            foreach($field['options']['FileTypes'] as $opt){
                $values[$opt] = $opt;
            }
        @endphp
        {!! Form::select('filetype'.'[]',\App\KoraFields\FileTypeField::getMimeTypesClean(),
            $field['options']['FileTypes'],
            ['class' => 'multi-select', 'Multiple', 'data-placeholder' => 'Search and Select the file types allowed here']) !!}

		<p class="sub-text mt-sm">
			If you leave this field blank, all file types will be allowed.
		</p>
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Documents');
    Kora.Inputs.Number();
@stop
