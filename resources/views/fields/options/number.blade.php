@extends('fields.show')

@section('fieldOptions')

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateRequired', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('required',trans('fields_options_number.req').': ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_number.updatereq'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateDefault', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('default',trans('fields_options_number.def').': ') !!}
        <input
                type="number" name="default" class="form-control" value="{{ $field->default }}"
                step="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Increment") }}"
                min="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Min") }}"
                max="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Max") }}">
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_number.updatedef'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','Min') !!}
    <div class="form-group">
        {!! Form::label('value',trans('fields_options_number.min').': ') !!}
        <input
                type="number" name="value" class="form-control" step="any"
                value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Min") }}"
                max="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Max") }}">
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_number.updatemin'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','Max') !!}
    <div class="form-group">
        {!! Form::label('value',trans('fields_options_number.max').': ') !!}
        <input
                type="number" name="value" class="form-control" step="any"
                value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Max") }}"
                min="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Min") }}">
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_number.updatemax'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','Increment') !!}
    <div class="form-group">
        {!! Form::label('value',trans('fields_options_number.inc').': ') !!}
        <input
                type="number" name="value" class="form-control" step="any"
                value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Increment") }}">
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_number.updateinc'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','Unit') !!}
    <div class="form-group">
        {!! Form::label('value',trans('fields_options_number.unit').': ') !!}
        {!! Form::text('value', \App\Http\Controllers\FieldController::getFieldOption($field,'Unit'), ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_number.updateunit'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    @include('errors.list')
@stop