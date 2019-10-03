@php
    $menuform = \App\Http\Controllers\FormController::getForm($fid);
@endphp

@include('partials.sideMenu.project', ['pid' => $menuform->project_id, 'openDrawer' => false])
<div class="drawer-element drawer-element-js">
  <a href="#" class="drawer-toggle drawer-toggle-js" data-drawer="{{ $openDrawer ?? '0' }}">
    <i class="icon icon-form"></i>
    <span>{{ $menuform->name }}</span>
    <i class="icon icon-chevron"></i>
  </a>

  <ul class="drawer-content drawer-content-js">
      <li class="content-link content-link-js" data-page="form-show">
        <a href="{{ url('/projects/'.$menuform->project_id).'/forms/'.$menuform->id}}">
          <span>Form Home</span>
        </a>
      </li>

      <li class="content-link content-link-js" data-page="record-index">
        <a  href="{{ url('/projects/'.$menuform->project_id).'/forms/'.$menuform->id.'/records'}}">Form Records & Search</a>
      </li>

      <li class="content-link content-link-js" data-page="advanced-index">
          <a  href="{{ url('/projects/'.$menuform->project_id).'/forms/'.$menuform->id.'/advancedSearch'}}">Form Records Advanced Search</a>
      </li>

      @if(\Auth::user()->canCreateFields($menuform))
          @php
              $lastPage = sizeof($menuform->layout["pages"])-1;
          @endphp
          <li class="content-link content-link-js" data-page="field-create">
              <a href="{{action('FieldController@create', ['pid'=>$pid, 'fid' => $fid, 'rootPage' =>$lastPage])}}">Create New Field</a>
          </li>
      @endif

      @php
          $cnt = sizeof($menuform->layout["fields"]);
      @endphp

      @if(\Auth::user()->canIngestRecords(\App\Http\Controllers\FormController::getForm($menuform->id)))
          <li class="content-link content-link-js
          @if($cnt == 0)
              pre-spacer
          @endif
                  " data-page="record-create">
              <a href="{{ action('RecordController@create',['pid' => $menuform->project_id, 'fid' => $menuform->id]) }}">Create New Record</a>
          </li>
      @endif

      @if($cnt > 0)
          <li class="content-link pre-spacer content-link-js" id="form-submenu">
              <a href='#' class="drawer-sub-menu-toggle drawer-sub-menu-toggle-js" data-toggle="dropdown">
                  <span>Jump to Field</span>
                  <i class="icon icon-plus"></i>
              </a>

              <ul class="drawer-deep-menu drawer-deep-menu-js">
                  @foreach($menuform->layout["pages"] as $page)
                      @foreach($page['flids'] as $flid)
                          @php $fname = $menuform->layout["fields"][$flid]['name'] @endphp
                          <li class="drawer-deep-menu-link">
                              <a class="padding-fix" href="{{ url('/projects/'.$pid).'/forms/'.$fid.'/fields/'.$flid.'/options'}}">{{ $fname }}</a>
                          </li>
                      @endforeach
                  @endforeach
              </ul>
          </li>
      @endif

      @if(\Auth::user()->canIngestRecords(\App\Http\Controllers\FormController::getForm($menuform->id)))
        <li class="spacer"></li>
        <li class="content-link content-link-js" data-page="record-import-setup">
          <a href="{{ action('RecordController@importRecordsView',['pid' => $menuform->project_id, 'fid' => $menuform->id]) }}">Import Records</a>
        </li>
      @endif

      <li class="content-link content-link-js" data-page="batch-assign">
        <a href="{{ action('RecordController@showMassAssignmentView',['pid' => $menuform->project_id, 'fid' => $menuform->id]) }}">Batch Assign Field Values</a>
      </li>

      @if (\Auth::user()->admin || \Auth::user()->isFormAdmin(\App\Http\Controllers\FormController::getForm($menuform->id)))
        <li class="content-link content-link-js" data-page="record-revisions">
          <a href="{{action('RevisionController@index', ['pid'=>$menuform->project_id, 'fid'=>$menuform->id])}}">Manage Record Revisions</a>
        </li>

        <li class="content-link content-link-js" data-page="record-preset">
          <a href="{{action('RecordPresetController@index', ['pid'=>$menuform->project_id, 'fid'=>$menuform->id])}}">Manage Record Presets</a>
        </li>

        <li class="export-record-open content-link content-link-js">
            <a href="#">Export All Records</a>
        </li>

        <li class="content-link content-link-js" data-page="form-edit">
          <a href="{{ action('FormController@edit', ['pid'=>$menuform->project_id, 'fid'=>$menuform->id]) }}">Edit Form Information</a>
        </li>

        <li class="content-link content-link-js" data-page="form-permissions">
          <a href="{{ action('FormGroupController@index', ['pid'=>$menuform->project_id, 'fid'=>$menuform->id]) }}">Form Permissions</a>
        </li>

        <li class="content-link content-link-js" data-page="form-association-permissions">
          <a href="{{action('AssociationController@index', ['fid'=>$menuform->id, 'pid'=>$menuform->project_id])}}">Association Permissions</a>
        </li>

        <li class="content-link content-link-js">
          <a href="{{ action('ExportController@exportForm',['fid'=>$menuform->id, 'pid' => $menuform->project_id]) }}">Export Form</a>
        </li>
      @endif

      {{--<li class="content-link content-link-js" data-page="metadata">--}}
        {{--<a href="{{ action('MetadataController@index', ['fid'=>$menuform->id, 'pid'=>$menuform->project_id]) }}">Link Open Data</a>--}}
      {{--</li>--}}
  </ul>
</div>
