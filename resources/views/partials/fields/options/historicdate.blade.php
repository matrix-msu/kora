@extends('fields.show')

@section('fieldOptions')
    @include('partials.fields.options.defaults.historicdate')

    @include('partials.fields.options.config.historicdate')
@stop

@section('fieldOptionsJS')
    Kora.Inputs.Number();
    Kora.Fields.Options('Date');
@stop
