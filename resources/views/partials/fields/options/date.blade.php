@extends('fields.show')

@section('fieldOptions')
    @include('partials.fields.options.defaults.date')

    @include('partials.fields.options.config.date')
@stop

@section('fieldOptionsJS')
    Kora.Inputs.Number();
    Kora.Fields.Options('Date');
@stop
