@extends('app', ['page_title' => "Record Revisions", 'page_class' => 'record-revisions'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->project_id])
    @include('partials.menu.form', ['pid' => $form->project_id, 'fid' => $form->id])
    @if(isset($rid) && !is_null($record))
        @include('partials.menu.record', ['pid' => $form->project_id, 'fid' => $form->id, 'rid' => $rid])
    @endif
    @include('partials.menu.static', ['name' => 'Record Revisions'])
@stop

@section('aside-content')
  @if (isset($rid))
      @include('partials.sideMenu.form', ['pid' => $form->project_id, 'fid' => $form->id])
      @include('partials.sideMenu.record', ['pid' => $form->project_id, 'fid' => $form->id, 'rid' => $rid, 'openDrawer' => true])
  @else
    @include('partials.sideMenu.form', ['pid' => $form->project_id, 'fid' => $form->id, 'openDrawer' => true])
  @endif
@stop

@section('header')
    <section class="head">
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-clock"></i>
                <span>Record Revisions{{isset($rid) ? ': ' . $form->project_id . '-' . $form->id . '-' . $rid : ''}}</span>
            </h1>
            <p class="description">
                Use this page to view and manage record revisions within this form.
                Record revisions allow you to see all of the changes made to all the records within the form,
                and gives you the ability to revert records to a previous revision.
            </p>
        </div>
    </section>
@stop

@section('body')
    @include('partials.revisions.modals.restoreFieldsModal')
    @include('partials.revisions.modals.reactivateRecordModal')
    @include('partials.projects.notification')

    @if (count($revisions) > 0 )
      @include('partials.revisions.filters')
      <section class="revisions revisions-js center">
          @foreach ($revisions as $index=>$revision)
              @include('partials.revisions.card')
          @endforeach
      </section>
      @include('partials.revisions.pagination')
    @else
      @if ($_GET &&
        (array_key_exists('records', $_GET) ||
        array_key_exists('users', $_GET) ||
        array_key_exists('dates', $_GET))
      )
        @include('partials.revisions.filters')
      @endif

      @include('partials.revisions.no-revisions')
    @endif
@stop

@section('javascripts')
    @include('partials.revisions.javascripts')
@stop

@section('stylesheets')
  <!-- <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css"> -->
@stop
