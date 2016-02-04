@extends('fields.show')

@section('fieldOptions')

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['OptionController@updateGallery', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('required',trans('fields_options_gallery.req').': ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>

    {!! Form::hidden('option','FieldSize') !!}
    <div class="form-group">
        {!! Form::label('filesize',trans('fields_options_gallery.maxsize').' (kb): ') !!}
        <input type="number" name="filesize" class="form-control" step="1"
               value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "FieldSize") }}" min="0">
    </div>

    {!! Form::hidden('option','ThumbSmall') !!}
    <div class="form-group">
        <?php
            $thumbSmCurr = explode('x',\App\Http\Controllers\FieldController::getFieldOption($field, "ThumbSmall"));
        ?>
        {!! Form::label('small_x',trans('fields_options_gallery.small').' (X): ') !!}
        <input type="number" name="small_x" class="form-control" step="any" value="{{$thumbSmCurr[0]}}" min="50" max="700">
        {!! Form::label('small_y',trans('fields_options_gallery.small').' (Y): ') !!}
        <input type="number" name="small_y" class="form-control" step="any" value="{{$thumbSmCurr[1]}}" min="50" max="700">
    </div>

    {!! Form::hidden('option','ThumbLarge') !!}
    <div class="form-group">
        <?php
        $thumbLrgCurr = explode('x',\App\Http\Controllers\FieldController::getFieldOption($field, "ThumbLarge"));
        ?>
        {!! Form::label('large_x',trans('fields_options_gallery.large').' (X): ') !!}
        <input type="number" name="large_x" class="form-control" step="1" value="{{$thumbLrgCurr[0]}}" min="50" max="700">
        {!! Form::label('large_y',trans('fields_options_gallery.large').' (Y): ') !!}
        <input type="number" name="large_y" class="form-control" step="1" value="{{$thumbLrgCurr[1]}}" min="50" max="700">
    </div>

    {!! Form::hidden('option','MaxFiles') !!}
    <div class="form-group">
        {!! Form::label('maxfiles',trans('fields_options_gallery.maxamount').': ') !!}
        <input type="number" name="maxfiles" class="form-control" step="1"
               value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "MaxFiles") }}" min="0">
    </div>

    {!! Form::hidden('option','FileTypes') !!}
    <div class="form-group">
        {!! Form::label('filetype',trans('fields_options_gallery.types').' (MIME): ') !!}
        {!! Form::select('filetype'.'[]',['image/jpeg' => 'Jpeg','image/gif' => 'Gif','image/png' => 'Png','image/bmp' => 'Bmp'],
            explode('[!]',\App\Http\Controllers\FieldController::getFieldOption($field, "FileTypes")),
            ['class' => 'form-control filetypes', 'Multiple', 'id' => 'list'.$field->flid]) !!}
    </div>

    <div class="form-group">
        {!! Form::submit(trans('field_options_generic.submit',['field'=>$field->name]),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    @include('errors.list')
@stop

@section('footer')
    <script>
        $('.filetypes').select2();
    </script>
@stop