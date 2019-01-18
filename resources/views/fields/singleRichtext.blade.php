@extends('app-plain', ['page_title' => $field->name, 'page_class' => 'field-single-richtext'])

@section('body')
    <p>{!!$typedField->rawtext!!}</p>
@stop
