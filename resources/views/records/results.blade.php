@extends('app', ['page_title' => 'Form Records', 'page_class' => 'record-results'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->project_id])
    @include('partials.menu.form', ['pid' => $form->project_id, 'fid' => $form->id])
    @include('partials.menu.static', ['name' => 'Form Records'])
@stop

@section('aside-content')
  @include('partials.sideMenu.form', ['pid' => $form->project_id, 'fid' => $form->id, 'openDrawer' => true])
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
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-form-record-search mr-sm"></i>
                <span>Form Records & Search</span>
            </h1>
            <p class="description">Enter keywords to search below. You can also filter by “Or”, “And”, or “Exact”
                keyword results. The Form Records & Search page also allows you to individually select a number of
                records in order to batch delete, assign field values, or to export. To create a selection of records,
                select the radio buttons next to each record name use wish to select.</p>
        </div>
    </section>
@stop

@section('body')
    @include("partials.records.modals.deleteRecordModal", ['record' => null])
    <section class="view-records center">
        <section class="search-records">
            <form method="GET" action="{{action('FormSearchController@keywordSearch',['pid' => $form->project_id, 'fid' => $form->id])}}" class="keyword-search-js">
                <div class="form-group search-input mt-xl">
                    {!! Form::label('keywords','Search Via Keyword(s) or KID') !!}
                    {!! Form::text('keywords', app('request')->input('keywords'), ['class' => 'text-input keywords-get-js', 'placeholder' => 'Type space separated keywords']) !!}
                </div>
                <div class="form-group search-input mt-xl">
                    {!! Form::label('method','or / and / exact') !!}
                    {!! Form::select('method',[0 => 'or',1 => 'and',2 => 'exact'], app('request')->input('method'), ['class' => 'single-select method-get-js']) !!}
                </div>

                <div class="form-group mt-xxxl search-button-container">
                    {{--TODO::CASTLE--}}
                    {{--@if($form->fields()->where('advsearch','=',1)->count() > 0)--}}
                    {{--<a href="{{action('AdvancedSearchController@index', ['pid' => $form->project_id, 'fid' => $form->id])}}" class="btn half-sub-btn" data-unsp-sanitized="clean">Go to Advanced Search</a>--}}
                    {{--@else--}}
                    {{--<a href="#" class="btn half-sub-btn disabled" data-unsp-sanitized="clean">No Advanced Search Available</a>--}}
                    {{--@endif--}}
                    <a href="#" class="btn half-sub-btn disabled" data-unsp-sanitized="clean">No Advanced Search Available</a>
                    <a href="#" class="btn half-btn submit-search-js right" data-unsp-sanitized="clean">Search</a>
                </div>
            </form>

            <div class="form-group mt-xxxl scroll-to-here-js">
                <div class="spacer"></div>
            </div>
        </section>

        <section class="display-records">
            <div class="form-group records-title mt-xxxl">
                @if(count($records) > 0)
                    Showing {{sizeof($records)}} of {{$total}} Records
                @else
                    No Results
                @endif
            </div>

            @if(count($records) > 0)
              @include('partials.records.pagination')
            @else
              @include('partials.records.no-records')
            @endif

            @if(count($records) > 0)
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
	                    <span><a href="#" class="expand-fields-js tooltip" title="Expand all fields" tooltip="Expand all Fields"><i class="icon icon-expand icon-expand-js"></i></a></span>
	                    <span><a href="#" class="collapse-fields-js tooltip" title="Collapse all fields" tooltip="Collapse all Fields"><i class="icon icon-condense icon-condense-js"></i></a></span>
	                </div>
	            </section>

                @foreach($records as $index => $record)
                    @include('partials.records.card')
                @endforeach

                @include('partials.records.pagination')

                <div class="form-group search-button-container mt-xxl">
                    <a href="#" class="btn half-sub-btn try-another-search try-another-js">Try Another Search</a>
                </div>
            @endif
        </section>
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
        Kora.Records.Toolbar();
    </script>
@stop
