@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
@stop

@section('content')
    <span><h1>{{ $form->name }}</h1></span>
    <div><b>Internal Name:</b> {{ $form->slug }}</div>
    <div><b>Description:</b> {{ $form->description }}</div>
    <div>
        <a href="{{ action('RecordController@index',['pid' => $form->pid, 'fid' => $form->fid]) }}">[Records]</a>
        <a href="{{ action('RecordController@create',['pid' => $form->pid, 'fid' => $form->fid]) }}">[New Record]</a>
    </div>
    <hr/>
    <h2>Record: {{$record->kid}}</h2>

    <div class="panel panel-default">
        @foreach($form->fields as $field)
            <div>
                <span><b>{{ $field->name }}:</b> </span>
                    <span>
                        @if($field->type=='Text')
                            @foreach($record->textfields as $tf)
                                @if($tf->flid == $field->flid)
                                    {{ $tf->text }}
                                @endif
                            @endforeach
                        @endif
                    </span>
            </div>
        @endforeach
        <div><b>Owner:</b> {{ $record->owner }}</div>
        <div><b>Created:</b> {{ $record->created_at }}</div>
        <div class="panel-footer">
            <span>
                <a href="{{ action('RecordController@edit',['pid' => $form->pid, 'fid' => $form->fid, 'rid' => $record->rid]) }}">[Edit]</a>
            </span>
            <span>
                <a onclick="deleteRecord()" href="javascript:void(0)">[Delete]</a>
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