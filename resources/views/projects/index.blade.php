@extends('app')

@section('header')
    <div id="kora_header_title">Projects</div>
    <div id="kora_header_description">Select a project below or create a project to get started.</div>
@stop

@section('body')
    <div id="project_index_bar">
        <div id="project_index_search">
            Find a Project
        </div>
        <div id="project_index_sort">
            <a class="project_index_sort_opt">Recently Modified</a>
            <a class="project_index_sort_opt">Custom</a>
            <a class="project_index_sort_opt">Alphabetical</a>
            <a class="project_index_sort_opt">Inactive</a>
        </div>
    </div>
    <div id="project_index_create">
        <form action="{{ action('ProjectController@create') }}">
            @if(\Auth::user()->admin)
                <input type="submit" value="Create a New Project">
            @endif
        </form>
    </div>
    <div id="project_index_cards">
        @foreach($projects as $project)
            <div class="project_index_card">
                <div class="project_index_card_header">
                    <a href="{{action("ProjectController@show",["pid" => $project->pid])}}">{{$project->name}} -></a>
                </div>
                <div class="project_index_card_body">
                    <div class="project_index_card_slug">
                        Unique Project ID: {{$project->slug}}
                    </div>
                    <div class="project_index_card_desc">
                        Project description: {{$project->description}}
                    </div>
                    <div class="project_index_card_admins">
                        Project Admins:
                        @foreach($project->adminGroup()->get() as $adminGroup)
                            {{$adminGroup->users()->lists("username")->implode("username",", ")}}
                        @endforeach
                    </div>
                    <div class="project_index_card_slug">
                        Project Forms:
                        @foreach($project->forms()->get() as $form)
                            <a href="{{action("FormController@show",["pid" => $project->pid,"fid" => $form->fid])}}">{{$form->name}}</a>
                        @endforeach
                    </div>
                </div>
                <div class="project_index_card_footer">

                </div>
            </div>
        @endforeach
    </div>
    <div id="project_index_requests">
        <div id="project_index_requests_text">
            Don't see the project you are looking for? You might not have the permissions...
        </div>
        <a href="#" id="project_index_requests_link">
            Request Permissions to a Project
        </a>
    </div>
@stop

@section('footer')
    <script>

    </script>
@stop