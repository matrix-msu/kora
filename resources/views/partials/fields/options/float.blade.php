@extends('fields.show')

@section('fieldOptions')
    @include('partials.fields.options.defaults.float')

    @include('partials.fields.options.config.float')
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Number');
@stop
