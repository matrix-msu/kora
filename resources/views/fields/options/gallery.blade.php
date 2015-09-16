@extends('fields.show')

@section('fieldOptions')

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateRequired', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('required','Required: ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit("Update Required",['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','FieldSize') !!}
    <div class="form-group">
        {!! Form::label('value','Max Field Size (kb): ') !!}
        <input type="number" name="value" class="form-control" step="1"
               value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "FieldSize") }}" min="0">
    </div>
    <div class="form-group">
        {!! Form::submit("Update Field Size",['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','ThumbSmall') !!}
    <div class="form-group">
        <?php
            $thumbSmCurr = explode('x',\App\Http\Controllers\FieldController::getFieldOption($field, "ThumbSmall"));
        ?>
        {!! Form::label('value_x','Small Thumbnail Size (X): ') !!}
        <input type="number" name="value_x" class="form-control" step="any" value="{{$thumbSmCurr[0]}}" min="50" max="700">
        {!! Form::label('value_y','Small Thumbnail Size (Y): ') !!}
        <input type="number" name="value_y" class="form-control" step="any" value="{{$thumbSmCurr[1]}}" min="50" max="700">
    </div>
    <div class="form-group">
        {!! Form::submit("Update Small Thumbnail Size",['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','ThumbLarge') !!}
    <div class="form-group">
        <?php
        $thumbLrgCurr = explode('x',\App\Http\Controllers\FieldController::getFieldOption($field, "ThumbLarge"));
        ?>
        {!! Form::label('value_x','Large Thumbnail Size (X): ') !!}
        <input type="number" name="value_x" class="form-control" step="1" value="{{$thumbLrgCurr[0]}}" min="50" max="700">
        {!! Form::label('value_y','Large Thumbnail Size (Y): ') !!}
        <input type="number" name="value_y" class="form-control" step="1" value="{{$thumbLrgCurr[1]}}" min="50" max="700">
    </div>
    <div class="form-group">
        {!! Form::submit("Update Large Thumbnail Size",['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','MaxFiles') !!}
    <div class="form-group">
        {!! Form::label('value','Max File Amount: ') !!}
        <input type="number" name="value" class="form-control" step="1"
               value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "MaxFiles") }}" min="0">
    </div>
    <div class="form-group">
        {!! Form::submit("Update Max File Amount",['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','FileTypes') !!}
    <div class="form-group">
        {!! Form::label('value','Allowed File (MIME) Types: ') !!}
        {!! Form::select('value'.'[]',['image/jpeg' => 'Jpeg','image/gif' => 'Gif','image/png' => 'Png','image/bmp' => 'Bmp'],
            explode('[!]',\App\Http\Controllers\FieldController::getFieldOption($field, "FileTypes")),
            ['class' => 'form-control filetypes', 'Multiple', 'id' => 'list'.$field->flid]) !!}
    </div>
    <div class="form-group">
        {!! Form::submit("Update File Types",['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    @include('errors.list')
@stop

@section('footer')
    <script>
        $('.filetypes').select2();
    </script>
@stop