@extends('app', ['page_title' => 'Kora Publishing', 'page_class' => 'publish'])

@section('stylesheets')
    <link rel="stylesheet" href="{{ config('app.url') }}assets/css/vendor/grapejs/css/grapes.min.css">
@endsection

@section('body')
    <div id="gjs"></div>
@endsection

@section('javascripts')
    {!! Minify::javascript([
      '/assets/javascripts/vendor/jquery/jquery.js',
      '/assets/javascripts/vendor/jquery/jquery-ui.js',
      '/assets/javascripts/navigation/navigation.js',
      '/assets/javascripts/general/global.js'
    ])->withFullUrl() !!}

    @include('partials.publish.javascripts')
@endsection