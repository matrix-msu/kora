@extends('fields.show')

@section('fieldOptions')
    @include('partials.fields.options.defaults.integer')

    @include('partials.fields.options.config.integer')
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Number');
@stop
