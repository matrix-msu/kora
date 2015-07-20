@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->pid])
@stop

@section('content')
    <h1>{{ $project->name }}</h1>
    <div><b>Internal Name:</b> {{ $project->slug }}</div>
    <div><b>Description:</b> {{ $project->description }}</div>

    @if (\Auth::user()->admin ||  \Auth::user()->isProjectAdmin($project))
    <form action="{{action('ProjectGroupController@index', ['pid'=>$project->pid])}}" style="display: inline">
        <button type="submit" class="btn btn-default">Manage Groups</button>
    </form>
    @endif
    <hr/>
    <h2>Forms</h2>
    @foreach($project->forms as $form)
        <div class="panel panel-default">
            <div class="panel-heading" style="font-size: 1.5em;">
                <a href="{{ action('FormController@show',['pid' => $project->pid,'fid' => $form->fid]) }}">{{ $form->name }}</a>
            </div>
            <div class="collapseTest" style="display:none">
                <div class="panel-body"><b>Description:</b> {{ $form->description }}</div>
                <div class="panel-footer">
                    @if(\Auth::user()->canEditForms($project))
                    <span>
                        <a href="{{ action('FormController@edit',['pid' => $project->pid, 'fid' => $form->fid]) }}">[Edit]</a>
                    </span>
                    @endif
                    @if(\Auth::user()->canDeleteForms($project))
                    <span>
                        <a onclick="deleteForm('{{ $form->name }}', {{ $form->fid }})" href="javascript:void(0)">[Delete]</a>
                    </span>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    @if(\Auth::user()->canCreateForms($project))
    <form action="{{ action('FormController@create', ['pid' => $project->pid]) }}">
        <input type="submit" value="Create New Form" class="btn btn-primary form-control">
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