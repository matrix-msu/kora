@extends('fields.show')

@section('fieldOptions')
    @include('partials.fields.options.defaults.richtext')
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Rich Text');
@stop
