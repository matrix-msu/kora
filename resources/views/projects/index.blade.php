@extends('app', ['page_title' => 'Projects'])

@section('header')
  <section class="head">
      <div class="inner-wrap">
        <h1 class="title">
          <i class="icon icon-projects"></i>
          <span>Projects</span>
        </h1>
        <p class="description">Select a project below or create a project to get started.</p>
      </div>
  </section>
@stop

@section('body')
  <section class="filters">
      <div class="underline-middle search search-js">
        <i class="icon icon-search"></i>
        <input type="text" placeholder="Find a Project">
        <i class="icon icon-cancel icon-cancel-js"></i>
      </div>
      <div class="sort-options sort-options-js">
          <a class="option underline-middle active">Recently Modified</a>
          <a class="option underline-middle">Custom</a>
          <a class="option underline-middle">Alphabetical</a>
          <a class="option underline-middle">Inactive</a>
      </div>
  </section>
  <section class="new-project-button">
    <form action="{{ action('ProjectController@create') }}">
      @if(\Auth::user()->admin)
        <input type="submit" value="Create a New Project">
      @endif
    </form>
  </section>
  <section class="project-selection project-js project-selection-js">
    @foreach($projects as $index=>$project)
      <div class="project {{ $index == 0 ? 'active' : '' }}">
        <div class="header {{ $index == 0 ? 'active' : '' }}">
          <div class="left">
            <div class="move-actions">
              <a class="action move-action-js up-js" href="">
                <i class="icon icon-arrow-up"></i>
              </a>

              <a class="action move-action-js down-js" href="">
                <i class="icon icon-arrow-down"></i>
              </a>
            </div>

            <a class="project-name" href="{{action("ProjectController@show",["pid" => $project->pid])}}">
              <span class="name">{{$project->name}}</span>
              <i class="icon icon-arrow-right"></i>
            </a>
          </div>
          <div class="project-toggle-wrap">
            <a href="#" class="project-toggle project-toggle-js">
              <i class="icon icon-chevron icon-chevron-down"></i>
            </a>
          </div>
        </div>

        <div class="content content-js {{ $index == 0 ? 'active' : '' }}">
          <div class="id">
            <span class="attribute">Unique Project ID: </span>
            <span>{{$project->slug}}</span>
          </div>

          <div class="description">
            {{$project->description}}
          </div>

          <div class="admins">
            <span class="attribute">Project Admins: </span>
            @foreach($project->adminGroup()->get() as $adminGroup)
              <span>
                {{$adminGroup->users()->lists("username")->implode("username",", ")}}
              </span>
            @endforeach
          </div>

          <div class="forms">
            <span class="attribute">Project Forms:</span>
            @foreach($project->forms()->get() as $form)
              <span class="form"><a class="form-link underline-middle-hover" href="{{action("FormController@show",["pid" => $project->pid,"fid" => $form->fid])}}">{{$form->name}}</a></span>
            @endforeach
          </div>
        </div>

        <div class="footer">
        </div>
      </div>
    @endforeach
  </section>
  <section class="foot">
    <p class="permission-information">
        Don't see the project you are looking for? You might not have the permissions...
    </p>
    <p>
    <a href="#" class="request-permissions">
        Request Permissions to a Project
    </a></p>
  </section>
@stop

@section('footer')
    <script>
      window.onload = function() {
        $('.sort-options-js a').click(function(e) {
          e.preventDefault();

          $('.sort-options-js a').removeClass('active');
          $(this).addClass('active');
        });

        $('.search-js i, .search-js input').click(function(e) {
          e.preventDefault();

          $(this).parent().addClass('active');
          $('.search-js input').focus();
        });

        $('.search-js input').focusout(function() {
          if (this.value.length == 0) {
            $(this).parent().removeClass('active');
            $(this).next().removeClass('active');
          }
        });

        $('.search-js input').keyup(function(e) {
          if (e.keyCode === 27) {
            $(this).val('');
          }

          if (this.value.length > 0) {
            $(this).next().addClass('active');
          } else {
            $(this).next().removeClass('active');
          }
        });

        $('.search-js .icon-cancel-js').click(function() {
          var $search = $('.search-js input');
          $search.val('').blur().parent().removeClass('active');
        });

        $('.project-toggle-js').click(function(e) {
          e.preventDefault();

          var $this = $(this);
          var $header = $this.parent().parent();
          var $project = $header.parent();
          var $content = $header.next();

          $this.children().toggleClass('active');
          $project.toggleClass('active');
          if ($project.hasClass('active')) {
            $header.addClass('active');
            $project.animate({height: $project.height() + $content.outerHeight(true) + 'px' }, 230);
            $content.effect('slide', { direction: 'up', mode: 'show', duration: 240 });
          } else {
            $project.animate({height: '58px'}, 230, function() {
              $header.hasClass('active') ? $header.removeClass('active') : null;
              $content.hasClass('active') ? $content.removeClass('active') : null;
            });
            $content.effect('slide', { direction: 'up', mode: 'hide', duration: 240 });
          }

        });

        $( ".project-selection-js" ).sortable({
          helper : 'clone',
          revert: true,
          containment: ".projects"
        });

        $('.move-action-js').click(function(e){
          e.preventDefault();

          var $this = $(this);
          var $headerInnerWrapper = $this.parent().parent();
          var $header = $headerInnerWrapper.parent();
          var $project = $header.parent();
          // $project.prev().before(current);
          if ($this.hasClass('up-js')) {
            var $previousProject = $project.prev();
            if ($previousProject.length == 0) { return; }

            $previousProject.css('z-index', 999)
              .css('position','relative')
              .animate({ top: $project.height() }, 300);
            $project.css('z-index', 1000)
              .css('position', 'relative')
              .animate({ top: '-' + $previousProject.height() }, 300, function () {
                $previousProject.css('z-index', '')
                  .css('top', '')
                  .css('position', '');
                $project.css('z-index', '')
                  .css('top', '')
                  .css('position', '')
                  .insertBefore($previousProject);
              });
          } else {
            var $nextProject = $project.next();
            if ($nextProject.length == 0) { return; }

            $nextProject.css('z-index', 999)
              .css('position', 'relative')
              .animate({ top: '-' + $project.height() }, 300);
            $project.css('z-index', 1000)
              .css('position', 'relative')
              .animate({ top: $nextProject.height() }, 300, function () {
                $nextProject.css('z-index', '')
                  .css('top', '')
                  .css('position', '');
                $project.css('z-index', '')
                  .css('top', '')
                  .css('position', '')
                  .insertAfter($nextProject);
              });
          }


        });

        $('#myButtonDown').click(function(){
          var current = $('.markedLi');
          current.next().after(current);
        });
      }
    </script>
@stop
