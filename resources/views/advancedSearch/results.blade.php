@extends('app', ['page_title' => 'Advanced Search', 'page_class' => 'record-index'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
    @include('partials.menu.static', ['name' => 'Advanced Search'])
@stop

@section('aside-content')
  @include('partials.sideMenu.form', ['pid' => $form->pid, 'fid' => $form->fid, 'openDrawer' => true])
@stop

@section('stylesheets')
    <link rel="stylesheet" type="text/css" href="{{ config('app.url') }}assets/css/vendor/fullcalendar/fullcalendar.css"/>
    <link rel="stylesheet" type="text/css" href="{{ config('app.url') }}assets/css/vendor/leaflet/leaflet.css"/>
    <link rel="stylesheet" type="text/css" href="{{ config('app.url') }}assets/css/vendor/slick/slick.css"/>
    <link rel="stylesheet" type="text/css" href="{{ config('app.url') }}assets/css/vendor/slick/slick-theme.css"/>
    <link rel="stylesheet" type="text/css" href="{{ config('app.url') }}assets/css/vendor/jplayer/pink.flag/css/jplayer.pink.flag.min.css"/>
@stop

@section('header')
    <section class="head">
        <a class="back" href="{{ URL::previous() }}"><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-form-record-search mr-sm"></i>
                <span>Advanced Search Results</span>
            </h1>
            <p class="description">View advanced search results or submit a new advanced search. </p>
        </div>
    </section>
@stop

@section('body')
    @include("partials.records.modals.deleteRecordModal", ['record' => null])
    <section class="view-records center">
        <section class="search-records">
            <section class="advanced-search-drawer">
                @include('partials.records.adv-form')
            </section>

            <div class="form-group mt-xxxl scroll-to-here-js">
                <div class="spacer"></div>
            </div>
        </section>

        <section class="display-records">
            <div class="form-group records-title mt-xxxl">
                Showing {{sizeof($records)}} of {{$total}} Records
            </div>

            @include('partials.records.pagination')

            <section class="filters center">
                <div class="pagination-options pagination-options-js">
                    <select class="page-count option-dropdown-js" id="page-count-dropdown">
                        <option value="10">10 per page</option>
                        <option value="20" {{app('request')->input('page-count') === '20' ? 'selected' : ''}}>20 per page</option>
                        <option value="30" {{app('request')->input('page-count') === '30' ? 'selected' : ''}}>30 per page</option>
                    </select>
                    <select class="order option-dropdown-js" id="order-dropdown">
                        <option value="lmd">Last Modified Descending</option>
                        <option value="lma" {{app('request')->input('order') === 'lma' ? 'selected' : ''}}>Last Modified Ascending</option>
                        <option value="idd" {{app('request')->input('order') === 'idd' ? 'selected' : ''}}>ID Descending</option>
                        <option value="ida" {{app('request')->input('order') === 'ida' ? 'selected' : ''}}>ID Ascending</option>
                    </select>
                </div>
                <div class="show-options show-options-js">
                    <a href="#" class="expand-fields-js" title="Expand all fields"><i class="icon icon-expand icon-expand-js"></i></a>
                    <a href="#" class="collapse-fields-js" title="Collapse all fields"><i class="icon icon-condense icon-condense-js"></i></a>
                </div>
            </section>

            @foreach($records as $index => $record)
                @include('partials.records.card')
            @endforeach

            @include('partials.records.pagination')
        </section>
    </section>
@stop

@section('footer')

@stop

@section('javascripts')
    @include('partials.records.javascripts')

    <script src="{{ config('app.url') }}assets/javascripts/vendor/leaflet/leaflet.js"></script>

    <script type="text/javascript">
        var deleteRecordURL = "{{action('RecordController@destroy', ['pid' => $form->pid, 'fid' => $form->fid, 'rid' => ''])}}";

        Kora.Records.Index();
    </script>
@stop
