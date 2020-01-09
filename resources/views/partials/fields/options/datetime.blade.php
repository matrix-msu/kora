@extends('fields.show')

@section('fieldOptions')
    @include('partials.fields.options.defaults.datetime')

    @include('partials.fields.options.config.datetime')
@stop

@section('fieldOptionsJS')
    Kora.Inputs.Number();
    Kora.Fields.Options('Date');
@stop
