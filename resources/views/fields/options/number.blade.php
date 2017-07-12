@extends('fields.show')

@section('fieldOptions')

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldAjaxController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('required',trans('fields_options_number.req').': ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('searchable',trans('fields_options_number.search').': ') !!}
        {!! Form::select('searchable',['false', 'true'], $field->searchable, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('extsearch',trans('fields_options_number.extsearch').': ') !!}
        {!! Form::select('extsearch',['false', 'true'], $field->extsearch, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('viewable',trans('fields_options_number.viewable').': ') !!}
        {!! Form::select('viewable',['false', 'true'], $field->viewable, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('viewresults',trans('fields_options_number.viewresults').': ') !!}
        {!! Form::select('viewresults',['false', 'true'], $field->viewresults, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('extview',trans('fields_options_number.extview').': ') !!}
        {!! Form::select('extview',['false', 'true'], $field->extview, ['class' => 'form-control']) !!}
    </div>

    <hr>

    <div class="form-group">
        {!! Form::label('default',trans('fields_options_number.def').': ') !!}
        <input
                type="number" name="default" class="form-control" value="{{ $field->default }}" id="default">
    </div>

    <div class="form-group">
        {!! Form::label('min',trans('fields_options_number.min').': ') !!}
        <input
                type="number" name="min" class="form-control" step="any" id="min"
                value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Min") }}">
    </div>

    <div class="form-group">
        {!! Form::label('max',trans('fields_options_number.max').': ') !!}
        <input
                type="number" name="max" class="form-control" step="any" id="max"
                value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Max") }}">
    </div>

    <div class="form-group">
        {!! Form::label('inc',trans('fields_options_number.inc').': ') !!}
        <input
                type="number" name="inc" class="form-control" step="any" id="inc"
                value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Increment") }}">
    </div>

    <div class="form-group">
        {!! Form::label('unit',trans('fields_options_number.unit').': ') !!}
        {!! Form::text('unit', \App\Http\Controllers\FieldController::getFieldOption($field,'Unit'), ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::submit(trans('field_options_generic.submit',['field'=>$field->name]),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    @include('errors.list')
@stop

@section('footer')
    <script>

    </script>
@stop