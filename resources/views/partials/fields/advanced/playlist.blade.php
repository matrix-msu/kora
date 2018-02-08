<div class="form-group mt-xl">
    {!! Form::label('filesize','Max File Size (kb): ') !!}
    <input type="number" name="filesize" class="text-input" step="1"  value="0" min="0">
</div>

<div class="form-group mt-xl">
    {!! Form::label('maxfiles','Max File Amount: ') !!}
    <input type="number" name="maxfiles" class="text-input" step="1" value="0" min="0">
</div>

<div class="form-group mt-xl">
    {!! Form::label('filetype','Allowed File Types: ') !!}
    {!! Form::select('filetype'.'[]',['audio/mp3' => 'MP3','audio/wav' => 'Wav','audio/ogg' => 'Ogg'],
        null, ['class' => 'multi-select', 'Multiple']) !!}
</div>

<script>
    Kora.Fields.Options('Playlist');
</script>