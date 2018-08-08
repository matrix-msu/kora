@extends('app', ['page_title' => 'Record '.$record->kid, 'page_class' => 'record-show'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
    @include('partials.menu.record', ['pid' => $record->pid, 'fid' => $record->fid, 'rid' => $record->rid])
    <!--@include('partials.menu.static', ['name' => $record->kid])-->
@stop


@section('aside-content')
  @include('partials.sideMenu.form', ['pid' => $form->pid, 'fid' => $form->fid])
  @include('partials.sideMenu.record', ['pid' => $record->pid, 'fid' => $record->fid, 'rid' => $record->rid, 'openDrawer' => true])
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
                <i class="icon icon-record mr-sm"></i>
                <span>Record: {{$record->kid}}</span>
                @if(\Auth::user()->canDestroyRecords($form) || \Auth::user()->isOwner($record))
                    <a href="#" class="head-button delete-record delete-record-js tooltip" tooltip="Delete Record">
                        <i class="icon icon-trash right"></i>
                    </a>
                @endif
            </h1>
            {{--TODO--}}
            <p class="description">
                @if(\Auth::user()->canModifyRecords($form) || \Auth::user()->isOwner($record))
                    <a class="underline-middle-hover" href="{{ action('RecordController@edit',
                        ['pid' => $form->pid, 'fid' => $form->fid, 'rid' => $record->rid]) }}">
                        <i class="icon icon-edit-little mr-xxs"></i>
                        <span>Edit Record</span>
                    </a>
                @endif
                @if(\Auth::user()->CanIngestRecords($form) || \Auth::user()->isOwner($record))
                    <a class="underline-middle-hover" href="{{action('RecordController@cloneRecord', [
                        'pid' => $form->pid, 'fid' => $form->fid, 'rid' => $record->rid])}}">
                        <i class="icon icon-duplicate-little mr-xxs"></i>
                        <span>Duplicate Record</span>
                    </a>
                @endif
                @if(\Auth::user()->admin || \Auth::user()->isFormAdmin($form) || \Auth::user()->isOwner($record))
                    <a class="underline-middle-hover" href="{{action('RevisionController@show',
                        ['pid' => $form->pid, 'fid' => $form->fid, 'rid' => $record->rid])}}">
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
    @include("partials.records.modals.deleteRecordModal")

    <section class="view-record center">
        @foreach(\App\Http\Controllers\PageController::getFormLayout($record->fid) as $page)
            @include('partials.records.page-card')
        @endforeach

        <div class="meta-title mt-xxxl">Record Owner</div>
        <section class="meta-data">
            {{$owner->first_name}} {{$owner->last_name}}
        </section>
        <div class="meta-title mt-m">Created</div>
        <section class="meta-data">
            {{$record->created_at}}
        </section>
        <div class="meta-title mt-m">Last Updated</div>
        <section class="meta-data">
            {{$record->updated_at}}
        </section>
        @if(sizeof(\App\Http\Controllers\AssociationController::getAssociatedRecords($record))>0)
            <div class="meta-title mt-m">Associated Records</div>
            <section class="meta-data">
                @foreach(\App\Http\Controllers\AssociationController::getAssociatedRecords($record) as $aRecord)
                    <div><a class="meta-link underline-middle-hover" href='{{env('BASE_URL')}}projects/{{$aRecord->pid}}/forms/{{$aRecord->fid}}/records/{{$aRecord->rid}}'>{{$aRecord->kid}}</a></div>
                @endforeach
            </section>
        @endif
    </section>
@stop

@section('footer')

@stop

@section('javascripts')
    @include('partials.records.javascripts')

    <script src="{{ config('app.url') }}assets/javascripts/vendor/leaflet/leaflet.js"></script>

    <script type="text/javascript">
        makeRecordPresetURL = '{{action('RecordPresetController@presetRecord')}}';
        ridForPreset = {{$record->rid}};
        csrfToken = '{{csrf_token()}}';

        Kora.Records.Show();
    </script>
@stop
