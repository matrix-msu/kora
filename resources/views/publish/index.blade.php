@extends('app', ['page_title' => 'Kora Publishing', 'page_class' => 'publish'])

@section('stylesheets')
    <link rel="stylesheet" href="{{ env('BASE_URL') }}grapejs/dist/css/grapes.min.css">
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

    <script src="{{ env('BASE_URL') }}grapejs/dist/grapes.min.js"></script>

    <script type="text/javascript">
        var editor = grapesjs.init({
            container : '#gjs',
            components: '<div class="txt-red">Hello world!</div>',
            style: '.txt-red{color: red}',
        });
    </script>
@endsection