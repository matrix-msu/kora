@extends('app', ['page_title' => 'Projects'])

@section('header')
  <section class="head">
      <div class="inner-wrap">
        <h1 class="title">
          <img class="" src="{{ env('BASE_URL') }}assets/images/projects.svg">
          <span>Projects</span>
        </h1>
        <p class="description">Select a project below or create a project to get started.</p>
      </div>
  </section>
@stop

@section('body')
  <section class="filters">
      <div class="left search search-js">
        <img class="icon icon-search" src="{{ env('BASE_URL') }}assets/images/search-dark.svg">
        <input type="text" placeholder="Find a Project">
        <img class="icon icon-cancel icon-cancel-js" src="{{ env('BASE_URL') }}assets/images/cancel-dark.svg">
      </div>
      <div class="right sort-options sort-options-js">
          <a class="option active">Recently Modified</a>
          <a class="option">Custom</a>
          <a class="option">Alphabetical</a>
          <a class="option">Inactive</a>
      </div>
  </section>
  <section class="new-project-button">
    <form action="{{ action('ProjectController@create') }}">
      @if(\Auth::user()->admin)
        <input type="submit" value="Create a New Project">
      @endif
    </form>
  </section>
    <!-- <div id="project_index_cards">
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
    </div> -->
@stop

@section('footer')
    <script>
      window.onload = function() {
        $('.sort-options-js a').click(function(e) {
          e.preventDefault();

          $('.sort-options-js a').removeClass('active');
          $(this).addClass('active');
        });

        $('.search-js img, .search-js input').click(function(e) {
          e.preventDefault();

          $(this).parent().addClass('active');
          $('.search-js input').focus();
        });

        $('.search-js input').focusout(function() {
          if (this.value.length == 0) {
            $(this).parent().removeClass('active');
            $(this).next().removeClass('active');
          }
        })

        $('.search-js input').keyup(function(e) {
          if (e.keyCode === 27) {
            $(this).val('');
          }

          if (this.value.length > 0) {
            $(this).next().addClass('active');
          } else {
            $(this).next().removeClass('active');
          }
        })

        $('.search-js .icon-cancel-js').click(function() {
          var $search = $('.search-js input');
          $search.val('').blur().parent().removeClass('active');
        })
      }
    </script>
@stop
