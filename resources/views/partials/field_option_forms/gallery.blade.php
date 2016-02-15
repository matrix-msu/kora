<hr>
{!! Form::hidden('advance','true') !!}

<div class="form-group">
    {!! Form::label('filesize',trans('fields_options_gallery.maxsize').' (kb): ') !!}
    <input type="number" name="filesize" class="form-control" step="1"
           value="0" min="0">
</div>

<div class="form-group">
    {!! Form::label('small_x',trans('fields_options_gallery.small').' (X): ') !!}
    <input type="number" name="small_x" class="form-control" step="any" value="150" min="50" max="700">
    {!! Form::label('small_y',trans('fields_options_gallery.small').' (Y): ') !!}
    <input type="number" name="small_y" class="form-control" step="any" value="150" min="50" max="700">
</div>

<div class="form-group">
    {!! Form::label('large_x',trans('fields_options_gallery.large').' (X): ') !!}
    <input type="number" name="large_x" class="form-control" step="1" value="300" min="50" max="700">
    {!! Form::label('large_y',trans('fields_options_gallery.large').' (Y): ') !!}
    <input type="number" name="large_y" class="form-control" step="1" value="300" min="50" max="700">
</div>

<div class="form-group">
    {!! Form::label('maxfiles',trans('fields_options_gallery.maxamount').': ') !!}
    <input type="number" name="maxfiles" class="form-control" step="1"
           value="0" min="0">
</div>

<div class="form-group">
    {!! Form::label('filetype',trans('fields_options_gallery.types').' (MIME): ') !!}
    {!! Form::select('filetype'.'[]',['image/jpeg' => 'Jpeg','image/gif' => 'Gif','image/png' => 'Png','image/bmp' => 'Bmp'],
        '',
        ['class' => 'form-control filetypes', 'Multiple', 'id' => 'list']) !!}
</div>

<script>
    $('.filetypes').select2();
</script>