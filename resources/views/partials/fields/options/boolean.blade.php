@extends('fields.show')

@section('fieldOptions')
    @include('partials.fields.options.config.boolean')
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Boolean');
@stop
