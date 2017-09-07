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
        </h1>
        <p class="description">{{ $project->description }}</p>
      </div>
  </section>
@stop

@section('body')

    <div><b>{{trans('projects_show.name')}}:</b> {{ $project->slug }}</div>

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

@stop

@section('javascripts')
  {!! Minify::javascript([
    '/assets/javascripts/vendor/jquery/jquery.js',
    '/assets/javascripts/vendor/jquery/jquery-ui.js',
    '/assets/javascripts/vendor/chosen.js',
    '/assets/javascripts/general/modal.js',
    '/assets/javascripts/projects/create.js',
    '/assets/javascripts/projects/index.js',
    '/assets/javascripts/projects/show.js',
    '/assets/javascripts/navigation/navigation.js',
    '/assets/javascripts/general/global.js'
  ])->withFullUrl() !!}

  <script type="text/javascript">
    var formDestroyUrl = '{{ action('FormController@destroy',['pid' => $project->pid, 'fid' => '']) }}';
    var areYouSure = '{{ trans('projects_show.areyousure') }}';
    var CSRFToken = '{{ csrf_token() }}';

    Kora.Projects.Show();
  </script>
@stop
