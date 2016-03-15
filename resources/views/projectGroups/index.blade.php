@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->pid])
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
                                        <div class="panel-heading">{{$projectGroup->name}}</div>

                                        <div class="collapseTest" style="display: none">
                                            <div class="panel-body">
                                                <span>{{trans('projectGroups_index.users')}}:</span>
                                                    <ul class="list-group" id="list{{$projectGroup->id}}">
                                                @foreach($projectGroup->users()->get() as $user)
                                                        <li class="list-group-item" id="list-element{{$projectGroup->id}}{{$user->id}}" name="{{$user->name}}">
                                                            {{$user->username}} @if(\Auth::user()->id != $user->id)
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
                                        <div class="panel-heading">{{$projectGroup->name}}</div>

                                        <div class="collapseTest" style="display: none">
                                            <div class="panel-body">
                                                <span>{{trans('projectGroups_index.users')}}:</span>
                                                <ul class="list-group" id="list{{$projectGroup->id}}">
                                                    @foreach($projectGroup->users()->get() as $user)
                                                        <li class="list-group-item" id="list-element{{$projectGroup->id}}{{$user->id}}" name="{{$user->name}}">
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
                                            <div class="panel-footer">
                                                <a href="javascript:void(0)" onclick="deleteProjectGroup({{$projectGroup->id}})">[{{trans('projectGroups_index.deleteproj')}}]</a>
                                            </div>
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
        $(".panel-heading").on("click", function(){
            if ($(this).siblings('.collapseTest').css('display') == 'none') {
                $(this).siblings('.collapseTest').slideDown();
            } else {
                $(this).siblings('.collapseTest').slideUp();
            }
        });

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
                success: function(){
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
            var response = confirm("{{trans('projectGroups_index.areyousure')}}?");
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