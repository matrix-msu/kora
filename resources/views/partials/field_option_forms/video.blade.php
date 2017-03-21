<hr>
{!! Form::hidden('advance','true') !!}

<div class="form-group">
    {!! Form::label('filesize',trans('fields_options_video.maxsize').' (kb): ') !!}
    <input type="number" name="filesize" class="form-control" step="1"
           value="0" min="0">
</div>

<div class="form-group">
    {!! Form::label('maxfiles',trans('fields_options_video.maxamount').': ') !!}
    <input type="number" name="maxfiles" class="form-control" step="1"
           value="0" min="0">
</div>

<div class="form-group">
    {!! Form::label('filetype',trans('fields_options_video.types').'(MIME): ') !!}
    {!! Form::select('filetype'.'[]',['video/mp4' => 'MP4','video/ogg' => 'OGV'],'',
        ['class' => 'form-control filetypes', 'Multiple', 'id' => 'list']) !!}
</div>

<script>
    $('.filetypes').select2();
</script>