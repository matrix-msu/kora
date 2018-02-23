@extends('app', ['page_title' => 'Record '.$record->kid, 'page_class' => 'record-show'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
    @include('partials.menu.static', ['name' => $record->kid])
@stop

@section('stylesheets')

@stop

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-record mr-sm"></i>
                <span>Record: {{$record->kid}}</span>
            </h1>
            {{--TODO--}}
            <p class="description">
                <a class="underline-middle-hover" href="{{ action('RecordController@edit',
                    ['pid' => $form->pid, 'fid' => $form->fid, 'rid' => $record->rid]) }}">
                    <i class="icon icon-edit-little mr-xxs"></i>
                    <span>Edit Record</span>
                </a>
                <a class="underline-middle-hover" href="{{action('RecordController@cloneRecord', [
                    'pid' => $form->pid, 'fid' => $form->fid, 'rid' => $record->rid])}}">
                    <i class="icon icon-duplicate-little mr-xxs"></i>
                    <span>Duplicate Record</span>
                </a>
                <a class="underline-middle-hover" href="{{action('RevisionController@show',
                    ['pid' => $form->pid, 'fid' => $form->fid, 'rid' => $record->rid])}}">
                    <i class="icon icon-clock-little mr-xxs"></i>
                    <span>View Revisions ({{$numRevisions}})</span>
                </a>
                @if($alreadyPreset)
                    <a class="underline-middle-hover already-preset-js" href="#">Designated as Preset</a>
                @else
                    <a class="underline-middle-hover designate-preset-js" href="#">Designate as Preset</a>
                @endif
            </p>
        </div>
    </section>
@stop

@section('body')
    @include("partials.records.modals.designateRecordPresetModal")
    @include("partials.records.modals.alreadyRecordPresetModal")

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

    <script type="text/javascript">
        makeRecordPresetURL = '{{action('RecordPresetController@presetRecord')}}';
        ridForPreset = {{$record->rid}};
        csrfToken = '{{csrf_token()}}';

        Kora.Records.Show();
    </script>

        {{--function deleteRecord() {--}}
            {{--var response = confirm("{{trans('records_show.areyousure')}} {{$record->kid}}?");--}}
            {{--if (response) {--}}
                {{--$.ajax({--}}
                    {{--//We manually create the link in a cheap way because the JS isn't aware of the fid until runtime--}}
                    {{--//We pass in a blank project to the action array and then manually add the id--}}
                    {{--url: '{{ action('RecordController@destroy', ['pid' => $form->pid, 'fid' => $form->fid, 'rid' => $record->rid]) }}',--}}
                    {{--type: 'DELETE',--}}
                    {{--data: {--}}
                        {{--"_token": "{{ csrf_token() }}"--}}
                    {{--},--}}
                    {{--success: function (result) {--}}
                        {{--location.href = '{{ action('RecordController@index', ['pid' => $form->pid, 'fid' => $form->fid]) }}';--}}
                    {{--}--}}
                {{--});--}}
            {{--}--}}
        {{--}--}}
@stop