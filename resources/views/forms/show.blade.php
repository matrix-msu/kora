@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
@stop

@section('content')
    <span><h1>{{ $form->name }}</h1></span>

    <div><b>{{trans('forms_show.slug')}}:</b> {{ $form->slug }}</div>
    <div><b>{{trans('forms_show.desc')}}:</b> {{ $form->description }}</div>

    <div>
        <a href="{{ action('RecordController@index',['pid' => $form->pid, 'fid' => $form->fid]) }}">[{{trans('forms_show.records')}}]</a>
        @if(\Auth::user()->canIngestRecords($form))
            <a href="{{ action('RecordController@create',['pid' => $form->pid, 'fid' => $form->fid]) }}">[{{trans('forms_show.newrec')}}]</a>
            <a href="{{ action('RecordController@importRecordsView',['pid' => $form->pid, 'fid' => $form->fid]) }}">[{{trans('forms_show.import')}}]</a>
        @endif
        @if(\Auth::user()->canModifyRecords($form))
            <a href="{{ action('RecordController@showMassAssignmentView',['pid' => $form->pid, 'fid' => $form->fid]) }}">[{{trans('forms_show.massassign')}}]</a>
        @endif
    </div>

    <hr/>

    @include('search.bar', ['pid' => $form->pid, 'fid' => $form->fid])


    @if (\Auth::user()->admin || \Auth::user()->isFormAdmin($form))
        <hr/>

        <h4>{{trans('forms_show.formpanel')}}</h4>
        <form action="{{action('FormGroupController@index', ['pid'=>$form->pid, 'fid'=>$form->fid])}}" style="display: inline">
            <button type="submit" class="btn btn-default">{{trans('forms_show.mGroups')}}</button>
        </form>
        <form action="{{action('AssociationController@index', ['fid'=>$form->fid, 'pid'=>$form->pid])}}" style="display: inline">
            <button type="submit" class="btn btn-default">{{trans('forms_show.mAssoc')}}</button>
        </form>
        <form action="{{action('RevisionController@index', ['pid'=>$form->pid, 'fid'=>$form->fid])}}" style="display: inline">
            <button type="submit" class="btn btn-default">{{trans('forms_show.mRecRiv')}}</button>
        </form>
        <form action="{{action('RecordPresetController@index', ['pid'=>$form->pid, 'fid'=>$form->fid])}}" style="display: inline">
            <button type="submit" class="btn btn-default">{{trans('forms_show.mRecPre')}}</button>
        </form>
        <div>
            <span>{{trans('forms_show.makepreset')}}: </span><input type="checkbox" onchange="presetForm()" id="preset" @if($form->preset) checked @endif>
        </div>
        <form method="post" action="{{action('RecordController@createTest', ['pid'=>$form->pid, 'fid'=>$form->fid])}}" style="display: inline">
            <input type="hidden" value="{{ csrf_token() }}" name="_token">
            <div><b>Create Test Records ({{trans('records_create.max')}} 1000):</b></div>
            <input type="number" name="test_records_num" value="1" step="1" max="1000" min="1">
            <button type="submit" class="btn btn-default">Create</button>
        </form>
    @endif
    <hr/>
    <h2>{{trans('forms_show.fields')}}</h2>

    @if(\Auth::user()->admin || \Auth::user()->isFormAdmin($form))
        <div>
            <a href="{{ action('ExportController@exportForm',['pid' => $form->pid, 'fid' => $form->fid]) }}">[{{trans('forms_show.export')}}]</a>
        </div> <br>
    @endif

    <div id="form_pages">
    @foreach($pageLayout as $page)
        <h2>{{$page["title"]}}</h2>

        @if(\Auth::user()->canCreateFields($form))
        <button type="button" class="move_pageUp" pageid="{{$page["id"]}}">UP</button>
        <button type="button" class="move_pageDown" pageid="{{$page["id"]}}">DOWN</button>
        <button type="button" class="delete_page" pageid="{{$page["id"]}}">DELETE</button>
        @endif

        <hr>

        @foreach($page["fields"] as $field)
            @include('forms.layout.printfield', ['field' => $field])
        @endforeach

        <hr>

        @if(\Auth::user()->canCreateFields($form))
        <form method="POST" action="{{action('FieldController@create', ['pid' => $form->pid, 'fid' => $form->fid]) }}">
            <input type="hidden" value="{{$page["id"]}}" name="rootPage"/>
            <input type="hidden" value="{{ csrf_token() }}" name="_token"/>
            <input type="submit" value="{{trans('forms_show.createfield')}}" class="btn btn-primary">
        </form>
        <button type="button" class="add_page" pageid="{{$page["id"]}}">ADD PAGE</button>
        {!! Form::text("pagetext_".$page["id"], null, ['id' => "pagetext_".$page["id"]]) !!}
        @endif

        <br><br>
    @endforeach
    </div>

