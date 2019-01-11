<?php $form = \App\Http\Controllers\FormController::getForm($form->id) ?>
@include('partials.sideMenu.project', ['pid' => $form->project_id, 'openDrawer' => false])
<div class="drawer-element drawer-element-js">
  <a href="#" class="drawer-toggle drawer-toggle-js" data-drawer="{{ $openDrawer or '0' }}">
    <i class="icon icon-form"></i>
    <span>{{ $form->name }}</span>
    <i class="icon icon-chevron"></i>
  </a>

  <ul class="drawer-content drawer-content-js">
      <li class="content-link content-link-js" data-page="form-show">
        <a href="{{ url('/projects/'.$form->project_id).'/forms/'.$form->id}}">
          <span>Form Home</span>
        </a>
      </li>

      <li class="content-link content-link-js" data-page="record-index">
        <a  href="{{ url('/projects/'.$form->project_id).'/forms/'.$form->id.'/records'}}">Form Records & Search</a>
      </li>

      <li class="content-link content-link-js" data-page="advanced-index">
          <a  href="{{ url('/projects/'.$form->project_id).'/forms/'.$form->id.'/advancedSearch'}}">Form Records Advanced Search</a>
      </li>

      {{--TODO::CASTLE--}}
      {{--@if(\Auth::user()->canCreateFields($form))--}}
          <?php
            //$lastPage = \App\Page::where('fid','=',$form->id)->orderBy('sequence','desc')->first();
          ?>
          {{--<li class="content-link content-link-js" data-page="field-create">--}}
              {{--<a href="{{action('FieldController@create', ['pid'=>$form->project_id, 'fid' => $form->id, 'rootPage' =>$lastPage->id])}}">Create New Field</a>--}}
          {{--</li>--}}
      {{--@endif--}}

      <?php
      //$fieldsInForm = \App\Field::where('fid', '=', $form->id)->get()->all();
      //$cnt = sizeof($fieldsInForm);
      ?>

      {{--@if(\Auth::user()->canIngestRecords(\App\Http\Controllers\FormController::getForm($form->id)))--}}
        {{--<li class="content-link content-link-js--}}
        {{--@if($cnt == 0)--}}
            {{--pre-spacer--}}
        {{--@endif--}}
        {{--" data-page="record-create">--}}
          {{--<a href="{{ action('RecordController@create',['pid' => $form->project_id, 'fid' => $form->id]) }}">Create New Record</a>--}}
        {{--</li>--}}
      {{--@endif--}}

      {{--@if($cnt > 0)--}}
          {{--<li class="content-link pre-spacer content-link-js" id="form-submenu">--}}
              {{--<a href='#' class="drawer-sub-menu-toggle drawer-sub-menu-toggle-js" data-toggle="dropdown">--}}
                  {{--<span>Jump to Field</span>--}}
                  {{--<i class="icon icon-plus"></i>--}}
              {{--</a>--}}
			  {{----}}
			  {{--<?php--}}
			  {{--// Sort forms by name--}}
			  {{--$name_flid_fields = [];--}}
			  {{--$fids = [];--}}
			  {{----}}
			  {{--foreach ($fieldsInForm as $field)--}}
			  {{--{--}}
				{{--$name_flid_fields[$field->flid] = $field->name;--}}
				{{--$fids[$field->flid] = $field->fid;--}}
			  {{--}--}}
			  {{----}}
			  {{--asort($name_flid_fields, SORT_NATURAL | SORT_FLAG_CASE);--}}
			  {{--?>--}}

              {{--<ul class="drawer-deep-menu drawer-deep-menu-js">--}}
                  {{--@foreach($name_flid_fields as $field_flid => $field_name)--}}
                      {{--<li class="drawer-deep-menu-link">--}}
                          {{--<a class="padding-fix" href="{{ url('/projects/'.$form->project_id).'/forms/'.$fids[$field_flid] .'/fields/'.$field_flid.'/options'}}">{{ $field_name }}</a>--}}
                      {{--</li>--}}
                  {{--@endforeach--}}
              {{--</ul>--}}
          {{--</li>--}}
      {{--@endif--}}

      @if(\Auth::user()->canIngestRecords(\App\Http\Controllers\FormController::getForm($form->id)))
        <li class="spacer"></li>
        <li class="content-link content-link-js" data-page="record-import-setup">
          <a href="{{ action('RecordController@importRecordsView',['pid' => $form->project_id, 'fid' => $form->id]) }}">Import Records</a>
        </li>
      @endif

      <li class="content-link content-link-js" data-page="batch-assign">
        <a href="{{ action('RecordController@showMassAssignmentView',['pid' => $form->project_id, 'fid' => $form->id]) }}">Batch Assign Field Values</a>
      </li>

      @if (\Auth::user()->admin || \Auth::user()->isFormAdmin(\App\Http\Controllers\FormController::getForm($form->id)))
        <li class="content-link content-link-js" data-page="record-revisions">
          <a href="{{action('RevisionController@index', ['pid'=>$form->project_id, 'fid'=>$form->id])}}">Manage Record Revisions</a>
        </li>

        <li class="content-link content-link-js" data-page="record-preset">
          <a href="{{action('RecordPresetController@index', ['pid'=>$form->project_id, 'fid'=>$form->id])}}">Manage Record Presets</a>
        </li>

        <li class="export-record-open content-link content-link-js">
            <a href="#">Export All Records</a>
        </li>

        <li class="content-link content-link-js" data-page="form-edit">
          <a href="{{ action('FormController@edit', ['pid'=>$form->project_id, 'fid'=>$form->id]) }}">Edit Form Information</a>
        </li>

        <li class="content-link content-link-js" data-page="form-permissions">
          <a href="{{ action('FormGroupController@index', ['pid'=>$form->project_id, 'fid'=>$form->id]) }}">Form Permissions</a>
        </li>

        <li class="content-link content-link-js" data-page="form-association-permissions">
          <a href="{{action('AssociationController@index', ['fid'=>$form->id, 'pid'=>$form->project_id])}}">Association Permissions</a>
        </li>

        <li class="content-link content-link-js">
          <a href="{{ action('ExportController@exportForm',['fid'=>$form->id, 'pid' => $form->project_id]) }}">Export Form</a>
        </li>
      @endif

      {{--<li class="content-link content-link-js" data-page="metadata">--}}
        {{--<a href="{{ action('MetadataController@index', ['fid'=>$form->id, 'pid'=>$form->project_id]) }}">Link Open Data</a>--}}
      {{--</li>--}}
  </ul>
</div>
