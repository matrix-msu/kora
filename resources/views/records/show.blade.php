@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
@stop

@section('content')
    <span><h1>{{ $form->name }}</h1></span>

    @include('partials.adminpanel')

    <hr/>

    <div><b>Internal Name:</b> {{ $form->slug }}</div>
    <div><b>Description:</b> {{ $form->description }}</div>

    @if (\Auth::user()->admin || \Auth::user()->isFormAdmin($form))
        <form action="{{action('FormGroupController@index', ['fid'=>$form->fid])}}" style="display: inline">
            <button type="submit" class="btn btn-default">Manage Groups</button>
        </form>
        <form action="{{action('RevisionController@index', ['fid'=>$form->fid, 'pid'=>$form->pid])}}" style="display: inline">
            <button type="submit" class="btn btn-default">Revision History</button>
        </form>
    @endif

    <div>
        <a href="{{ action('RecordController@index',['pid' => $form->pid, 'fid' => $form->fid]) }}">[Records]</a>
        @if(\Auth::user()->canIngestRecords($form))
        <a href="{{ action('RecordController@create',['pid' => $form->pid, 'fid' => $form->fid]) }}">[New Record]</a>
        @endif
    </div>
    <hr/>
    <h2>Record: {{$record->kid}}</h2>

    <div class="panel panel-default">
        @include('forms.layout.logic',['form' => $form, 'fieldview' => 'records.layout.displayfield'])
        <div><b>Owner:</b> {{ $owner->username }}</div>
        <div><b>Created At:</b> {{ $record->created_at }}</div>
        <div class="panel-footer">
            <span>
                @if(\Auth::user()->canModifyRecords($form) || \Auth::user()->isOwner($record))
                <a href="{{ action('RecordController@edit',['pid' => $form->pid, 'fid' => $form->fid, 'rid' => $record->rid]) }}">[Edit]</a>
                @endif
            </span>
            <span>
                @if(\Auth::user()->canDestroyRecords($form) || \Auth::user()->isOwner($record))
                <a onclick="deleteRecord()" href="javascript:void(0)">[Delete]</a>
                @endif
            </span>
            <span>
                @if(\Auth::user()->admin || \Auth::user()->isFormAdmin($form))
                <a href='{{action('RevisionController@show', ['pid' => $form->pid, 'fid' => $form->fid, 'rid' => $record->rid])}}'>[History]</a>
                @endif
            </span>
        </div>
    </div>
@stop

@section('footer')
    <script>
        function deleteRecord() {
            var response = confirm("Are you sure you want to delete {{$record->kid}}?");
            if (response) {
                $.ajax({
                    //We manually create the link in a cheap way because the JS isn't aware of the fid until runtime
                    //We pass in a blank project to the action array and then manually add the id
                    url: '{{ action('RecordController@destroy', ['pid' => $form->pid, 'fid' => $form->fid, 'rid' => $record->rid]) }}',
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function (result) {
                        location.href = '{{ action('RecordController@index', ['pid' => $form->pid, 'fid' => $form->fid]) }}';
                    }
                });
            }
        }
    </script>
@stop