@extends('app', ['page_title' => 'Search Kora', 'page_class' => 'record-index'])

@section('leftNavLinks')

@stop

@section('aside-content')
  @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => false, 'openProjectDrawer' => true])
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
                <i class="icon icon-search-big mr-sm"></i>
                <span>Search Kora</span>
            </h1>
            <p class="description">To search Kora, Enter keywords to in the first field below. You can also search by
                specific projects, and filter by “Or”, “And”, or “Exact” keyword results. Select “Search” to input your
                search query. You can also scroll beneath this section to begin exploring all Kora results.</p>
        </div>
    </section>
@stop

@section('body')
    <section class="view-records center">
        <section class="search-records">
            <form method="GET" action="{{action('ProjectSearchController@globalSearch')}}" class="keyword-search-js">
                <div class="form-group search-input mt-xl">
                    {!! Form::label('keywords','Search Via Keyword(s) or KID') !!}
                    {!! Form::text('keywords', app('request')->input('keywords'), ['class' => 'text-input keywords-get-js', 'placeholder' => 'Type space separated keywords']) !!}
                </div>
                <div class="form-group search-input mt-xl">
                    {!! Form::label('method','or / and / exact') !!}
                    {!! Form::select('method',[0 => 'or',1 => 'and',2 => 'exact'], app('request')->input('method'), ['class' => 'single-select method-get-js']) !!}
                </div>

                <div class="form-group search-spacer mt-xl">
                    {!! Form::label('projects','Search and Select Projects(s) to Filter Results') !!}
                    {!! Form::select('projects[]',$projects, ( !is_null(app('request')->input('projects')) ? app('request')->input('projects') : "ALL" ), ['multiple',
                        'class' => 'multi-select projects-get-js', 'data-placeholder' => 'Select Projects(s) to search']) !!}
                </div>

                <div class="form-group mt-xxxl search-button-container">
                    <a href="#" class="btn mb-sm submit-search-js" data-unsp-sanitized="clean">Search</a>
                </div>
            </form>

            <div class="form-group mt-xxxl scroll-to-here-js">
                <div class="spacer"></div>
            </div>
        </section>

        <section class="display-records">

            @if(sizeof($records)>0)
              
            <div class="form-group records-title mt-xxxl">
                Showing {{sizeof($records)}} of {{$total}} Records
            </div>
            
                @include('partials.records.pagination')

                <section class="filters">
                    <div class="pagination-options pagination-options-js">
                        <select class="page-count results-option-dropdown-js" id="page-count-dropdown">
                            <option value="10">10 per page</option>
                            <option value="20" {{app('request')->input('page-count') === '20' ? 'selected' : ''}}>20 per page</option>
                            <option value="30" {{app('request')->input('page-count') === '30' ? 'selected' : ''}}>30 per page</option>
                        </select>
                        <select class="order results-option-dropdown-js" id="order-dropdown">
                            <option value="lmd">Last Modified Descending</option>
                            <option value="lma" {{app('request')->input('order') === 'lma' ? 'selected' : ''}}>Last Modified Ascending</option>
                            <option value="idd" {{app('request')->input('order') === 'idd' ? 'selected' : ''}}>ID Descending</option>
                            <option value="ida" {{app('request')->input('order') === 'ida' ? 'selected' : ''}}>ID Ascending</option>
                        </select>
                    </div>
                    <div class="show-options show-options-js">
                        <span><a href="#" class="expand-fields-js tooltip" title="Expand all Records" tooltip="Expand all Records"><i class="icon icon-expand icon-expand-js"></i></a></span>
                        <span><a href="#" class="collapse-fields-js tooltip" title="Collapse all Records" tooltip="Collapse all Records"><i class="icon icon-condense icon-condense-js"></i></a></span>
                    </div>
                </section>

                @foreach($records as $index => $record)
                    @include('partials.records.card')
                @endforeach

                @include('partials.records.pagination')
            @else
              @include('partials.records.no-records')
            @endif
        </section>
    </section>
@stop

@section('footer')

@stop

@section('javascripts')
    @include('partials.records.javascripts')

    <script src="{{ config('app.url') }}assets/javascripts/vendor/leaflet/leaflet.js"></script>

    <script type="text/javascript">
        var deleteRecordURL = "";

        Kora.Records.Index();
    </script>
@stop
