<?php $form = \App\Http\Controllers\FormController::getForm($form->fid) ?>
@include('partials.sideMenu.project', ['pid' => $form->pid, 'openDrawer' => false])
<div class="drawer-element drawer-element-js">
  <a href="#" class="drawer-toggle drawer-toggle-js" data-drawer="{{ $openDrawer or '0' }}">
    <i class="icon icon-form"></i>
    <span>{{ $form->name }}</span>
    <i class="icon icon-chevron"></i>
  </a>

  <ul class="drawer-content drawer-content-js">
      <li class="content-link content-link-js" data-page="form-show">
        <a href="{{ url('/projects/'.$form->pid).'/forms/'.$form->fid}}">
          <span>Form Home</span>
        </a>
      </li>

      <li class="content-link content-link-js" data-page="record-index">
        <a  href="{{ url('/projects/'.$form->pid).'/forms/'.$form->fid.'/records'}}">View Form Records</a>
      </li>

      @if(\Auth::user()->canCreateFields($form))
          <?php $lastPage = \App\Page::where('fid','=',$form->fid)->orderBy('sequence','desc')->first(); ?>
          <li class="content-link content-link-js" data-page="field-create">
              <a href="{{action('FieldController@create', ['pid'=>$form->pid, 'fid' => $form->fid, 'rootPage' =>$lastPage->id])}}">Create New Field</a>
          </li>
      @endif

      <?php
      $fieldsInForm = \App\Field::where('fid', '=', $form->fid)->get()->all();
      $cnt = sizeof($fieldsInForm);
      ?>

      @if(\Auth::user()->canIngestRecords(\App\Http\Controllers\FormController::getForm($form->fid)))
        <li class="content-link content-link-js
        @if($cnt == 0)
            pre-spacer
        @endif
        " data-page="record-create">
          <a href="{{ action('RecordController@create',['pid' => $form->pid, 'fid' => $form->fid]) }}">Create New Record</a>
        </li>
      @endif

      @if($cnt > 0)
          <li class="content-link pre-spacer content-link-js" id="form-submenu">
              <a href='#' class="drawer-sub-menu-toggle drawer-sub-menu-toggle-js" data-toggle="dropdown">
                  <span>Jump to Field</span>
                  <i class="icon icon-plus"></i>
              </a>

              <ul class="drawer-deep-menu drawer-deep-menu-js">
                  @foreach($fieldsInForm as $field)
                      <li class="drawer-deep-menu-link">
                          <a class="padding-fix" href="{{ url('/projects/'.$form->pid).'/forms/'.$field->fid .'/fields/'.$field->flid.'/options'}}">{{ $field->name }}</a>
                      </li>
                  @endforeach
              </ul>
          </li>
      @endif

      @if(\Auth::user()->canIngestRecords(\App\Http\Controllers\FormController::getForm($form->fid)))
        <li class="spacer"></li>
        <li class="content-link content-link-js" data-page="record-import-setup">
          <a href="{{ action('RecordController@importRecordsView',['pid' => $form->pid, 'fid' => $form->fid]) }}">Import Records</a>
        </li>
      @endif

      <li class="content-link content-link-js" data-page="batch-assign">
        <a href="{{ action('RecordController@showMassAssignmentView',['pid' => $form->pid, 'fid' => $form->fid]) }}">Batch Assign Field Values</a>
      </li>

      @if (\Auth::user()->admin || \Auth::user()->isFormAdmin(\App\Http\Controllers\FormController::getForm($form->fid)))
        <li class="content-link content-link-js" data-page="record-revisions">
          <a href="{{action('RevisionController@index', ['pid'=>$form->pid, 'fid'=>$form->fid])}}">Manage Record Revisions</a>
        </li>

        <li class="content-link content-link-js" data-page="record-preset">
          <a href="{{action('RecordPresetController@index', ['pid'=>$form->pid, 'fid'=>$form->fid])}}">Manage Record Presets</a>
        </li>

        <li class="export-record-open content-link content-link-js">
            <a href="#">Export All Records</a>
        </li>

        <li class="content-link content-link-js" data-page="form-edit">
          <a href="{{ action('FormController@edit', ['pid'=>$form->pid, 'fid'=>$form->fid]) }}">Edit Form Information</a>
        </li>

        <li class="content-link content-link-js" data-page="form-permissions">
          <a href="{{ action('FormGroupController@index', ['pid'=>$form->pid, 'fid'=>$form->fid]) }}">Form Permissions</a>
        </li>

        <li class="content-link content-link-js" data-page="form-association-permissions">
          <a href="{{action('AssociationController@index', ['fid'=>$form->fid, 'pid'=>$form->pid])}}">Association Permissions</a>
        </li>

        <li class="content-link content-link-js">
          <a href="{{ action('ExportController@exportForm',['fid'=>$form->fid, 'pid' => $form->pid]) }}">Export Form</a>
        </li>
      @endif

      {{--<li class="content-link content-link-js" data-page="metadata">--}}
        {{--<a href="{{ action('MetadataController@index', ['fid'=>$form->fid, 'pid'=>$form->pid]) }}">Link Open Data</a>--}}
      {{--</li>--}}
  </ul>
</div>
