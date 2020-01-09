@extends('fields.show')

@section('fieldOptions')
    @include('partials.fields.options.defaults.boolean')
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Boolean');
@stop
