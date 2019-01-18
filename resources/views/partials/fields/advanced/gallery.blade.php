{!! Form::hidden('advanced',true) !!}
<div class="form-group mt-xxxl">
    {!! Form::label('filesize','Max File Size (kb)') !!}
    <div class="number-input-container number-input-container-js">
      <input type="number" name="filesize" class="text-input" step="1" min="0" placeholder="Enter max file size (kb) here">
    </div>
</div>

<div class="form-group mt-xl half pr-m">
    {!! Form::label('small_x','Small Thumbnail (X)') !!}
    <div class="number-input-container number-input-container-js">
      <input type="number" name="small_x" class="text-input" step="any" value="150" min="50" max="700" placeholder="Enter small thumbnail (X) here">
    </div>
</div>

<div class="form-group mt-xl half pl-m">
    {!! Form::label('small_y','Small Thumbnail (Y)') !!}
    <div class="number-input-container number-input-container-js">
      <input type="number" name="small_y" class="text-input " step="any" value="150" min="50" max="700" placeholder="Enter small thumbnail (Y) here">
    </div>
</div>

<div class="form-group">
    {{--This is a fake spacer for handling multiple half inputs in a row--}}
</div>

<div class="form-group mt-xl half pr-m">
    {!! Form::label('large_x','Large Thumbnail (X)') !!}
    <div class="number-input-container number-input-container-js">
      <input type="number" name="large_x" class="text-input" step="1" value="300" min="50" max="700" placeholder="Enter large thumbnail (X) here">
    </div>
</div>

<div class="form-group mt-xl half pl-m">
    {!! Form::label('large_y','Large Thumbnail (Y)') !!}
    <div class="number-input-container number-input-container-js">
      <input type="number" name="large_y" class="text-input" step="1" value="300" min="50" max="700" placeholder="Enter large thumbnail (Y) here">
    </div>
</div>

<div class="form-group mt-xl">
    {!! Form::label('maxfiles','Max File Amount') !!}
    <div class="number-input-container number-input-container-js">
      <input type="number" name="maxfiles" class="text-input" step="1" min="0" placeholder="Enter max file amount here">
    </div>
</div>

<div class="form-group mt-xl">
    {!! Form::label('filetype','Allowed File Types') !!}
    {!! Form::select('filetype'.'[]',['image/jpeg' => 'Jpeg','image/gif' => 'Gif','image/png' => 'Png','image/bmp' => 'Bmp'],
        getDefaultTypes('Gallery'), ['class' => 'multi-select', 'Multiple', 'data-placeholder' => 'Search and Select the file types allowed here']) !!}
</div>

<script>
    Kora.Inputs.Number();
    Kora.Fields.Options('Gallery');
</script>
