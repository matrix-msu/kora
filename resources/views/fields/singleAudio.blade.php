@extends('app-plain', ['page_title' => $filename, 'page_class' => 'field-single-audio'])

@section('body')
    <audio class="field-audio" controls src="{{ $src }}" alt="{{ $filename }}">
@stop