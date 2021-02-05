@extends('app', ['page_title' => "Permissions - {$project->name}", 'page_class' => 'project-permissions'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->id])
    @include('partials.menu.static', ['name' => 'Project Permissions'])
@stop

@section('aside-content')
  @include('partials.sideMenu.project', ['pid' => $project->id, 'openDrawer' => true])
@stop

@section('stylesheets')
  <!-- No Additional Stylesheets Necessary -->
@stop

@section('header')
  <section class="head">
      <a class="back" href=""><i class="icon icon-chevron"></i></a>
      <div class="inner-wrap center">
        <h1 class="title">
          <i class="icon icon-project-permissions"></i>
          <span>Project Permissions</span>
        </h1>
        <p class="description">Select a permission group below or create a new permission group to get started.</p>
      </div>
  </section>
@stop

@section('body')
  @include("partials.projects.notification")
  @include("partials.projectGroups.editNameModal")
  @include("partials.projectGroups.newPermissionModal")
  @include("partials.projectGroups.deletePermissionModal")
  @include("partials.projectGroups.addUsersModal")
  @include("partials.projectGroups.removeUserModal")
  @include("partials.user.profileModal")

  <section class="new-object-button center">
    <form action="#">
      @if(\Auth::user()->admin)
        <input class="new-permission-js" type="submit" value="Create a New Permissions Group">
      @endif
    </form>
  </section>

  <section class="permission-group-selection center permission-group-js permission-group-selection">
    @foreach($projectGroups as $index=>$projectGroup)
      @php
          $specialGroup = ($project->adminGroup_id == $projectGroup->id) ||
            ($projectGroup->name == $project->name . " Default Group")
      @endphp

      <div class="group group-js card {{ $index == $active || $projectGroup->id == $active ? 'active' : '' }}" id="{{$projectGroup->id}}">
        <div class="header {{ $index == $active || $projectGroup->id == $active ? 'active' : '' }}">
          <div class="left pl-m">
            @if ($project->adminGroup_id == $projectGroup->id)
              <i class="icon icon-star pr-xs"></i>
            @elseif ($projectGroup->name == $project->name." Default Group")
              <i class="icon icon-shield pr-xs"></i>
            @endif

            <a class="title permission-toggle-by-name-js" href="#">
              <span class="name name-js">{{ str_replace($project->name." ", "", $projectGroup->name) }}</span>
            </a>
          </div>

          <div class="card-toggle-wrap">
            <a href="#" class="card-toggle permission-toggle-js">
              <i class="icon icon-chevron {{ $index == $active || $projectGroup->id == $active ? 'active' : '' }}"></i>
            </a>
          </div>
        </div>

        <div class="content content-js {{ $index == $active || $projectGroup->id == $active ? 'active' : '' }}">
          <div class="allowed-actions">
            <div class="form-group action">
              <div class="check-box-half check-box-rectangle">
                <input type="checkbox"
                      @if ($project->adminGroup_id == $projectGroup->id)
                       checked disabled
                      @elseif ($projectGroup->create)
                        checked
                      @endif
                       value="1"
                       class="check-box-input preset-input-js"
                       onclick="Kora.ProjectGroups.Index.updatePermissions({{$projectGroup->id}})"
                       id="create-{{$projectGroup->id}}"
                       name="create" />
                <span class="check"></span>
                <span class="placeholder">Can Create Forms</span>
              </div>
            </div>

            <div class="form-group action">
              <div class="check-box-half check-box-rectangle">
                <input type="checkbox"
                      @if ($project->adminGroup_id == $projectGroup->id)
                       checked disabled
                      @elseif ($projectGroup->edit)
                        checked
                      @endif
                       value="1"
                       class="check-box-input preset-input-js"
                       onclick="Kora.ProjectGroups.Index.updatePermissions({{$projectGroup->id}})"
                       id="edit-{{$projectGroup->id}}"
                       name="edit" />
                <span class="check"></span>
                <span class="placeholder">Can Edit Forms</span>
              </div>
            </div>

            <div class="form-group action">
              <div class="check-box-half check-box-rectangle">
                <input type="checkbox"
                      @if ($project->adminGroup_id == $projectGroup->id)
                       checked disabled
                      @elseif ($projectGroup->delete)
                        checked
                      @endif
                       value="1"
                       class="check-box-input preset-input-js"
                       onclick="Kora.ProjectGroups.Index.updatePermissions({{$projectGroup->id}})"
                       id="delete-{{$projectGroup->id}}"
                       name="delete" />
                <span class="check"></span>
                <span class="placeholder">Can Delete Forms</span>
              </div>
            </div>
          </div>

          <div class="users users-js" data-group="{{$projectGroup->id}}">
            @php
              $users = $projectGroup->users()->get();
            @endphp
            @if (sizeof($users) == 0)
              <p class="no-users no-users-js">
                <span>No users in this group, select</span>
                <a href="#" class="user-add add-users-js underline-middle-hover"
                  data-select="add_user_select{{$projectGroup->id}}"
                  data-group="{{$projectGroup->id}}" >
                  <i class="icon icon-user-add"></i>
                  <span>Add User(s) to Group</span>
                </a>
                <span>to add some!</span>
              </p>
            @endif

            @php
                $uniqueNames = [];
                $nonUniqueNames = [];
                foreach($users as $user) {
                    if(!in_array($user->getFullName(),$uniqueNames))
                        $uniqueNames[] = $user->getFullName();
                    else
                        $nonUniqueNames[] = $user->getFullName();
                }
            @endphp
            @foreach($users as $user)
              <div class="user user-js" id="list-element{{$projectGroup->id}}{{$user->id}}">
                <a href="#" class="name view-user-js"
                   data-name="{{$user->getFullName()}}"
                   data-username="{{$user->username}}"
                   data-email="{{$user->email}}"
                   data-organization="{{$user->preferences['organization']}}"
                   data-profile="{{$user->getProfilePicUrl()}}"
                   data-profile-url="{{action('Auth\UserController@index', ['uid' => $user->id])}}">
                   {{ (in_array($user->getFullName(), $nonUniqueNames)) ? $user->getFullName().' ('.$user->username.')' : $user->getFullName() }}
                </a>

                @if (\Auth::user()->id != $user->id)
                  <a href="#" class="cancel remove-user-js" data-value="[{{$projectGroup->id}}, {{$user->id}}, {{$project->id}}]">
                    <i class="icon icon-cancel"></i>
                  </a>
                @endif
              </div>
            @endforeach
            @include("partials.projectGroups.addUsersBody")
          </div>

            <div class="footer">
              @if(!$specialGroup)
                <a class="quick-action trash-container delete-permission-group-js left" href="#" data-group="{{$projectGroup->id}}">
                  <i class="icon icon-trash"></i>
                </a>
              @endif

                <a href="#" class="quick-action user-add add-users-js underline-middle-hover"
                  data-select="add_user_select{{$projectGroup->id}}"
                  data-group="{{$projectGroup->id}}" >
                  <i class="icon icon-user-add"></i>
                  <span>Add User(s) to Group</span>
                </a>

              @if (!$specialGroup)
                <a class="quick-action edit-group-name-js underline-middle-hover"
                   href="#"
                   data-name="{{ str_replace($project->name." ", "", $projectGroup->name) }}"
                   data-group="{{$projectGroup->id}}" >
                  <i class="icon icon-edit-little"></i>
                  <span>Edit Group Name</span>
                </a>
              @endif
            </div>
        </div>
      </div>
    @endforeach
  </section>
@stop

@section('footer')

@stop

@section('javascripts')
  @include('partials.projectGroups.javascripts')

  <script type="text/javascript">
    var CSRFToken = '{{ csrf_token() }}';
    var pid = '{{$project->id}}';
    var removeUserPath = '{{ action('ProjectGroupController@removeUser', ["pid" => $project->id]) }}';
    var addUsersPath = '{{ action('ProjectGroupController@addUsers', ["pid" => $project->id]) }}';
    var editNamePath = '{{ action('ProjectGroupController@updateName', ["pid" => $project->id]) }}';
    var updatePermissionsPath = '{{ action('ProjectGroupController@updatePermissions', ["pid" => $project->id]) }}';
    var deletePermissionsPath = '{{ action('ProjectGroupController@deleteProjectGroup', ["pid" => $project->id]) }}';
    var validateEmailsUrl = '{{ url('/') }}/admin/users/validateEmails';

    Kora.ProjectGroups.Index();
  </script>
@stop
