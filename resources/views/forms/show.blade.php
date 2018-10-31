@extends('app', ['page_title' => "{$form->name} Form", 'page_class' => 'form-show'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
@stop

@section('aside-content')
  @include('partials.sideMenu.form', ['pid' => $form->pid, 'fid' => $form->fid, 'openDrawer' => true])
@stop

@section('stylesheets')
  <!-- No Additional Stylesheets Necessary -->
@stop

@section('header')
  <section class="head">
    <a class="back" href="{{ URL::previous() }}"><i class="icon icon-chevron"></i></a>
    <div class="inner-wrap center">
      <h1 class="title">
        <i class="icon icon-form"></i>
        <a href="{{ action('FormController@edit',['pid' => $form->pid, 'fid' => $form->fid]) }}" class="head-button tooltip" tooltip="Edit Form">
          <i class="icon icon-edit right"></i>
        </a>
        <span>{{ $form->name }}</span>
      </h1>
      <p class="identifier">
        <span>Unique Form ID:</span>
        <span>{{ $form->slug }}</span>
      </p>
      <p class="description">{{ $form->description }}</p>

      <div class="form-group">
        <div class="form-quick-options">
          <div class="button-container">
			<?php $count = count($form->records) ?>
            <a href="{{ url('/projects/'.$form->pid).'/forms/'.$form->fid.'/records'}}" class="btn half-sub-btn">Form Records & Search ({{ $count }})</a>
            <a href="@if ($hasFields) {{ action('RecordController@create',['pid' => $form->pid, 'fid' => $form->fid]) }} @endif" class="btn half-sub-btn
                @if(!$hasFields) disabled tooltip @endif" tooltip="Whoops, you can’t create a new record when the form has no fields.">Create New Record</a>
          </div>
        </div>
      </div>
    </div>
  </section>
@stop


@section('body')
  <?php
  $page_has_fields = false;

  foreach($pageLayout as $page)
  {
    if (count($page["fields"]) > 0)
  	{
	  $page_has_fields = true;
	  break;	
  	}
  }
  ?>

  @include('partials.projects.notification')

  @if ($page_has_fields)
  <section class="filters center">
    <div class="underline-middle search search-js">
      <i class="icon icon-search"></i>
      <input type="text" placeholder="Find a Field">
      <i class="icon icon-cancel icon-cancel-js"></i>
    </div>
    <div class="show-options show-options-js">
      <a href="#" class="expand-fields-js tooltip" title="Expand all fields" tooltip="Expand All Fields"><i class="icon icon-expand icon-expand-js"></i></a>
      <a href="#" class="collapse-fields-js tooltip" title="Collapse all fields" tooltip="Condense All Fields"><i class="icon icon-condense icon-condense-js"></i></a>
    </div>
  </section>
  @endif

  <div class="modal modal-js modal-mask page-delete-modal-js">
    <div class="content small">
      <div class="header">
        <span class="title">Delete Page?</span>
        <a href="#" class="modal-toggle modal-toggle-js">
          <i class="icon icon-cancel"></i>
        </a>
      </div>
      <div class="body">
        <span class="description">
          Are you sure you wish to delete this page from this form?
          Doing so will also delete all of the fields within this page.
          You must move all fields to a different form page if you wish
          to keep them.
        </span>

        <div class="form-group">
          <a href="#" class="btn warning delete-page-confirm-js">Delete Page</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal modal-js modal-mask field-delete-modal-js">
    <div class="content small">
      <div class="header">
        <span class="title">Delete Field?</span>
        <a href="#" class="modal-toggle modal-toggle-js">
          <i class="icon icon-cancel"></i>
        </a>
      </div>
      <div class="body">
        <span class="description">
          Are you sure you wish to delete this field from this page?
        </span>

        <div class="form-group">
          <a href="#" class="btn warning delete-field-confirm-js">Delete Field</a>
        </div>
      </div>
    </div>
  </div>

  <section class="pages pages-js center {{ $page_has_fields ? '' : 'mt-xxxl' }}">
    <?php $pages_count = count($pageLayout); ?>
    @foreach($pageLayout as $idx=>$page)
      <div class="page" page-id="{{$page["id"]}}">
        <div class="page-header">
          <div class="move-actions">
            <a class="action move-action-page-js up-js" page_id="{{$page["id"]}}" href="#">
              <i class="icon icon-arrow-up"></i>
            </a>

            <a class="action move-action-page-js down-js" page_id="{{$page["id"]}}" href="#">
              <i class="icon icon-arrow-down"></i>
            </a>
          </div>

          <div class="form-group title-container">
            {!! Form::text('name', null, ['class' => 'title page-title-js', 'placeholder' => $page["title"], 'pageid' => $page["id"]]) !!}
          </div>

          @if ($pages_count > 1)
		  <div>
		    <a href="#" data-page='{{$page["id"]}}' class="cancel-container delete-page-js tooltip" tooltip="Delete Page">
		  	  <i class="icon icon-cancel"></i>
		    </a>
		  </div>
		  @elseif ($pages_count == 1)
		  <div>
		    <a href="#" data-page='{{$page["id"]}}' class="cancel-container-disabled delete-page-js delete-disabled not-allowed">
		  	  <i class="icon-cancel not-allowed"></i>
		   </a>
		  </div>
		  @endif
        </div>

        <div class="field-sort-js" style="min-height: 10px;">
        @foreach($page["fields"] as $index=>$field)
            <div class="field-container">
              @include('forms.layout.field', ['field' => $field])
            </div>
          @endforeach
        </div>

        @if(\Auth::user()->canCreateFields($form))
          <form method="DET" action="{{action('FieldController@create', ['pid' => $form->pid, 'fid' => $form->fid, 'rootPage' => $page['id']]) }}">
            <div class="form-group new-field-button">
              <input type="submit" value="Create New Field Here">
            </div>
          </form>
          @if (!count($page["fields"]) > 0)
            @include('forms.layout.no-fields')
          @endif
        @endif
      </div>

      @if(\Auth::user()->canCreateFields($form))
        <div class="form-group new-page-button">
          <a href="#" data-new-page="{{$idx + 2}}" data-prev-page="{{$page["id"]}}" class="new-page-js btn transparent">Create New Form Page Here</a>
        </div>
      @endif

    @endforeach
  </section>
@stop

@section('javascripts')
  @include('partials.forms.javascripts')

  <script type="text/javascript">
    var modifyFormPageRoute = "{{ action('PageController@modifyFormPage', ['pid' => $form->pid, 'fid' => $form->fid]) }}";
    var saveFullFormLayoutRoute = "{{ action('PageController@saveFullFormLayout', ['pid' => $form->pid, 'fid' => $form->fid]) }}";
    var addMethod = "{{\App\Http\Controllers\PageController::_ADD}}";
    var delMethod = "{{\App\Http\Controllers\PageController::_DELETE}}";
    var renameMethod = "{{\App\Http\Controllers\PageController::_RENAME}}";
    var upMethod = "{{\App\Http\Controllers\PageController::_UP}}";
    var downMethod = "{{\App\Http\Controllers\PageController::_DOWN}}";
    Kora.Forms.Show();
  </script>
@stop
