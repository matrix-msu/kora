@extends('app', ['page_title' => 'Advanced Search', 'page_class' => 'advanced-index'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
    @include('partials.menu.static', ['name' => 'Advanced Search'])
@stop

@section('aside-content')
    @include('partials.sideMenu.form', ['pid' => $form->pid, 'fid' => $form->fid, 'openDrawer' => true])
@stop

@section('stylesheets')
    <link rel="stylesheet" type="text/css" href="{{ url('assets/css/vendor/fullcalendar/fullcalendar.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ url('assets/css/vendor/leaflet/leaflet.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ url('assets/css/vendor/slick/slick.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ url('assets/css/vendor/slick/slick-theme.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ url('assets/css/vendor/jplayer/pink.flag/css/jplayer.pink.flag.min.css') }}"/>
@stop

@section('header')
    <section class="head">
        <a class="back" href="{{ URL::previous() }}"><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-form-record-search mr-sm"></i>
                <span>Form Records Advanced Search</span>
            </h1>
            <p class="description">Use the advanced search options below, then select 'Submit Advanced Search'</p>
        </div>
    </section>
@stop

@section('body')
    <section class="view-records center">
        <section class="search-records">
            <section class="advanced-search-drawer">
                @include('partials.records.adv-form')
            </section>

            <div class="form-group mt-xxxl">
                <div class="spacer"></div>
            </div>
        </section>

        <section class="display-records">
            <div class="form-group results-here-text mt-xxxl">
                Search results will appear here after a search has been inputted.
            </div>
        </section>
    </section>
@stop

@section('footer')

@stop

@section('javascripts')
    @include('partials.records.javascripts')

    <script src="{{ url('assets/javascripts/vendor/leaflet/leaflet.js') }}"></script>

    <script type="text/javascript">
        Kora.Records.Index();
    </script>
@stop
