@extends('fields.show')

@section('fieldOptions')

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateRequired', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('required',trans('fields_options_documents.req').': ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_documents.updatereq'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','FieldSize') !!}
    <div class="form-group">
        {!! Form::label('value',trans('fields_options_documents.maxsize').' (kb): ') !!}
        <input type="number" name="value" class="form-control" step="1"
               value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "FieldSize") }}" min="0">
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_documents.updatesize'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','MaxFiles') !!}
    <div class="form-group">
        {!! Form::label('value',trans('fields_options_documents.maxamount').': ') !!}
        <input type="number" name="value" class="form-control" step="1"
               value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "MaxFiles") }}" min="0">
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_documents.updateamount'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','FileTypes') !!}
    <div class="form-group">
        {!! Form::label('value',trans('fields_options_documents.types').' (MIME): ') !!}
        <?php
            $values = array();
            foreach(explode('[!]',\App\Http\Controllers\FieldController::getFieldOption($field, "FileTypes")) as $opt){
                $values[$opt] = $opt;
            }
        ?>
        {!! Form::select('value'.'[]',$values,
            explode('[!]',\App\Http\Controllers\FieldController::getFieldOption($field, "FileTypes")),
            ['class' => 'form-control filetypes', 'Multiple', 'id' => 'list'.$field->flid]) !!}
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_documents.updatetypes'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    @include('errors.list')
@stop

@section('footer')
    <script>
        $('.filetypes').select2({
            tags: true
        });
    </script>
@stop