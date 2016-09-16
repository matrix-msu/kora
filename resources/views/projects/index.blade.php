@extends('app')

@section('content')
    <h1>{{trans('projects_index.dash')}}</h1>

    <hr/>

    @include('projectSearch.bar', ['projectArrays' => $projectArrays])

    @include('partials.adminpanel')

    <hr/>
    <h2>{{trans('projects_index.title')}}</h2>
    @if(\Auth::user()->admin)
        <div>
            <a href="{{ action('ProjectController@importProjectView') }}">[{{trans('projects_index.import')}}]</a>
        </div>
    @endif
    <div id="toggle_proj">
        Toggle Inactive: <input type="checkbox" id="toggle_proj_check">
    </div> <br>
    @foreach ($projects as $project)
        @if((\Auth::user()->admin || \Auth::user()->inAProjectGroup($project)))
            @if($project->active==1)
            <div class="panel panel-default">
                <div class="panel-heading">
                    <a href="{{ action('ProjectController@show',[$project->pid]) }}" style="font-size: 1.5em;">{{ $project->name }}</a>
                </div>
                <div class="collapseTest" style="display:none">
                <div class="panel-body">
                    <span><b>{{trans('projects_index.status')}}:</b> </span>
                    <span style="color:green">{{trans('projects_index.active')}}</span>
                    <div><b>{{trans('projects_index.desc')}}:</b> {{ $project->description }}</div>
                </div>
            @else
            <div class="panel panel-default inactiveProj" style="display:none">
                <div class="panel-heading" style="font-size: 1.5em;">
                    {{ $project->name }}
                </div>
                <div class="collapseTest" style="display:none">
                <div class="panel-body">
                    <span><b>{{trans('projects_index.status')}}:</b> </span>
                    <span style="color:red">{{trans('projects_index.inactive')}}</span>
                    <div><b>{{trans('projects_index.desc')}}:</b> {{ $project->description }}</div>
                </div>
            @endif
            <div class="panel-footer">

                <span>
                    @if(\Auth::user()->admin) <a href="{{ action('ProjectController@edit',[$project->pid]) }}">[{{trans('projects_index.edit')}}]</a> @endif
                </span>
                <span>
                    @if(\Auth::user()->admin) <a onclick="deleteProject('{{ $project->name }}', {{ $project->pid }})" href="javascript:void(0)">[{{trans('projects_index.delete')}}]</a> @endif
                </span>
            </div></div><!-- this is the close tag for the collapseTest div -->
        </div>
        @endif
    @endforeach

    <br/>

    <form action="{{ action('ProjectController@create') }}">
        @if(\Auth::user()->admin) <input type="submit" value="{{trans('projects_index.create')}}" class="btn btn-primary form-control"> @endif
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

        $( "#toggle_proj" ).on( "click", "#toggle_proj_check", function() {
            var checked = $(this).is(":checked");

            $('.inactiveProj').each(function () {
                if (checked){
                    $(this).slideDown();
                }else {
                    $(this).slideUp();
                }
            });
        });

        function deleteProject(projName,pid) {
            var encode = $('<div/>').html("{{trans('projects_index.areyousure')}}").text();
            var response = confirm(encode + projName + "?");
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