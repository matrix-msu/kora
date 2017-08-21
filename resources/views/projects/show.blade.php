@extends('app', ['page_title' => 'Project'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->pid])
@stop

@section('content')
    <h1>{{ $project->name }}</h1>

    <div><b>{{trans('projects_show.name')}}:</b> {{ $project->slug }}</div>
    <div><b>{{trans('projects_show.desc')}}:</b> {{ $project->description }}</div>

    <hr/>

    @include('projectSearch.bar', ['projectArrays' => $projectArrays])

    @if (\Auth::user()->admin ||  \Auth::user()->isProjectAdmin($project))
        <hr/>

        <h4> {{trans('projects_show.admin')}}</h4>
    <form action="{{action('ProjectGroupController@index', ['pid'=>$project->pid])}}" style="display: inline">
        <button type="submit" class="btn btn-default">{{trans('projects_show.groups')}}</button>
    </form>
    <form action="{{action('OptionPresetController@index', ['pid'=>$project->pid])}}" style="display: inline">
        <button type="submit" class="btn btn-default">{{trans('projects_show.presets')}}</button>
    </form>
    <form action="{{action('FormController@importFormViewK2',['pid' => $project->pid])}}" style="display: inline">
        <button type="submit" class="btn btn-default">{{trans('projects_show.importk2')}}</button>
    </form>
    @endif
    <hr/>
    <h2>{{trans('projects_show.forms')}}</h2>
    @if(\Auth::user()->admin || \Auth::user()->isProjectAdmin($project))
        <div>
            <a href="{{ action('ExportController@exportProject',['pid' => $project->pid]) }}">[{{trans('projects_show.export')}}]</a>
            <a href="{{ action('FormController@importFormView',['pid' => $project->pid]) }}">[{{trans('projects_show.import')}}]</a>
        </div> <br>
    @endif
    @foreach($project->forms as $form)
        @if(\Auth::user()->admin || \Auth::user()->inAFormGroup($form))
        <div class="panel panel-default">
            <div class="panel-heading" style="font-size: 1.5em;">
                <a href="{{ action('FormController@show',['pid' => $project->pid,'fid' => $form->fid]) }}">{{ $form->name }}</a>
            </div>
            <div class="collapseTest" style="display:none">
                <div class="panel-body">
                    <b>{{trans('projects_show.name')}}:</b> {{ $form->slug }}<br>
                    <b>{{trans('projects_show.desc')}}:</b> {{ $form->description }}
                </div>
                <div class="panel-footer">
                    @if(\Auth::user()->canEditForms($project))
                    <span>
                        <a href="{{ action('FormController@edit',['pid' => $project->pid, 'fid' => $form->fid]) }}">[{{trans('projects_show.edit')}}]</a>
                    </span>
                    @endif
                    @if(\Auth::user()->canDeleteForms($project))
                    <span>
                        <a onclick="deleteForm('{{ $form->name }}', {{ $form->fid }})" href="javascript:void(0)">[{{trans('projects_show.delete')}}]</a>
                    </span>
                    @endif
                </div>
            </div>
        </div>
        @endif
    @endforeach

    @if(\Auth::user()->canCreateForms($project))
    <form action="{{ action('FormController@create', ['pid' => $project->pid]) }}">
        <input type="submit" value="{{trans('projects_show.create')}}" class="btn btn-primary form-control">
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
            var encode = $('<div/>').html("{{ trans('projects_show.areyousure') }}").text();
            var response = confirm(encode + formName + "?");
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
