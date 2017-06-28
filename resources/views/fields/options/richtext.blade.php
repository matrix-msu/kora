@extends('fields.show')

@section('fieldOptions')

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldAjaxController@updateOptions', $field->pid, $field->fid, $field->flid, $field->type]]) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('required',trans('fields_options_richtext.req').': ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('searchable',trans('fields_options_richtext.search').': ') !!}
        {!! Form::select('searchable',['false', 'true'], $field->searchable, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('extsearch',trans('fields_options_richtext.extsearch').': ') !!}
        {!! Form::select('extsearch',['false', 'true'], $field->extsearch, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('viewable',trans('fields_options_richtext.viewable').': ') !!}
        {!! Form::select('viewable',['false', 'true'], $field->viewable, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('viewresults',trans('fields_options_richtext.viewresults').': ') !!}
        {!! Form::select('viewresults',['false', 'true'], $field->viewresults, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('extview',trans('fields_options_richtext.extview').': ') !!}
        {!! Form::select('extview',['false', 'true'], $field->extview, ['class' => 'form-control']) !!}
    </div>

    <hr>

    <div class="form-group">
        {!! Form::label('default',trans('fields_options_richtext.def').': ') !!}
        {!! Form::text('default', $field->default, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::submit(trans('field_options_generic.submit',['field'=>$field->name]),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    @include('errors.list')
@stop