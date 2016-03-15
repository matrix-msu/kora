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
                        <h3>{{trans('formGroups_index.manage')}}</h3>
                    </div>

                    <div class="panel-body">

                        @foreach($formGroups as $formGroup)
                            @if($form->adminGID == $formGroup->id)
                                <div class="panel panel-default">
                                    <div class="panel-heading">{{$formGroup->name}}</div>

                                    <div class="collapseTest" style="display: none">
                                        <div class="panel-body">
                                            <span>{{trans('formGroups_index.users')}}:</span>
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
                                                <option selected value="0">{{trans('formGroups_index.adduser')}}</option>
                                                @foreach($all_users as $user)
                                                    @if($formGroup->hasUser($user))
                                                    @else
                                                        <option id="{{$user->id}}">{{$user->username}}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <hr/>
                                            <div id="checkboxes">
                                                <span>{{trans('formGroups_index.permissions')}}:</span>
                                                <ul class="list-group" id="perm-list{{$formGroup->id}}">
                                                    <li class="list-group-item">{{trans('formGroups_index.cField')}}: <input type="checkbox" id="create" checked disabled></li>
                                                    <li class="list-group-item">{{trans('formGroups_index.eField')}}: <input type="checkbox" id="edit" checked disabled></li>
                                                    <li class="list-group-item">{{trans('formGroups_index.dField')}}: <input type="checkbox" id="delete" checked disabled></li>
                                                    <li class="list-group-item">{{trans('formGroups_index.cRec')}} <input type="checkbox" id="ingest" checked disabled></li>
                                                    <li class="list-group-item">{{trans('formGroups_index.eRec')}}: <input type="checkbox" id="modify" checked disabled></li>
                                                    <li class="list-group-item">{{trans('formGroups_index.dRec')}}: <input type="checkbox" id="destroy" checked disabled></li>
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
                                            <span>{{trans('formGroups_index.users')}}:</span>
                                            <ul class="list-group" id="list{{$formGroup->id}}">
                                                @foreach($formGroup->users()->get() as $user)
                                                    <li class="list-group-item" id="list-element{{$formGroup->id}}{{$user->id}}" name="{{$user->name}}">
                                                        {{$user->username}} <a href="javascript:void(0)" onclick="removeUser({{$formGroup->id}}, {{$user->id}})">[X]</a>
                                                    </li>
                                                @endforeach
                                            </ul>

                                            <select onchange="addUser({{$formGroup->id}})" id="dropdown{{$formGroup->id}}">
                                                <option selected value="0">{{trans('formGroups_index.adduser')}}</option>
                                                @foreach($all_users as $user)
                                                    @if($formGroup->hasUser($user))
                                                    @else
                                                        <option id="{{$user->id}}">{{$user->username}}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <hr/>
                                            <div id="checkboxes">
                                                <span>{{trans('formGroups_index.permissions')}}:</span>
                                                <ul class="list-group" id="perm-list{{$formGroup->id}}">
                                                    <li class="list-group-item">{{trans('formGroups_index.cField')}}:
                                                        <input type="checkbox" id="create{{$formGroup->id}}" @if($formGroup->create) checked="checked" @endif onclick="updatePermissions({{$formGroup->id}})">
                                                    </li>
                                                    <li class="list-group-item">{{trans('formGroups_index.eField')}}:
                                                        <input type="checkbox" id="edit{{$formGroup->id}}" @if($formGroup->edit) checked="checked" @endif onclick="updatePermissions({{$formGroup->id}})">
                                                    </li>
                                                    <li class="list-group-item">{{trans('formGroups_index.dField')}}:
                                                        <input type="checkbox" id="delete{{$formGroup->id}}" @if($formGroup->delete) checked="checked" @endif onclick="updatePermissions({{$formGroup->id}})">
                                                    </li>
                                                    <li class="list-group-item">{{trans('formGroups_index.cRec')}}:
                                                        <input type="checkbox" id="ingest{{$formGroup->id}}" @if($formGroup->ingest) checked="checked" @endif onclick="updatePermissions({{$formGroup->id}})">
                                                    </li>
                                                    <li class="list-group-item">{{trans('formGroups_index.eRec')}}:
                                                        <input type="checkbox" id="modify{{$formGroup->id}}" @if($formGroup->modify) checked="checked" @endif onclick="updatePermissions({{$formGroup->id}})">
                                                    </li>
                                                    <li class="list-group-item">{{trans('formGroups_index.dRec')}}:
                                                        <input type="checkbox" id="destroy{{$formGroup->id}}" @if($formGroup->destroy) checked="checked" @endif onclick="updatePermissions({{$formGroup->id}})">
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="panel-footer">
                                            <a href="javascript:void(0)" onclick="deleteFormGroup({{$formGroup->id}})">[{{trans('formGroups_index.deleteform')}}]</a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        <hr/>

                        <h3>{{trans('formGroups_index.createform')}}</h3>

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
            var selector = $("#dropdown"+formGroup+" option:selected");

            var userId = selector.attr('id');
            var username = selector.text();

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

        /**
         * Update the permissions of a particular form group.
         *
         * Note that permissions create, edit, and delete refer to the creation, editing, and deletion of fields, respectfully.
         * And that permissions ingest, modify, and destroy refer to the creation, editing, and deletion of records, respectfully.
         *
         * @param formGroup {int} The form group id.
         */
        function updatePermissions(formGroup){
            var permCreate, permEdit, permDelete, permIngest, permModify, permDestroy;

            // If the box is checked, allow users in the form group to create fields within the form.
            if ($("#create"+formGroup).is(':checked'))
                permCreate = 1;
            else
                permCreate = 0;

            // Allow users to edit fields.
            if ($("#edit"+formGroup).is(':checked'))
                permEdit = 1;
            else
                permEdit = 0;

            // Allow users to delete fields.
            if ($("#delete"+formGroup).is(':checked'))
                permDelete = 1;
            else
                permDelete = 0;

            // If the box is checked, allow users in the form group to create records within the form.
            if ($("#ingest"+formGroup).is(':checked'))
                permIngest = 1;
            else
                permIngest = 0;

            // Allow users to edit records.
            if ($("#modify"+formGroup).is(':checked'))
                permModify = 1;
            else
                permModify = 0;

            // Allow users to delete records.
            if ($("#destroy"+formGroup).is(':checked'))
                permDestroy = 1;
            else
                permDestroy = 0;

            $.ajax({
                url: '{{action('FormGroupController@updatePermissions')}}',
                type: 'PATCH',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "formGroup": formGroup,
                    "permCreate": permCreate,
                    "permEdit": permEdit,
                    "permDelete": permDelete,
                    "permIngest": permIngest,
                    "permModify": permModify,
                    "permDestroy": permDestroy
                }
            });
        }

        $('#users').select2();
    </script>
@stop