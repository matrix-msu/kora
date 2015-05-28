@extends('fields.show')

@section('fieldOptions')
    <div><b>Required</b>: {{ $field->required }}</div>
    <div><b>Default</b>: {{ $field->default }}</div>
    <div><b>Regex Pattern</b>: {{ \App\Http\Controllers\FieldController::getFieldOption($field,'Regex') }}</div>
    <div><b>Multi-Line</b>: {{ \App\Http\Controllers\FieldController::getFieldOption($field,'MultiLine') }}</div>
@stop