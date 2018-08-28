@extends('app-plain', ['page_title' => $filename, 'page_class' => 'field-single-image'])

@section('body')
    <img class="field-image" src="{{ $src }}" alt="{{ $filename }}">
@stop