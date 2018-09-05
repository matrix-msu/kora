@extends('app-plain', ['page_title' => $filename, 'page_class' => 'field-single-video'])

@section('body')
    <video class="field-video" src="{{ $src }}" alt="{{ $filename }}" controls>
      <source src="" type="">
      Your browser does not support the video tag.
    </video>
@stop
