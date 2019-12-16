@extends('app', ['page_title' => 'Record '.$record->kid, 'page_class' => 'record-show'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->project_id])
    @include('partials.menu.form', ['pid' => $form->project_id, 'fid' => $form->id])
    @include('partials.menu.record', ['pid' => $record->project_id, 'fid' => $record->form_id, 'rid' => $record->id])
@stop


@section('aside-content')
  @include('partials.sideMenu.form', ['pid' => $form->project_id, 'fid' => $form->id])
  @include('partials.sideMenu.record', ['pid' => $record->project_id, 'fid' => $record->form_id, 'rid' => $record->id, 'openDrawer' => true])
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
                <i class="icon icon-record mr-sm"></i>
                @if(\Auth::user()->canDestroyRecords($form) || \Auth::user()->isOwner($record))
                    <a href="#" class="head-button delete-record delete-record-js tooltip" tooltip="Delete Record">
                        <i class="icon icon-trash right"></i>
                    </a>
                @endif
                <span>Record: {{$record->kid}}</span>
            </h1>
            <p class="description">
                @if(\Auth::user()->canModifyRecords($form) || \Auth::user()->isOwner($record))
                    <a class="underline-middle-hover" href="{{ action('RecordController@edit',
                        ['pid' => $form->project_id, 'fid' => $form->id, 'rid' => $record->id]) }}">
                        <i class="icon icon-edit-little mr-xxs"></i>
                        <span>Edit Record</span>
                    </a>
                @endif
                @if(\Auth::user()->CanIngestRecords($form) || \Auth::user()->isOwner($record))
                    <a class="underline-middle-hover" href="{{action('RecordController@cloneRecord', [
                        'pid' => $form->project_id, 'fid' => $form->id, 'rid' => $record->id])}}">
                        <i class="icon icon-duplicate-little mr-xxs"></i>
                        <span>Duplicate Record</span>
                    </a>
                @endif
                @if(\Auth::user()->admin || \Auth::user()->isFormAdmin($form) || \Auth::user()->isOwner($record))
                    <a class="underline-middle-hover" href="{{action('RevisionController@show',
                        ['pid' => $form->project_id, 'fid' => $form->id, 'rid' => $record->id])}}">
                        <i class="icon icon-clock-little mr-xxs"></i>
                        <span>View Revisions ({{$numRevisions}})</span>
                    </a>
                @endif
                @if(\Auth::user()->admin || \Auth::user()->isFormAdmin($form))
                    @if($alreadyPreset)
                        <a class="already-preset already-preset-js" href="#">Designated as Preset</a>
                    @else
                        <a class="underline-middle-hover designate-preset-js" href="#">Designate as Preset</a>
                    @endif
                @endif
            </p>
        </div>
    </section>
@stop

@section('body')
    @include("partials.projects.notification")
    @include("partials.records.modals.deleteRecordModal")

    <section class="view-record center">
        @foreach($form->layout['pages'] as $page)
            @include('partials.records.page-card')
        @endforeach

        <div class="meta-title mt-xxxl">Record Owner</div>
        <section class="meta-data">
            @if(!is_null($owner))
                {{$owner->preferences['first_name']}} {{$owner->preferences['last_name']}}
            @else
                No owner available
            @endif
        </section>
        <div class="meta-title mt-m">Created</div>
        <div id="created-at" class="hidden">{{$record->created_at}}</div>
        <section class="meta-data time"></section>
        <div class="meta-title mt-m">Last Updated</div>
        <div id="updated-at" class="hidden">{{$record->updated_at}}</div>
        <section class="meta-data time"></section>

        @if($record->getAssociatedRecordsCount()>0)
            <div class="meta-title mt-m">Associated Records ({{ $record->getAssociatedRecordsCount() }})</div>
            <section class="meta-data reverse-assoc-data-section-js">Loading...</section>
        @endif
    </section>
@stop

@section('footer')

@stop

@section('javascripts')
    @include('partials.records.javascripts')

    <script src="{{ url('assets/javascripts/vendor/leaflet/leaflet.js') }}"></script>

    <script type="text/javascript">
        makeRecordPresetURL = '{{action('RecordPresetController@presetRecord')}}';
        revAssocDataURL = '{{action('RecordController@getAssociatedRecordData', ['pid' => $form->project_id, 'fid' => $form->id, 'rid' => $record->id])}}';
        ridForPreset = '{{$record->kid}}';
        revAssocCount = {{$record->getAssociatedRecordsCount()}}
        csrfToken = '{{csrf_token()}}';

        Kora.Records.Show();
    </script>
@stop
