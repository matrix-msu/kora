<hr>
{!! Form::hidden('advance','true') !!}

<div class="form-group">
    {!! Form::label('filesize',trans('fields_options_playlist.maxsize').' (kb): ') !!}
    <input type="number" name="filesize" class="form-control" step="1"
           value="0" min="0">
</div>

<div class="form-group">
    {!! Form::label('maxfiles',trans('fields_options_playlist.maxamount').': ') !!}
    <input type="number" name="maxfiles" class="form-control" step="1"
           value="0" min="0">
</div>

<div class="form-group">
    {!! Form::label('filetype',trans('fields_options_playlist.types').' (MIME): ') !!}
    {!! Form::select('filetype'.'[]',['audio/mp3' => 'MP3','audio/wav' => 'Wav','audio/ogg' => 'Ogg'],'',
        ['class' => 'form-control filetypes', 'Multiple', 'id' => 'list']) !!}
</div>

<script>
    $('.filetypes').select2();
</script>