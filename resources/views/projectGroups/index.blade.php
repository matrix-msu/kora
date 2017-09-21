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
  <section class="new-object-button center">
    <form action="{{ action('ProjectController@create') }}">
      @if(\Auth::user()->admin)
        <input type="submit" value="Create a New Permissions Group">
      @endif
    </form>
  </section>

  <section class="permission-group-selection center permission-group-js permission-group-selection">
    @foreach($projectGroups as $index=>$projectGroup)
      @if($project->adminGID == $projectGroup->id)
        <div class="permission-group card {{ $index == 0 ? 'active' : '' }}" id="{{$projectGroup->id}}">
          <div class="header {{ $index == 0 ? 'active' : '' }}">
            <div class="left pl-m">
              <i class="icon icon-star"></i>

              <a class="title inactive" href="#">
                <span class="name">{{ str_replace($project->name." ", "", $projectGroup->name) }}</span>
              </a>
            </div>

            <div class="card-toggle-wrap">
              <a href="#" class="card-toggle project-toggle-js">
                <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
              </a>
            </div>
          </div>

          <div class="content content-js {{ $index == 0 ? 'active' : '' }}">
            <div class="id">
              <span class="attribute">Unique Project ID: </span>
              <span>{{$project->slug}}</span>
            </div>

            <div class="description">
              {{$project->description}}
            </div>

            <div class="admins">
              <spa`n class="attribute">Project Admins: </span>
              @foreach($project->adminGroup()->get() as $adminGroup)
                <span>
                  {{
                    implode(
                      array_map(
                        create_function('$u', 'return $u->getFullNameAttribute();'),
                        $adminGroup->users()->get()->all()
                      ),
                      ", "
                    )
                  }}
                </span>
              @endforeach
            </div>


              <div class="footer">
                <a class="quick-action edit-group-name-js underline-middle-hover left" href="#">
                  <i class="icon icon-trash"></i>
                </a>

                <a class="quick-action edit-group-name-js underline-middle-hover" href="#">
                  <i class="icon icon-edit-little"></i>
                  <span>Edit Group Name</span>
                </a>
              </div>
            @else
          </div>
        </div>
      @endif
    @endforeach
  </section>
@stop

@section('footer')

@stop

@section('javascripts')
  @include('partials.projectGroups.javascripts')

  <script type="text/javascript">
    var CSRFToken = '{{ csrf_token() }}';
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
                          <li class="list-group-item" id="list-element{{$projectGroup->id}}{{$user->id}}" name="{{$user->username}}">
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

              @include('partials.newProjectGroup')

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

        function changeGroupName(textBox){
            textBox.attr('style','');
            newName = textBox.val();
            gid = textBox.attr('gid');
            pid = {{$project->pid}};

            editDiv = textBox.parent();
            mainDiv = editDiv.siblings(".projectGroupName");

            if(newName==''){
                textBox.attr('style','border:3px solid red');
                return;
            }else {
                $.ajax({
                    url: '{{action('ProjectGroupController@updateName')}}',
                    type: 'PATCH',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "gid": gid,
                        "name": newName,
                        "pid" : pid
                    },
                    success: function (response) {
                        divText = newName+" <a class='nameEdit'>[EDIT]</a>";
                        mainDiv.html(divText);

                        textBox.val('');
                        textBox.attr('placeholder',newName);

                        editDiv.slideUp();
                        mainDiv.slideDown();
                    }
                });
            }
        }

        /**
         * The Ajax to remove a user from a particular project's project group.
         *
         * @param projectGroup {int} The project group id.
         * @param userId {int} The user id.
         * @param pid {int} The project id.
         */
        function removeUser(projectGroup, userId, pid){
            var username = $("#list-element"+projectGroup+userId).attr('name');

            $.ajax({
                url: '{{action('ProjectGroupController@removeUser')}}',
                type: 'PATCH',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "userId": userId,
                    "projectGroup": projectGroup,
                    "pid" : pid
                },
                success: function(){
                    var selector = $("#dropdown"+projectGroup);
                    //
                    // Remove the user from the list of users currently in the group.
                    // Then add the user to the users that can be added to the group.
                    //
                    selector.attr('selected', '0');

                    $("#list-element"+projectGroup+userId).remove();
                    selector.append('<option id="'+userId+'">'+username+'</option>');
                }
            });
        }

        /**
         * The Ajax to add a user to a particular project's project group.
         *
         * @param projectGroup {int} The project group id.
         * @param pid {int} The project id.
         */
        function addUser(projectGroup, pid){
            var selector = $("#dropdown"+projectGroup+" option:selected");

            var userId = selector.attr('id');
            var username = selector.text();

            $.ajax({
                url: '{{action('ProjectGroupController@addUser')}}',
                type: 'PATCH',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "userId": userId,
                    "projectGroup": projectGroup
                },
                success: function(data){
                    if(data!=''){
                        $('#list'+data).children().each(function(){
                            //remove from list
                            if($(this).attr('name')==username){
                                $(this).remove();
                            }
                        });

                        $('#dropdown'+data).append("<option id='"+userId+"'>"+username+"</option>");
                    }

                    //
                    // Add the user to the users currently in the group.
                    // Then remove the user from the list that can be added to the group.
                    //
                    $("#list"+projectGroup).append('<li class="list-group-item" id="list-element'+projectGroup+userId+'" name="'+username+'">'
                                                    +username+' <a href="javascript:void(0)" onclick="removeUser('+projectGroup+', '+userId+', '+pid+')">[X]</a></li>');
                    $("#dropdown"+projectGroup+" option[id='"+userId+"']").remove();
                }
            });
        }

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
                    url: '{{action('ProjectGroupController@deleteProjectGroup')}}',
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

        /**
         * Update the permissions of a particular project group.
         *
         * @param projectGroup {int} The project
         */
        function updatePermissions(projectGroup){
            var permCreate, permEdit, permDelete;

            // If the box is checked, allow users in the project group to create forms within the project.
            if ($("#create"+projectGroup).is(':checked'))
                permCreate = 1;
            else
                permCreate = 0;

            // Allow users to edit forms.
            if ($("#edit"+projectGroup).is(':checked'))
                permEdit = 1;
            else
                permEdit = 0;

            // Allow users to delete forms.
            if ($("#delete"+projectGroup).is(':checked'))
                permDelete = 1;
            else
                permDelete = 0;

            $.ajax({
                url: '{{action('ProjectGroupController@updatePermissions')}}',
                type: 'PATCH',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "projectGroup": projectGroup,
                    "permCreate": permCreate,
                    "permEdit": permEdit,
                    "permDelete": permDelete
                }
            });
        }

        $('#users').select2();
    </script>
@stop
