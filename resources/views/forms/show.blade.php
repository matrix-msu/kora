@extends('app', ['page_title' => "{$form->name} Form", 'page_class' => 'form-show'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->project_id])
    @include('partials.menu.form', ['pid' => $form->project_id, 'fid' => $form->id])
@stop

@section('aside-content')
  @include('partials.sideMenu.form', ['pid' => $form->project_id, 'fid' => $form->id, 'openDrawer' => true])
@stop

@section('stylesheets')
  <!-- No Additional Stylesheets Necessary -->
@stop

@section('header')
  <section class="head">
    <a class="back" href=""><i class="icon icon-chevron"></i></a>
    <div class="inner-wrap center">
      <h1 class="title">
        <i class="icon icon-form"></i>
        <a href="{{ action('FormController@edit',['pid' => $form->project_id, 'fid' => $form->id]) }}" class="head-button tooltip" tooltip="Edit Form">
          <i class="icon icon-edit right"></i>
        </a>
        <span>{{ $form->name }}</span>
      </h1>
      <p class="identifier">
        <span>Unique Form ID:</span>
        <span>{{ $form->internal_name }}</span>
      </p>
      <p class="description">{!! nl2br(e($form->description)) !!}</p>

      <div class="form-group">
        <div class="form-quick-options">
          <div class="button-container">
			@php
              $count = $form->getRecordCount();
            @endphp
            <a href="{{ url('/projects/'.$form->project_id).'/forms/'.$form->id.'/records'}}" class="btn half-sub-btn">Form Records & Search ({{ $count }})</a>
            <a href="@if ($hasFields) {{ action('RecordController@create',['pid' => $form->project_id, 'fid' => $form->id]) }} @endif" class="btn half-sub-btn
                @if(!$hasFields) disabled tooltip @endif" tooltip="Whoops, you can’t create a new record when the form has no fields.">Create New Record</a>
          </div>
        </div>
      </div>
    </div>
  </section>
@stop


@section('body')
  @include('partials.projects.notification')

  @if ($hasFields)
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
          Are you sure you wish to delete this field? Deleting will remove any data collected for this field on preexisting records within this form. This cannot be undone.
        </span>

        <div class="form-group">
          <a href="#" class="btn warning delete-field-confirm-js">Delete Field</a>
        </div>
      </div>
    </div>
  </div>

  <section class="pages pages-js center {{ $hasFields ? '' : 'mt-xxxl' }}">
    @php
      $pages_count = sizeof($layout['pages']);
    @endphp

    @foreach($layout['pages'] as $idx=>$page)
      <div class="page" page-id="{{$idx}}">
        <div class="page-header">
          <div class="move-actions">
            <a class="action move-action-page-js up-js" page_id="{{$idx}}" href="#">
              <i class="icon icon-arrow-up"></i>
            </a>

            <a class="action move-action-page-js down-js" page_id="{{$idx}}" href="#">
              <i class="icon icon-arrow-down"></i>
            </a>
          </div>

          <div class="form-group title-container">
            {!! Form::text('name', null, ['class' => 'title page-title-js', 'placeholder' => $page["title"], 'pageid' => $idx]) !!}
          </div>

          @if ($pages_count > 1)
		  <div>
		    <a href="#" data-page='{{$idx}}' class="cancel-container delete-page-js tooltip" tooltip="Delete Page">
		  	  <i class="icon icon-cancel"></i>
		    </a>
		  </div>
		  @elseif ($pages_count == 1)
		  <div>
		    <a href="#" data-page='{{$idx}}' class="cancel-container-disabled delete-page-js delete-disabled not-allowed">
		  	  <i class="icon-cancel not-allowed"></i>
		   </a>
		  </div>
		  @endif
        </div>

        <div class="field-sort-js" style="min-height: 10px;">
          @php
            $index = 0;
            $onFormPage = true;
          @endphp
        @foreach($page["flids"] as $flid)
            <div class="field-container">
              @include('forms.layout.field', ['flid' => $flid, 'field' => $layout['fields'][$flid], 'pid' => $form->project_id, 'fid' => $form->id])
            </div>
            @php $index++; @endphp
          @endforeach
        </div>

        @if(\Auth::user()->canCreateFields($form))
          <form method="DET" action="{{action('FieldController@create', ['pid' => $form->project_id, 'fid' => $form->id, 'rootPage' => $idx]) }}">
            <div class="form-group new-field-button">
              <input type="submit" value="Create New Field Here">
            </div>
          </form>
        @endif

        @include('forms.layout.no-fields')
      </div>

      @if(\Auth::user()->canCreateFields($form))
        <div class="form-group new-page-button">
          <a href="#" data-new-page="{{$idx + 1}}" class="new-page-js btn transparent">Create New Form Page Here</a>
        </div>
      @endif

    @endforeach
  </section>
@stop

@section('javascripts')
  @include('partials.forms.javascripts')

  <script type="text/javascript">
    var modifyFormPageRoute = "{{ action('PageController@modifyFormPage', ['pid' => $form->project_id, 'fid' => $form->id]) }}";
    var saveFullFormLayoutRoute = "{{ action('PageController@saveFullFormLayout', ['pid' => $form->project_id, 'fid' => $form->id]) }}";
    var addMethod = "{{\App\Http\Controllers\PageController::_ADD}}";
    var delMethod = "{{\App\Http\Controllers\PageController::_DELETE}}";
    var renameMethod = "{{\App\Http\Controllers\PageController::_RENAME}}";
    var upMethod = "{{\App\Http\Controllers\PageController::_UP}}";
    var downMethod = "{{\App\Http\Controllers\PageController::_DOWN}}";
    Kora.Forms.Show();
  </script>
@stop
