{!! Form::hidden('advanced',true) !!}
<div class="form-group mt-xxxl">
    {!! Form::label('filesize','Max File Size (kb)') !!}
    <input type="number" name="filesize" class="text-input" step="1" value="0" min="0">
</div>

<div class="form-group mt-xl half pr-m">
    {!! Form::label('small_x','Small Thumbnail (X)') !!}
    <input type="number" name="small_x" class="text-input" step="any" value="150" min="50" max="700">
</div>

<div class="form-group mt-xl half pl-m">
    {!! Form::label('small_y','Small Thumbnail (Y)') !!}
    <input type="number" name="small_y" class="text-input " step="any" value="150" min="50" max="700">
</div>

<div class="form-group">
    {{--This is a fake spacer for handling multiple half inputs in a row--}}
</div>

<div class="form-group mt-xl half pr-m">
    {!! Form::label('large_x','Large Thumbnail (X)') !!}
    <input type="number" name="large_x" class="text-input" step="1" value="300" min="50" max="700">
</div>

<div class="form-group mt-xl half pl-m">
    {!! Form::label('large_y','Large Thumbnail (Y)') !!}
    <input type="number" name="large_y" class="text-input" step="1" value="300" min="50" max="700">
</div>

<div class="form-group mt-xl">
    {!! Form::label('maxfiles','Max File Amount') !!}
    <input type="number" name="maxfiles" class="text-input" step="1" value="0" min="0">
</div>

<div class="form-group mt-xl">
    {!! Form::label('filetype','Allowed File Types') !!}
    {!! Form::select('filetype'.'[]',['image/jpeg' => 'Jpeg','image/gif' => 'Gif','image/png' => 'Png','image/bmp' => 'Bmp'],
        getDefaultTypes('Gallery'), ['class' => 'multi-select', 'Multiple', 'data-placeholder' => 'Search and Select the file types allowed here']) !!}
</div>

<script>
    Kora.Fields.Options('Gallery');
</script>