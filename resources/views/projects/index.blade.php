@extends('app')

@section('content')
    <h1>My Dashboard</h1>

    @include('partials.adminpanel')

    <hr/>
    <h2>Projects</h2>
    @foreach ($projects as $project)
        @if((\Auth::user()->admin || \Auth::user()->inAProjectGroup($project)))
            <div class="panel panel-default">
            @if($project->active==1)
                <div class="panel-heading">
                    <a href="{{ action('ProjectController@show',[$project->pid]) }}" style="font-size: 1.5em;">{{ $project->name }}</a>
                </div>
                <div class="collapseTest" style="display:none">
                <div class="panel-body">
                    <span><b>Status:</b> </span>
                    <span style="color:green">Active</span>
                    <div><b>Description:</b> {{ $project->description }}</div>
                </div>
            @else
                <div class="panel-heading" style="font-size: 1.5em;">
                    {{ $project->name }}
                </div>
                <div class="collapseTest" style="display:none">
                <div class="panel-body">
                    <span><b>Status:</b> </span>
                    <span style="color:red">Inactive</span>
                    <div><b>Description:</b> {{ $project->description }}</div>
                </div>
            @endif
            <div class="panel-footer">

                <span>
                    @if(\Auth::user()->admin) <a href="{{ action('ProjectController@edit',[$project->pid]) }}">[Edit]</a> @endif
                </span>
                <span>
                    @if(\Auth::user()->admin) <a onclick="deleteProject('{{ $project->name }}', {{ $project->pid }})" href="javascript:void(0)">[Delete]</a> @endif
                </span>
            </div></div><!-- this is the close tag for the collapseTest div -->
        </div>
        @endif
    @endforeach

    <br/>

    <form action="{{ action('ProjectController@create') }}">
        @if(\Auth::user()->admin) <input type="submit" value="Create New Project" class="btn btn-primary form-control"> @endif
    </form>

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

        function deleteProject(projName,pid) {
            var response = confirm("Are you sure you want to delete "+projName+"?");
            if (response) {
                $.ajax({
                    //We manually create the link in a cheap way because the JS isn't aware of the pid until runtime
                    //We pass in a blank project to the action array and then manually add the id
                    url: '{{ action('ProjectController@destroy',['']) }}/'+pid,
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