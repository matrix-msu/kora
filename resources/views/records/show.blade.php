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
                <i class="icon icon-record mr-sm"></i>
                @if(\Auth::user()->canDestroyRecords($form) || \Auth::user()->isOwner($record))
                    <a href="#" class="head-button delete-record delete-record-js tooltip" tooltip="Delete Record">
                        <i class="icon icon-trash right"></i>
                    </a>
                @endif
                <span>Record: {{$record->kid}}</span>
            </h1>
            {{--TODO--}}
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
                {{$owner->first_name}} {{$owner->last_name}}
            @else
                No owner available
            @endif
        </section>
        <div class="meta-title mt-m">Created</div>
        <section class="meta-data">
            {{$record->created_at}}
        </section>
        <div class="meta-title mt-m">Last Updated</div>
        <section class="meta-data">
            {{$record->updated_at}}
        </section>
        {{--TODO::CASTLE--}}
        {{--@if(sizeof($record->getAssociatedRecords())>0)--}}
            {{--<div class="meta-title mt-m">Associated Records</div>--}}
            {{--<section class="meta-data">--}}
                {{--@foreach($record->getAssociatedRecords() as $aRecord)--}}
                    {{--<div><a class="meta-link underline-middle-hover"--}}
                            {{--href='{{url('projects/'.$aRecord->pid.'/forms/'.$aRecord->fid.'/records/'.$aRecord->rid)}}'>{{$aRecord->kid}}--}}
                        {{--</a> | {{$aRecord->getReversePreview()}}</div>--}}
                {{--@endforeach--}}
            {{--</section>--}}
        {{--@endif--}}
    </section>
@stop

@section('footer')

@stop

@section('javascripts')
    @include('partials.records.javascripts')

    <script src="{{ url('assets/javascripts/vendor/leaflet/leaflet.js') }}"></script>

    <script type="text/javascript">
        makeRecordPresetURL = '{{action('RecordPresetController@presetRecord')}}';
        ridForPreset = {{$record->id}};
        csrfToken = '{{csrf_token()}}';

        Kora.Records.Show();
    </script>
@stop
