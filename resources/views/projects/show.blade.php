@extends('app')

@section('content')
    <span><h1>{{ $project->name.' ('.$project->slug.')' }}</h1></span>
    <div>Description: {{ $project->description }}</div>
    <div>Admin: (Display Admin Here)</div>
    <hr/>

    @foreach($project->forms as $form)
        <div class="panel panel-default">
            <div class="panel-heading" style="font-size: 1.5em;">
                <a href="{{ action('FormController@show',['pid' => $project->pid, 'fid' => $form->fid]) }}">{{ $form->name }}</a>
            </div>
            <div class="panel-body">{{ $form->description }}</div>
            <div class="panel-footer">
                <span>
                    <a href="{{ action('FormController@edit',['pid' => $project->pid, 'fid' => $form->fid]) }}">[Edit]</a>
                </span>
                <span>
                    <a onclick="deleteForm('{{ $form->name }}', {{ $form->fid }})" href="javascript:void(0)">[Delete]</a>
                </span>
            </div>
        </div>
    @endforeach

    <form action="{{ action('FormController@create', ['pid' => $project->pid]) }}">
        <input type="submit" value="Create New Form" class="btn btn-primary form-control">
    </form>
@stop

@section('footer')
    <script>
        function deleteForm(formName, fid) {
            var response = confirm("Are you sure you want to delete "+formName+"?");
            if (response) {
                $.ajax({
                    //We manually create the link in a cheap way because the JS isn't aware of the fid until runtime
                    //We pass in a blank project to the action array and then manually add the id
                    url: '{{ action('FormController@destroy',['pid' => $project->pid, 'fid' => '']) }}/'+fid,
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
    </script>
@stop