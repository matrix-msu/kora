{!! Form::hidden('advanced',true) !!}
<div class="form-group mt-xxxl">
    {!! Form::label('filesize','Max File Size (kb)') !!}
    <div class="number-input-container number-input-container-js">
        <input type="number" name="filesize" class="text-input" step="1" min="0" value="" placeholder="Enter max file size (kb) here">
    </div>
</div>

<div class="form-group mt-xl">
    {!! Form::label('maxfiles','Max File Amount') !!}
    <div class="number-input-container number-input-container-js">
        <input type="number" name="maxfiles" class="text-input" step="1" min="0" value="" placeholder="Enter max file amount here">
    </div>
</div>

<div class="form-group mt-xl">
    <label for="filetype">Allowed File Types (<a target="_blank" class="field-meme-link underline-middle-hover" href="https://en.wikipedia.org/wiki/MIME">MIME</a>): </label>
    {!! Form::select('filetype'.'[]',\App\KoraFields\FileTypeField::getMimeTypesClean(), null,
        ['class' => 'multi-select', 'Multiple', 'data-placeholder' => 'Search and Select the file types allowed here']) !!}

	<p class="sub-text mt-sm">
        If you leave this field blank, all file types will be allowed.
    </p>
</div>

<script>
    Kora.Fields.Options('Documents');
    Kora.Inputs.Number();
</script>
