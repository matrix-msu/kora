@extends('app', ['page_title' => $project->name, 'page_class' => 'project-show'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->pid])
@stop

@section('stylesheets')
  <!-- No Additional Stylesheets Necessary -->
@stop

@section('header')
  <section class="head">
      <div class="inner-wrap center">
        <h1 class="title">
          <i class="icon icon-project"></i>
          <span>{{ $project->name }}</span>
          <a href="{{ action('ProjectController@edit',['pid' => $project->pid]) }}" class="head-button">
            <i class="icon icon-edit right"></i>
          </a>
        </h1>
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

  <section class="new-form-button center">
    @if(\Auth::user()->canCreateForms($project))
      <form action="{{ action('FormController@create', ['pid' => $project->pid]) }}">
        @if(\Auth::user()->admin)
          <input type="submit" value="Create a New Form">
        @endif
      </form>
    @endif
  </section>

  <section class="form-selection center form-js form-selection-js">
    @include("partials.projects.show.alphabetical", ['isCustom' => false])
    @include("partials.projects.show.custom", ['isCustom' => true])
  </section>

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
@stop

@section('footer')

@stop

@section('javascripts')
  @include('partials.projects.javascripts')

  <script type="text/javascript">
    var formDestroyUrl = '{{ action('FormController@destroy',['pid' => $project->pid, 'fid' => '']) }}';
    var areYouSure = '{{ trans('projects_show.areyousure') }}';
    var CSRFToken = '{{ csrf_token() }}';

    Kora.Projects.Show();
  </script>
@stop
