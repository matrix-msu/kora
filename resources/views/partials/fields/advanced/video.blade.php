{!! Form::hidden('advanced',true) !!}
<div class="form-group mt-xxxl">
    {!! Form::label('filesize','Max File Size (kb)') !!}
    <input type="number" name="filesize" class="text-input" step="1" min="0" placeholder="Enter max file size (kb) here">
</div>

<div class="form-group mt-xl">
    {!! Form::label('maxfiles','Max File Amount') !!}
    <input type="number" name="maxfiles" class="text-input" step="1" min="0" placeholder="Enter max file amount here">
</div>

<div class="form-group mt-xl">
    {!! Form::label('filetype','Allowed File Types') !!}
    {!! Form::select('filetype'.'[]',['video/mp4' => 'MP4'], getDefaultTypes('Video'),
        ['class' => 'multi-select', 'Multiple', 'data-placeholder' => 'Search and Select the file types allowed here']) !!}
</div>

<script>
    Kora.Fields.Options('Video');
</script>