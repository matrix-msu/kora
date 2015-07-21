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
                        <h3>Manage Project Groups</h3>
                    </div>

                    <div class="panel-body">



                            @foreach($projectGroups as $projectGroup)
                                @if($project->adminGID == $projectGroup->id)
                                    <div class="panel panel-default">
                                        <div class="panel-heading">{{$projectGroup->name}}</div>

                                        <div class="collapseTest" style="display: none">
                                            <div class="panel-body">
                                                <span>Users associated with this project group:</span>
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
                                                    <option selected value="0">Add a user</option>
                                                    @foreach($all_users as $user)
                                                        @if($projectGroup->hasUser($user))
                                                        @else
                                                            <option id="{{$user->id}}">{{$user->username}}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <hr/>
                                                <div id="checkboxes">
                                                    <span>Permissions:</span>
                                                    <ul class="list-group" id="perm-list{{$projectGroup->id}}">
                                                        <li class="list-group-item">Create Forms: <input type="checkbox" id="create" checked disabled></li>
                                                        <li class="list-group-item">Edit Forms: <input type="checkbox" id="edit" checked disabled></li>
                                                        <li class="list-group-item">Delete Forms: <input type="checkbox" id="delete" checked disabled></li>
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
                                                <span>Users associated with this project group:</span>
                                                <ul class="list-group" id="list{{$projectGroup->id}}">
                                                    @foreach($projectGroup->users()->get() as $user)
                                                        <li class="list-group-item" id="list-element{{$projectGroup->id}}{{$user->id}}" name="{{$user->name}}">
                                                            {{$user->username}} <a href="javascript:void(0)" onclick="removeUser({{$projectGroup->id}}, {{$user->id}}, {{$project->pid}})">[X]</a>
                                                        </li>
                                                    @endforeach
                                                </ul>

                                                <select onchange="addUser({{$projectGroup->id}}, {{$project->pid}})" id="dropdown{{$projectGroup->id}}">
                                                    <option selected value="0">Add a user</option>
                                                    @foreach($all_users as $user)
                                                        @if($projectGroup->hasUser($user))
                                                        @else
                                                            <option id="{{$user->id}}">{{$user->username}}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            <hr/>
                                            <div id="checkboxes">
                                                <span>Permissions:</span>
                                                <ul class="list-group" id="perm-list{{$projectGroup->id}}">
                                                    <li class="list-group-item">Create Forms:
                                                        <input type="checkbox" id="create{{$projectGroup->id}}" @if($projectGroup->create) checked="checked" @endif onclick="updatePermissions({{$projectGroup->id}})">
                                                    </li>
                                                    <li class="list-group-item">Edit Forms:
                                                        <input type="checkbox" id="edit{{$projectGroup->id}}" @if($projectGroup->edit) checked="checked" @endif onclick="updatePermissions({{$projectGroup->id}})">
                                                    </li>
                                                    <li class="list-group-item">Delete Forms:
                                                        <input type="checkbox" id="delete{{$projectGroup->id}}" @if($projectGroup->delete) checked="checked" @endif onclick="updatePermissions({{$projectGroup->id}})">
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                            <div class="panel-footer">
                                                <a href="javascript:void(0)" onclick="deleteProjectGroup({{$projectGroup->id}})">[Delete Project Group]</a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                        <hr/>

                        <h3>Create Project Groups</h3>

                        @include('partials.newProjectGroup')

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer')
    <script>
        $(".panel-heading").on("click", function(){
            if ($(this).siblings('.collapseTest').css('display') == 'none') {
                $(this).siblings('.collapseTest').slideDown();
            } else {
                $(this).siblings('.collapseTest').slideUp();
            }
        });

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
                    $("#dropdown"+projectGroup).attr('selected', '0');

                    $("#list-element"+projectGroup+userId).remove();
                    $("#dropdown"+projectGroup).append('<option id="'+userId+'">'+username+'</option>');
                }
            });
        }

        function addUser(projectGroup, pid){
            var userId = $("#dropdown"+projectGroup+" option:selected").attr('id');
            var username = $("#dropdown"+projectGroup+" option:selected").text();

            $.ajax({
                url: '{{action('ProjectGroupController@addUser')}}',
                type: 'PATCH',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "userId": userId,
                    "projectGroup": projectGroup
                },
                success: function(){
                    $("#list"+projectGroup).append('<li class="list-group-item" id="list-element'+projectGroup+userId+'" name="'+username+'">'
                                                    +username+' <a href="javascript:void(0)" onclick="removeUser('+projectGroup+', '+userId+', '+pid+')">[X]</a></li>');
                    $("#dropdown"+projectGroup+" option[id='"+userId+"']").remove();
                }
            });
        }

        function deleteProjectGroup(projectGroup){
            var response = confirm("Are you sure you want to delete this group?");
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

        function updatePermissions(projectGroup){
            var permCreate, permEdit, permDelete;

            if ($("#create"+projectGroup).is(':checked'))
                permCreate = 1;
            else
                permCreate = 0;

            if ($("#edit"+projectGroup).is(':checked'))
                permEdit = 1;
            else
                permEdit = 0;

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