@stop

@section('footer')
    <script>
        $( ".panel-heading" ).on( "click", function() {
            if ($(this).siblings('.collapseTest').css('display') == 'none' ){
                $(this).siblings('.collapseTest').slideDown();
            }else {
                $(this).siblings('.collapseTest').slideUp();
            }
        });

        $( "#form_pages" ).on( "click", ".move_pageUp", function() {
            var page_id = $(this).attr('pageid');

            $.ajax({
                //We manually create the link in a cheap way because our JS isn't aware of the fid until runtime
                //We pass in a blank project to the action array and then manually add the id
                url: '{{ action('PageController@modifyFormPage', ['pid' => $form->pid, 'fid' => $form->fid]) }}',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "method": "{{\App\Http\Controllers\PageController::_UP}}",
                    "pageID": page_id
                },
                success: function (result) {
                    location.reload();
                }
            });
        });

        $( "#form_pages" ).on( "click", ".move_pageDown", function() {
            var page_id = $(this).attr('pageid');

            $.ajax({
                //We manually create the link in a cheap way because our JS isn't aware of the fid until runtime
                //We pass in a blank project to the action array and then manually add the id
                url: '{{ action('PageController@modifyFormPage', ['pid' => $form->pid, 'fid' => $form->fid]) }}',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "method": "{{\App\Http\Controllers\PageController::_DOWN}}",
                    "pageID": page_id
                },
                success: function (result) {
                    location.reload();
                }
            });
        });

        $( "#form_pages" ).on( "click", ".delete_page", function() {
            var page_id = $(this).attr('pageid');

            $.ajax({
                //We manually create the link in a cheap way because our JS isn't aware of the fid until runtime
                //We pass in a blank project to the action array and then manually add the id
                url: '{{ action('PageController@modifyFormPage', ['pid' => $form->pid, 'fid' => $form->fid]) }}',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "method": "{{\App\Http\Controllers\PageController::_DELETE}}",
                    "pageID": page_id
                },
                success: function (result) {
                    location.reload();
                }
            });
        });

        $( "#form_pages" ).on( "click", ".add_page", function() {
            var page_id = $(this).attr('pageid');
            var title = $("#pagetext_"+page_id).val();

            $.ajax({
                //We manually create the link in a cheap way because our JS isn't aware of the fid until runtime
                //We pass in a blank project to the action array and then manually add the id
                url: '{{ action('PageController@modifyFormPage', ['pid' => $form->pid, 'fid' => $form->fid]) }}',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "method": "{{\App\Http\Controllers\PageController::_ADD}}",
                    "aboveID": page_id,
                    "newPageName": title
                },
                success: function (result) {
                    location.reload();
                }
            });
        });

        function deleteField(fieldName, flid) {
            var encode = $('<div/>').html("{{ trans('forms_show.areyousure') }} ").text();
            var response = confirm(encode + fieldName + "?");
            if (response) {
                $.ajax({
                    //We manually create the link in a cheap way because our JS isn't aware of the fid until runtime
                    //We pass in a blank project to the action array and then manually add the id
                    url: '{{ action('FieldController@destroy', ['pid' => $form->pid, 'fid' => $form->fid, 'flid' => '']) }}/'+flid,
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function (result) {
                        location.reload();
                    }
                });
            }
        }

        function presetForm(){
            var preset;
            if($('#preset').is(':checked'))
                preset = 1;
            else
                preset = 0;
            $.ajax({
                url: '{{action('FormController@preset', ['pid' => $form->pid, 'fid' => $form->fid])}}',
                type: 'POST',
                data: {
                    "_token": '{{csrf_token()}}',
                    "preset": preset
                }
            });
        }
    </script>
@stop