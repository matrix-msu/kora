@extends('app', ['page_title' => 'Record '.$record->kid, 'page_class' => 'record-show'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
    @include('partials.menu.static', ['name' => $record->kid])
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
        <a class="rotate" href="{{ URL::previous() }}"><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-record mr-sm"></i>
                <span>Record: {{$record->kid}}</span>
                @if(\Auth::user()->canDestroyRecords($form) || \Auth::user()->isOwner($record))
                    <a href="#" class="head-button delete-record delete-record-js">
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
                        <a class="underline-middle-hover already-preset-js" href="#">Designated as Preset</a>
                    @else
                        <a class="underline-middle-hover designate-preset-js" href="#">Designate as Preset</a>
                    @endif
                @endif
            </p>
        </div>
    </section>
@stop

@section('body')
    @include("partials.records.modals.designateRecordPresetModal")
    @include("partials.records.modals.alreadyRecordPresetModal")
    @include("partials.records.modals.deleteRecordModal")

    <section class="view-record center">
        @foreach(\App\Http\Controllers\PageController::getFormLayout($record->fid) as $page)
            @include('partials.records.page-card')
        @endforeach
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