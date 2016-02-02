@extends('fields.show')

@section('fieldOptions')

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateRequired', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('required',trans('fields_options_gallery.req').': ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_gallery.updatereq'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','FieldSize') !!}
    <div class="form-group">
        {!! Form::label('value',trans('fields_options_gallery.maxsize').' (kb): ') !!}
        <input type="number" name="value" class="form-control" step="1"
               value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "FieldSize") }}" min="0">
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_gallery.updatesize'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','ThumbSmall') !!}
    <div class="form-group">
        <?php
            $thumbSmCurr = explode('x',\App\Http\Controllers\FieldController::getFieldOption($field, "ThumbSmall"));
        ?>
        {!! Form::label('value_x',trans('fields_options_gallery.small').' (X): ') !!}
        <input type="number" name="value_x" class="form-control" step="any" value="{{$thumbSmCurr[0]}}" min="50" max="700">
        {!! Form::label('value_y',trans('fields_options_gallery.small').' (Y): ') !!}
        <input type="number" name="value_y" class="form-control" step="any" value="{{$thumbSmCurr[1]}}" min="50" max="700">
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_gallery.updatesmall'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','ThumbLarge') !!}
    <div class="form-group">
        <?php
        $thumbLrgCurr = explode('x',\App\Http\Controllers\FieldController::getFieldOption($field, "ThumbLarge"));
        ?>
        {!! Form::label('value_x',trans('fields_options_gallery.large').' (X): ') !!}
        <input type="number" name="value_x" class="form-control" step="1" value="{{$thumbLrgCurr[0]}}" min="50" max="700">
        {!! Form::label('value_y',trans('fields_options_gallery.large').' (Y): ') !!}
        <input type="number" name="value_y" class="form-control" step="1" value="{{$thumbLrgCurr[1]}}" min="50" max="700">
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_gallery.updatelarge'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','MaxFiles') !!}
    <div class="form-group">
        {!! Form::label('value',trans('fields_options_gallery.maxamount').': ') !!}
        <input type="number" name="value" class="form-control" step="1"
               value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "MaxFiles") }}" min="0">
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_gallery.updateamount'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','FileTypes') !!}
    <div class="form-group">
        {!! Form::label('value',trans('fields_options_gallery.types').' (MIME): ') !!}
        {!! Form::select('value'.'[]',['image/jpeg' => 'Jpeg','image/gif' => 'Gif','image/png' => 'Png','image/bmp' => 'Bmp'],
            explode('[!]',\App\Http\Controllers\FieldController::getFieldOption($field, "FileTypes")),
            ['class' => 'form-control filetypes', 'Multiple', 'id' => 'list'.$field->flid]) !!}
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_gallery.updatetypes'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    @include('errors.list')
@stop

@section('footer')
    <script>
        $('.filetypes').select2();
    </script>
@stop