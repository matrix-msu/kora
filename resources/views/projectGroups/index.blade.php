@extends('app', ['page_title' => "Permissions - {$project->name}", 'page_class' => 'project-permissions'])

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
          <i class="icon icon-project-permissions"></i>
          <span>Project Permissions</span>
        </h1>
        <p class="description">Select a permission group below or create a new permission group to get started.</p>
      </div>
  </section>
@stop

@section('body')
  @include("partials.projectGroups.editNameModal")
  @include("partials.projectGroups.newPermissionModal")
  @include("partials.projectGroups.deletePermissionModal")
  @include("partials.projectGroups.addUsersModal")
  @include("partials.projectGroups.removeUserModal")
  @include("partials.projectGroups.viewUserModal")

  <section class="new-object-button center">
    <form action="#">
      @if(\Auth::user()->admin)
        <input class="new-permission-js" type="submit" value="Create a New Permissions Group">
      @endif
    </form>
  </section>

  <section class="permission-group-selection center permission-group-js permission-group-selection">
    @foreach($projectGroups as $index=>$projectGroup)
      <?php
        $specialGroup = ($project->adminGID == $projectGroup->id) ||
          ($projectGroup->name == $project->name . " Default Group")
      ?>

      <div class="group group-js card {{ $index == 0 ? 'active' : '' }}" id="{{$projectGroup->id}}">
        <div class="header {{ $index == 0 ? 'active' : '' }}">
          <div class="left pl-m">
            @if ($project->adminGID == $projectGroup->id)
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
              <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
            </a>
          </div>
        </div>

        <div class="content content-js {{ $index == 0 ? 'active' : '' }}">
          <div class="allowed-actions">
            <div class="form-group action">
              <div class="check-box-half check-box-rectangle">
                <input type="checkbox"
                      @if ($project->adminGID == $projectGroup->id)
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
                      @if ($project->adminGID == $projectGroup->id)
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
                      @if ($project->adminGID == $projectGroup->id)
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
            <?php
              $users = $projectGroup->users()->get();
            ?>
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

            @foreach($users as $user)
              <div class="user user-js" id="list-element{{$projectGroup->id}}{{$user->id}}">
                <a href="#" class="name view-user-js">{{ $user->first_name }} {{ $user->last_name }}</a>

                @if (\Auth::user()->id != $user->id)
                  <a href="#" class="cancel remove-user-js" data-value="[{{$projectGroup->id}}, {{$user->id}}, {{$project->pid}}]">
                    <i class="icon icon-cancel"></i>
                  </a>
                @endif
              </div>
            @endforeach
            @include("partials.projectGroups.addUsersBody")
          </div>

            <div class="footer">
              @if (!$specialGroup)
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
    var pid = '{{$project->pid}}';
    var removeUserPath = '{{ action('ProjectGroupController@removeUser', ["pid" => $project->pid]) }}';
    var addUsersPath = '{{ action('ProjectGroupController@addUsers', ["pid" => $project->pid]) }}';
    var editNamePath = '{{ action('ProjectGroupController@updateName', ["pid" => $project->pid]) }}';
    var updatePermissionsPath = '{{ action('ProjectGroupController@updatePermissions', ["pid" => $project->pid]) }}';
    var deletePermissionsPath = '{{ action('ProjectGroupController@deleteProjectGroup', ["pid" => $project->pid]) }}';
    Kora.ProjectGroups.Index();
  </script>
@stop


