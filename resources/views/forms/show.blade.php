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
        @endif
        @if(\Auth::user()->canModifyRecords($form))
            <a href="{{ action('RecordController@showMassAssignmentView',['pid' => $form->pid, 'fid' => $form->fid]) }}">[{{trans('forms_show.massassign')}}]</a>
        @endif
    </div>

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
    @endif
    <hr/>
    <h2>{{trans('forms_show.fields')}}</h2>

    @if(\Auth::user()->admin || \Auth::user()->isFormAdmin($form))
        <div>
            <a href="{{ action('ExportController@exportForm',['pid' => $form->pid, 'fid' => $form->fid]) }}">[{{trans('forms_show.export')}}]</a>
        </div> <br>
    @endif

    @include('forms.layout.logic',['form' => $form, 'fieldview' => 'forms.layout.printfield', 'layoutPage' => true])

    @if(\Auth::user()->canCreateFields($form))
        <form action="{{action('FormController@addNode', ['pid' => $form->pid, 'fid' => $form->fid]) }}"
              method="POST" class="form-group form-inline">
            <input type="hidden" value="{{ csrf_token() }}" name="_token">
            <input type="text" name="name" class = "form-control" required/>
            <input type="submit" value="{{trans('forms_show.createnode')}}" class="btn form-control">
        </form>
        <form action="{{action('FieldController@create', ['pid' => $form->pid, 'fid' => $form->fid]) }}">
            <input type="submit" value="{{trans('forms_show.createfield')}}" class="btn btn-primary form-control">
        </form>
    @endif

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

        function deleteField(fieldName, flid) {
            var response = confirm("{{trans('forms_show.areyousure')}} "+fieldName+"?");
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

        var fieldNavAjax = '{{ action('FieldNavController@index') }}';

        function moveFieldUp(flid){
            $.post(fieldNavAjax, { action:'moveFieldUp', flid:flid, _token: "{{ csrf_token() }}", _method:'POST'},
                    function(resp){
                        location.reload();
                    }, 'html');
        }

        function moveFieldDown(flid){
            $.post(fieldNavAjax, { action:'moveFieldDown', flid:flid, _token: "{{ csrf_token() }}", _method:'POST'},
                    function(resp){
                        location.reload();
                    }, 'html');
        }

        function moveFieldUpIn(flid){
            $.post(fieldNavAjax, { action:'moveFieldUpIn', flid:flid, _token: "{{ csrf_token() }}", _method:'POST'},
                    function(resp){
                        location.reload();
                    }, 'html');
        }

        function moveFieldDownIn(flid){
            $.post(fieldNavAjax, { action:'moveFieldDownIn', flid:flid, _token: "{{ csrf_token() }}", _method:'POST'},
                    function(resp){
                        location.reload();
                    }, 'html');
        }

        function moveFieldUpOut(flid){
            $.post(fieldNavAjax, { action:'moveFieldUpOut', flid:flid, _token: "{{ csrf_token() }}", _method:'POST'},
                    function(resp){
                        location.reload();
                    }, 'html');
        }

        function moveFieldDownOut(flid){
            $.post(fieldNavAjax, { action:'moveFieldDownOut', flid:flid, _token: "{{ csrf_token() }}", _method:'POST'},
                    function(resp){
                        location.reload();
                    }, 'html');
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