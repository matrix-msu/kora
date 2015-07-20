@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
@stop

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3>Manage Form Groups</h3>
                    </div>

                    <div class="panel-body">

                        @foreach($formGroups as $formGroup)
                            @if($form->adminGID == $formGroup->id)
                                <div class="panel panel-default">
                                    <div class="panel-heading">{{$formGroup->name}}</div>

                                    <div class="collapseTest" style="display: none">
                                        <div class="panel-body">
                                            <span>Users associated with this form group:</span>
                                            <ul class="list-group" id="list{{$formGroup->id}}">
                                                @foreach($formGroup->users()->get() as $user)
                                                    <li class="list-group-item" id="list-element{{$formGroup->id}}{{$user->id}}" name="{{$user->name}}">
                                                        {{$user->username}} @if(\Auth::user()->id != $user->id)
                                                            <a href="javascript:void(0)" onclick="removeUser({{$formGroup->id}}, {{$user->id}})">[X]</a>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <select onchange="addUser({{$formGroup->id}})" id="dropdown{{$formGroup->id}}">
                                                <option selected value="0">Add a user</option>
                                                @foreach($all_users as $user)
                                                    @if($formGroup->hasUser($user))
                                                    @else
                                                        <option id="{{$user->id}}">{{$user->username}}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <hr/>
                                            <div id="checkboxes">
                                                <span>Permissions:</span>
                                                <ul class="list-group" id="perm-list{{$formGroup->id}}">
                                                    <li class="list-group-item">Create: <input type="checkbox" id="create" checked disabled></li>
                                                    <li class="list-group-item">Edit: <input type="checkbox" id="edit" checked disabled></li>
                                                    <li class="list-group-item">Delete: <input type="checkbox" id="delete" checked disabled></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @foreach($formGroups as $formGroup)
                            @if($form->adminGID != $formGroup->id)
                                <div class="panel panel-default">
                                    <div class="panel-heading">{{$formGroup->name}}</div>

                                    <div class="collapseTest" style="display: none">
                                        <div class="panel-body">
                                            <span>Users associated with this form group:</span>
                                            <ul class="list-group" id="list{{$formGroup->id}}">
                                                @foreach($formGroup->users()->get() as $user)
                                                    <li class="list-group-item" id="list-element{{$formGroup->id}}{{$user->id}}" name="{{$user->name}}">
                                                        {{$user->username}} <a href="javascript:void(0)" onclick="removeUser({{$formGroup->id}}, {{$user->id}})">[X]</a>
                                                    </li>
                                                @endforeach
                                            </ul>

                                            <select onchange="addUser({{$formGroup->id}})" id="dropdown{{$formGroup->id}}">
                                                <option selected value="0">Add a user</option>
                                                @foreach($all_users as $user)
                                                    @if($formGroup->hasUser($user))
                                                    @else
                                                        <option id="{{$user->id}}">{{$user->username}}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <hr/>
                                            <div id="checkboxes">
                                                <span>Permissions:</span>
                                                <ul class="list-group" id="perm-list{{$formGroup->id}}">
                                                    <li class="list-group-item">Create:
                                                        <input type="checkbox" id="create{{$formGroup->id}}" @if($formGroup->create) checked="checked" @endif onclick="updatePermissions({{$formGroup->id}})">
                                                    </li>
                                                    <li class="list-group-item">Edit:
                                                        <input type="checkbox" id="edit{{$formGroup->id}}" @if($formGroup->edit) checked="checked" @endif onclick="updatePermissions({{$formGroup->id}})">
                                                    </li>
                                                    <li class="list-group-item">Delete:
                                                        <input type="checkbox" id="delete{{$formGroup->id}}" @if($formGroup->delete) checked="checked" @endif onclick="updatePermissions({{$formGroup->id}})">
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="panel-footer">
                                            <a href="javascript:void(0)" onclick="deleteFormGroup({{$formGroup->id}})">[Delete Form Group]</a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        <hr/>

                        <h3>Create Form Groups</h3>

                        @include('partials.newFormGroup')

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

        function removeUser(formGroup, userId){
            var username = $("#list-element"+formGroup+userId).attr('name');

            $.ajax({
                url: '{{action('FormGroupController@removeUser')}}',
                type: 'PATCH',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "userId": userId,
                    "formGroup": formGroup
                },
                success: function(){
                    $("#dropdown"+formGroup).attr('selected', '0');

                    $("#list-element"+formGroup+userId).remove();
                    $("#dropdown"+formGroup).append('<option id="'+userId+'">'+username+'</option>');
                }
            });
        }

        function addUser(formGroup){
            var userId = $("#dropdown"+formGroup+" option:selected").attr('id');
            var username = $("#dropdown"+formGroup+" option:selected").text();

            $.ajax({
                url: '{{action('FormGroupController@addUser')}}',
                type: 'PATCH',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "userId": userId,
                    "formGroup": formGroup
                },
                success: function(){
                    $("#list"+formGroup).append('<li class="list-group-item" id="list-element'+formGroup+userId+'" name="'+username+'">'
                    +username+' <a href="javascript:void(0)" onclick="removeUser('+formGroup+', '+userId+')">[X]</a></li>');
                    $("#dropdown"+formGroup+" option[id='"+userId+"']").remove();
                }
            });
        }

        function deleteFormGroup(formGroup){
            var response = confirm("Are you sure you want to delete this group?");
            if (response) {
                $.ajax({
                    url: '{{action('FormGroupController@deleteFormGroup')}}',
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "formGroup": formGroup
                    },
                    success: function() {
                        location.reload();
                    }
                });
            }
        }

        function updatePermissions(formGroup){
            var permCreate, permEdit, permDelete;

            if ($("#create"+formGroup).is(':checked'))
                permCreate = 1;
            else
                permCreate = 0;

            if ($("#edit"+formGroup).is(':checked'))
                permEdit = 1;
            else
                permEdit = 0;

            if ($("#delete"+formGroup).is(':checked'))
                permDelete = 1;
            else
                permDelete = 0;

            $.ajax({
                url: '{{action('FormGroupController@updatePermissions')}}',
                type: 'PATCH',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "formGroup": formGroup,
                    "permCreate": permCreate,
                    "permEdit": permEdit,
                    "permDelete": permDelete
                }
            });
        }

        $('#users').select2();
    </script>
@stop