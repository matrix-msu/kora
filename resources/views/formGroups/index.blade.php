@extends('app', ['page_title' => "{$form->name} Permissions", 'page_class' => 'form-permissions'])

@section('leftNavLinks')
  @include('partials.menu.project', ['pid' => $form->pid])
  @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
  @include('partials.menu.static', ['name' => 'Form Permissions'])
@stop

@section('header')
  <section class="head">
    <div class="inner-wrap center">
      <h1 class="title">
        <i class="icon icon-form-permissions"></i>
        <span>Form Permissions</span>
      </h1>
      <p class="description">Select a permission group below or create a new permission group to get started.</p>
    </div>
  </section>
@stop

@section('body')
  @include("partials.formGroups.editNameModal")
  @include("partials.formGroups.newPermissionModal")
  @include("partials.formGroups.deletePermissionModal")
  @include("partials.formGroups.addUsersModal")
  @include("partials.formGroups.removeUserModal")
  @include("partials.formGroups.viewUserModal")

  <section class="new-object-button center">
    @if(\Auth::user()->isProjectAdmin($project))
      <form action="#">
        <input class="new-permission-js" type="submit" value="Create a New Permissions Group">
      </form>
    @endif
  </section>

  <section class="permission-group-selection center permission-group-js permission-group-selection">
    @foreach($formGroups as $index=>$formGroup)
      <div class="group group-js card {{ $index == 0 ? 'active' : '' }}" id="{{$formGroup->id}}">
        <div class="header {{ $index == 0 ? 'active' : '' }}">
          <div class="left pl-m">
            @if ($form->adminGID == $formGroup->id)
              <i class="icon icon-star pr-xs"></i>
            @elseif ($formGroup->name == $form->name." Default Group")
              <i class="icon icon-shield pr-xs"></i>
            @endif

            <a class="title permission-toggle-by-name-js" href="#">
              <span class="name name-js">{{ str_replace($form->name." ", "", $formGroup->name) }}</span>
            </a>
          </div>

          <div class="card-toggle-wrap">
            <a href="#" class="card-toggle permission-toggle-js">
              <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
            </a>
          </div>
        </div>

        <div class="content content-js {{ $index == 0 ? 'active' : '' }}">
          <div class="allowed-actions">
            <div class="form-group action">
              <div class="check-box-half check-box-rectangle">
                <input type="checkbox"
                  @if ($form->adminGID == $formGroup->id)
                    checked disabled
                  @elseif ($formGroup->create)
                    checked
                  @endif
                  value="1"
                  class="check-box-input preset-input-js"
                  onclick="Kora.FormGroups.Index.updatePermissions({{$formGroup->id}})"
                  id="create-{{$formGroup->id}}"
                  name="create" />
                <span class="check"></span>
                <span class="placeholder">Can Create Fields</span>
              </div>
            </div>

            <div class="form-group action">
              <div class="check-box-half check-box-rectangle">
                <input type="checkbox"
                  @if ($form->adminGID == $formGroup->id)
                    checked disabled
                  @elseif ($formGroup->edit)
                    checked
                  @endif
                  value="1"
                  class="check-box-input preset-input-js"
                  onclick="Kora.FormGroups.Index.updatePermissions({{$formGroup->id}})"
                  id="edit-{{$formGroup->id}}"
                  name="edit" />
                <span class="check"></span>
                <span class="placeholder">Can Edit Fields</span>
              </div>
            </div>

            <div class="form-group action">
              <div class="check-box-half check-box-rectangle">
                <input type="checkbox"
                  @if ($form->adminGID == $formGroup->id)
                    checked disabled
                  @elseif ($formGroup->delete)
                    checked
                  @endif
                  value="1"
                  class="check-box-input preset-input-js"
                  onclick="Kora.FormGroups.Index.updatePermissions({{$formGroup->id}})"
                  id="delete-{{$formGroup->id}}"
                  name="delete" />
                <span class="check"></span>
                <span class="placeholder">Can Delete Fields</span>
              </div>
            </div>
          </div>

          <div class="allowed-actions">
            <div class="form-group action">
              <div class="check-box-half check-box-rectangle">
                <input type="checkbox"
                  @if ($form->adminGID == $formGroup->id)
                    checked disabled
                  @elseif ($formGroup->ingest)
                    checked
                  @endif
                  value="1"
                  class="check-box-input preset-input-js"
                  onclick="Kora.FormGroups.Index.updatePermissions({{$formGroup->id}})"
                  id="ingest-{{$formGroup->id}}"
                  name="ingest" />
                <span class="check"></span>
                <span class="placeholder">Can Create Records</span>
              </div>
            </div>

            <div class="form-group action">
              <div class="check-box-half check-box-rectangle">
                <input type="checkbox"
                  @if ($form->adminGID == $formGroup->id)
                    checked disabled
                  @elseif ($formGroup->modify)
                    checked
                  @endif
                  value="1"
                  class="check-box-input preset-input-js"
                  onclick="Kora.FormGroups.Index.updatePermissions({{$formGroup->id}})"
                  id="modify-{{$formGroup->id}}"
                  name="modify" />
                <span class="check"></span>
                <span class="placeholder">Can Edit Records</span>
              </div>
            </div>

            <div class="form-group action">
              <div class="check-box-half check-box-rectangle">
                <input type="checkbox"
                  @if ($form->adminGID == $formGroup->id)
                    checked disabled
                  @elseif ($formGroup->destroy)
                    checked
                  @endif
                  value="1"
                  class="check-box-input preset-input-js"
                  onclick="Kora.FormGroups.Index.updatePermissions({{$formGroup->id}})"
                  id="destroy-{{$formGroup->id}}"
                  name="destroy" />
                <span class="check"></span>
                <span class="placeholder">Can Delete Records</span>
              </div>
            </div>
          </div>

        </div>
      </div>
    @endforeach
  </section>
@stop

@section('javascripts')
  @include('partials.formGroups.javascripts')

  <script type="text/javascript">
    var CSRFToken = '{{ csrf_token() }}';
    var pid = '{{$project->pid}}';
    var removeUserPath = '{{ action('FormGroupController@removeUser') }}';
    var addUsersPath = '{{ action('FormGroupController@addUser') }}';
    var editNamePath = '{{ action('FormGroupController@updateName', ["pid" => $project->pid, "fid" => $form->fid]) }}';
    var updatePermissionsPath = '{{ action('FormGroupController@updatePermissions', ["pid" => $project->pid, "fid" => $form->fid]) }}';
    var deletePermissionsPath = '{{ action('FormGroupController@deleteFormGroup', ["pid" => $project->pid, "fid" => $form->fid]) }}';
    Kora.FormGroups.Index();
  </script>
@stop
