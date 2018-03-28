@extends('app', ['page_title' => $project->name, 'page_class' => 'project-show'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->pid])
@stop

@section('stylesheets')
  <!-- No Additional Stylesheets Necessary -->
@stop

@section('header')
  <section class="head">
      <a class="rotate" href="{{ URL::previous() }}"><i class="icon icon-chevron"></i></a>
      <div class="inner-wrap center">
        <h1 class="title">
          <i class="icon icon-project"></i>
          <span>{{ $project->name }}</span>
          <a href="{{ action('ProjectController@edit',['pid' => $project->pid]) }}" class="head-button">
            <i class="icon icon-edit right"></i>
          </a>
        </h1>
        <p class="identifier">
          <span>Unique Project ID:</span>
          <span>{{ $project->slug }}</span>
        </p>
        <p class="description">{{ $project->slug }}: {{ $project->description }}</p>
      </div>
  </section>
@stop

@section('body')
  <section class="filters center">
      <div class="underline-middle search search-js">
        <i class="icon icon-search"></i>
        <input type="text" placeholder="Find a Form">
        <i class="icon icon-cancel icon-cancel-js"></i>
      </div>
      <div class="sort-options sort-options-js">
          <!-- <a href="modified" class="option underline-middle">Recently Modified</a> -->
          <a href="#custom" class="option underline-middle underline-middle-hover">Custom</a>
          <a href="#active" class="option underline-middle underline-middle-hover active">Alphabetical</a>
      </div>
  </section>

  <section class="new-object-button center">
    @if(\Auth::user()->canCreateForms($project))
      <form action="{{ action('FormController@create', ['pid' => $project->pid]) }}">
          <input type="submit" value="Create a New Form">
      </form>
    @endif
  </section>

  <section class="form-selection center form-js form-selection-js">
    @include("partials.projects.show.alphabetical", ['isCustom' => false, 'active' => true])
    @include("partials.projects.show.custom", ['isCustom' => true, 'active' => false])
  </section>
@stop

@section('footer')

@stop

@section('javascripts')
  @include('partials.projects.javascripts')

  <script type="text/javascript">
    var CSRFToken = '{{ csrf_token() }}';
    var saveCustomOrderUrl = '{{ action('Auth\UserController@saveFormCustomOrder', ['pid' => $project->pid]) }}';

    Kora.Projects.Show();
  </script>
@stop