@section('content')
  <div class="container">
    <div class="row">
      <div class="col-md-10 col-md-offset-1">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3>{{trans('projectGroups_index.manage')}}</h3>
          </div>

          <div class="panel-body">
            @foreach($projectGroups as $projectGroup)
              @if($project->adminGID == $projectGroup->id)
                <div class="panel panel-default">
                  <div class="collapseTest" style="display: none">
                    <div class="panel-body">
                      <span>{{trans('projectGroups_index.users')}}:</span>
                      <ul class="list-group" id="list{{$projectGroup->id}}">
                        @foreach($projectGroup->users()->get() as $user)
                          <li class="list-group-item" name="{{$user->username}}">
                            {{$user->username}}
                            @if(\Auth::user()->id != $user->id)
                              <a href="javascript:void(0)" onclick="removeUser({{$projectGroup->id}}, {{$user->id}}, {{$project->pid}})">[X]</a>
                            @endif
                          </li>
                        @endforeach
                      </ul>

                      <select onchange="addUser({{$projectGroup->id}}, {{$project->pid}})" id="dropdown{{$projectGroup->id}}">
                        <option selected value="0">{{trans('projectGroups_index.add')}}</option>
                        @foreach($all_users as $user)
                          @if($projectGroup->hasUser($user))
                          @else
                            @if(\Auth::user()->id != $user->id)
                              <option id="{{$user->id}}">{{$user->username}}</option>
                            @endif
                          @endif
                        @endforeach
                      </select>
                      <hr/>
                      <div id="checkboxes">
                        <span>{{trans('projectGroups_index.permissions')}}:</span>
                        <ul class="list-group" id="perm-list{{$projectGroup->id}}">
                          <li class="list-group-item">{{trans('projectGroups_index.create')}}: <input type="checkbox" id="create" checked disabled></li>
                          <li class="list-group-item">{{trans('projectGroups_index.edit')}}: <input type="checkbox" id="edit" checked disabled></li>
                          <li class="list-group-item">{{trans('projectGroups_index.delete')}}: <input type="checkbox" id="delete" checked disabled></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              @endif
            @endforeach

                  @foreach($projectGroups as $projectGroup)
                      @if($project->adminGID != $projectGroup->id)
                          <div class="panel panel-default">
                              <div class="panel-heading">
                                  @if($projectGroup->name == $project->name." Default Group")
                                      {{$projectGroup->name}}
                                  @else
                                  <div class="projectGroupName">
                                      {{$projectGroup->name}} <a class="nameEdit">[EDIT]</a>
                                  </div>
                                  <div class="projectGroupEdit" style="display:none">
                                      <input type="text" class="newGroupName" placeholder="{{$projectGroup->name}}" gid="{{$projectGroup->id}}">
                                      <a class="nameSave">[SAVE]</a> <a class="nameRevert">[X]</a>
                                  </div>
                                  @endif
                              </div>

                              <div class="collapseTest" style="display: none">
                                  <div class="panel-body">
                                      <span>{{trans('projectGroups_index.users')}}:</span>
                                      <ul class="list-group" id="list{{$projectGroup->id}}">
                                          @foreach($projectGroup->users()->get() as $user)
                                              <li class="list-group-item" id="list-element{{$projectGroup->id}}{{$user->id}}" name="{{$user->username}}">
                                                  {{$user->username}} <a href="javascript:void(0)" onclick="removeUser({{$projectGroup->id}}, {{$user->id}}, {{$project->pid}})">[X]</a>
                                              </li>
                                          @endforeach
                                      </ul>

                                      <select onchange="addUser({{$projectGroup->id}}, {{$project->pid}})" id="dropdown{{$projectGroup->id}}">
                                          <option selected value="0">{{trans('projectGroups_index.add')}}</option>
                                          @foreach($all_users as $user)
                                              @if($projectGroup->hasUser($user))
                                              @else
                                                  @if(\Auth::user()->id != $user->id)
                                                      <option id="{{$user->id}}">{{$user->username}}</option>
                                                  @endif
                                              @endif
                                          @endforeach
                                      </select>
                                  <hr/>
                                  <div id="checkboxes">
                                      <span>{{trans('projectGroups_index.permissions')}}:</span>
                                      <ul class="list-group" id="perm-list{{$projectGroup->id}}">
                                          <li class="list-group-item">{{trans('projectGroups_index.create')}}:
                                              <input type="checkbox" id="create{{$projectGroup->id}}" @if($projectGroup->create) checked="checked" @endif onclick="updatePermissions({{$projectGroup->id}})">
                                          </li>
                                          <li class="list-group-item">{{trans('projectGroups_index.edit')}}:
                                              <input type="checkbox" id="edit{{$projectGroup->id}}" @if($projectGroup->edit) checked="checked" @endif onclick="updatePermissions({{$projectGroup->id}})">
                                          </li>
                                          <li class="list-group-item">{{trans('projectGroups_index.delete')}}:
                                              <input type="checkbox" id="delete{{$projectGroup->id}}" @if($projectGroup->delete) checked="checked" @endif onclick="updatePermissions({{$projectGroup->id}})">
                                          </li>
                                      </ul>
                                  </div>
                              </div>
                                  @if($projectGroup->name != $project->name." Default Group")
                                  <div class="panel-footer">
                                      <a href="javascript:void(0)" onclick="deleteProjectGroup({{$projectGroup->id}})">[{{trans('projectGroups_index.deleteproj')}}]</a>
                                  </div>
                                  @endif
                              </div>
                          </div>
                      @endif
                  @endforeach

              <hr/>

              <h3>{{trans('projectGroups_index.createproj')}}</h3>
          </div>
        </div>
      </div>
    </div>
  </div>
@stop

@section('footer')
    <script>

        /**
         * The collapsing display jQuery.
         */
        $(".panel-heading").on("click", function(e){
            if($(e.target).is(".nameEdit")) return;
            if($(e.target).is(".nameSave")) return;
            if($(e.target).is(".nameRevert")) return;
            if($(e.target).is(".newGroupName")) return;

            if ($(this).siblings('.collapseTest').css('display') == 'none') {
                $(this).siblings('.collapseTest').slideDown();
            } else {
                $(this).siblings('.collapseTest').slideUp();
            }
        });

        $(".panel-heading").on("click", ".nameEdit", function(){
            editButton = $(this);

            mainDiv = editButton.parent();
            editDiv = mainDiv.siblings(".projectGroupEdit");

            mainDiv.slideUp();
            editDiv.slideDown();

            textBox = editDiv.children('.newGroupName');
            textBox.focus();
        });

        $(".panel-heading").on("click", ".nameSave", function() {
            saveBtn = $(this);
            textBox = saveBtn.siblings(".newGroupName");

            changeGroupName(textBox);
        });

        $(".panel-heading").on("click", ".nameRevert", function() {
            revertBtn = $(this);

            editDiv = revertBtn.parent();
            mainDiv = editDiv.siblings(".projectGroupName");

            mainDiv.slideDown();
            editDiv.slideUp();
        });

        $('.newGroupName').keypress(function (e) {
            textBox = $(this);

            if(e.which == 13)  // the enter key code
            {
                changeGroupName(textBox);
            }else if(e.keyCode==27){
                editDiv = textBox.parent();
                mainDiv = editDiv.siblings(".projectGroupName");

                editDiv.slideUp();
                mainDiv.slideDown();
            }
        });

        /**
         * The Ajax to delete a project group.
         *
         * @param projectGroup {int} The project group id.
         */
        function deleteProjectGroup(projectGroup){
            var encode = $('<div/>').html('{{trans('projectGroups_index.areyousure')}}').text();
            var response = confirm(encode + "?");
            if (response) {
                $.ajax({
                    url: '{{action('ProjectGroupController@deleteProjectGroup', ["pid" => $project->pid])}}',
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "projectGroup": projectGroup
                    },
                    success: function() {
                        location.reload();
                    }
                });
            }
        }

        $('#users').select2();
    </script>
@stop
