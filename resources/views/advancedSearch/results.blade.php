@extends('app', ['page_title' => 'Advanced Search', 'page_class' => 'advanced-index'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->project_id])
    @include('partials.menu.form', ['pid' => $form->project_id, 'fid' => $form->id])
    @include('partials.menu.static', ['name' => 'Advanced Search'])
@stop

@section('aside-content')
  @include('partials.sideMenu.form', ['pid' => $form->project_id, 'fid' => $form->id, 'openDrawer' => true])
@stop

@section('stylesheets')
    <link rel="stylesheet" type="text/css" href="{{ url('assets/css/vendor/leaflet/leaflet.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ url('assets/css/vendor/slick/slick.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ url('assets/css/vendor/slick/slick-theme.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ url('assets/css/vendor/jplayer/pink.flag/css/jplayer.pink.flag.min.css') }}"/>
@stop

@section('header')
    <section class="head">
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
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
    @include("partials.records.modals.deleteRecordModal", ['record' => null])
    @include("partials.records.modals.deleteMultipleRecordsModal", ['record' => null])
    @include("partials.records.modals.exportMultipleRecordsModal", ['record' => null])
    <section class="view-records center">
        <section class="search-records">
            <section class="advanced-search-drawer">
                @include('partials.records.adv-form')
            </section>

            <div class="form-group mt-xxxl scroll-to-here-js">
                <div class="spacer"></div>
            </div>
        </section>

        @if(sizeof($records) > 0)
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

              <div class="form-group search-button-container mt-xxl">
                  <a href="#" class="btn half-sub-btn try-another-search try-another-js">Try Another Search</a>
              </div>
          </section>
        @else
            @include('partials.records.no-records')
        @endif
    </section>
    @if (count($records) > 0)
        @include('partials.records.toolbar')
    @endif
@stop

@section('footer')

@stop

@section('javascripts')
    @include('partials.records.javascripts')

    <script src="{{ url('assets/javascripts/vendor/leaflet/leaflet.js') }}"></script>

    <script type="text/javascript">
        var deleteRecordURL = "{{action('RecordController@destroy', ['pid' => $form->project_id, 'fid' => $form->id, 'rid' => ''])}}";

        Kora.Records.Index();
        Kora.Records.Advanced();
        Kora.Records.Toolbar();
    </script>
@stop
