@extends('app')

@section('content')
    <h1>My Projects</h1>

    <hr/>

    @foreach ($projects as $project)
        <project>
            @if($project->active==1)
                <h2>
                    <a href="{{ action('ProjectController@show',[$project->pid]) }}">{{ $project->name }}</a>
                </h2>
                <div>
                    <span>Status: </span>
                    <span style="color:green">Active</span>
                </div>
            @else
                <h2>
                    <div>{{ $project->name }}</div>
                </h2>
                <div>
                    <span>Status: </span>
                    <span style="color:red">Inactive</span>
                </div>
            @endif


            <div class="body">Description: {{ $project->description }}</div>
            <span>
                <a href="{{ action('ProjectController@edit',[$project->pid]) }}">[Edit]</a>
            </span>
            <span>
                <a onclick="deleteProject('{{ $project->name }}', {{ $project->pid }})" href="javascript:void(0)">[Delete]</a>
            </span>
        </project>
    @endforeach

    <br/>

    <form action="{{ action('ProjectController@create') }}">
        <input type="submit" value="Create New" class="btn btn-primary form-control">
    </form>
@stop

@section('footer')
    <script>
        function deleteProject(projName,pid) {
            var response = confirm("Are you sure you want to delete "+projName+"?");
            if (response) {
                $.ajax({
                    //We manually create the link in a cheap way because the JS isn't aware of the pid until runtime
                    //We pass in a blank project to the action array and then manually add the id
                    url: '{{ action('ProjectController@destroy',['']) }}/'+pid,
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function (result) {
                        location.reload();
                    }
                });
            }
        }
    </script>
@stop