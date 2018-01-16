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
                                                    <li class="list-group-item" id="list-element{{$formGroup->id}}{{$user->id}}" name="{{$user->username}}">
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
                                    <div class="panel-heading">
                                        @if($formGroup->name == $form->name." Default Group")
                                            {{$formGroup->name}}
                                        @else
                                        <div class="formGroupName">
                                            {{$formGroup->name}} <a class="nameEdit">[EDIT]</a>
                                        </div>
                                        <div class="formGroupEdit" style="display:none">
                                            <input type="text" class="newGroupName" placeholder="{{$formGroup->name}}" gid="{{$formGroup->id}}">
                                            <a class="nameSave">[SAVE]</a> <a class="nameRevert">[X]</a>
                                        </div>
                                        @endif
                                    </div>

                                    <div class="collapseTest" style="display: none">
                                        <div class="panel-body">
                                            <span>{{trans('formGroups_index.users')}}:</span>
                                            <ul class="list-group" id="list{{$formGroup->id}}">
                                                @foreach($formGroup->users()->get() as $user)
                                                    <li class="list-group-item" id="list-element{{$formGroup->id}}{{$user->id}}" name="{{$user->username}}">
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
                                        @if($formGroup->name != $form->name." Default Group")
                                        <div class="panel-footer">
                                            <a href="javascript:void(0)" onclick="deleteFormGroup({{$formGroup->id}})">[{{trans('formGroups_index.deleteform')}}]</a>
                                        </div>
                                        @endif
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

@section('javascripts')
    @include('partials.forms.javascripts')
    <script>
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
            editDiv = mainDiv.siblings(".formGroupEdit");

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
            mainDiv = editDiv.siblings(".formGroupName");

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
                mainDiv = editDiv.siblings(".formGroupName");

                editDiv.slideUp();
                mainDiv.slideDown();
            }
        });

        function changeGroupName(textBox){
            textBox.attr('style','');
            newName = textBox.val();
            gid = textBox.attr('gid');
            pid = {{$form->pid}};
            fid = {{$form->fid}};

            editDiv = textBox.parent();
            mainDiv = editDiv.siblings(".formGroupName");

            if(newName==''){
                textBox.attr('style','border:3px solid red');
                return;
            }else {
                $.ajax({
                    url: '{{action('FormGroupController@updateName')}}',
                    type: 'PATCH',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "gid": gid,
                        "name": newName,
                        "pid" : pid,
                        "fid" : fid
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

                    $("#list"+formGroup).append('<li class="list-group-item" id="list-element'+formGroup+userId+'" name="'+username+'">'
                    +username+' <a href="javascript:void(0)" onclick="removeUser('+formGroup+', '+userId+')">[X]</a></li>');
                    $("#dropdown"+formGroup+" option[id='"+userId+"']").remove();
                }
            });
        }

        function deleteFormGroup(formGroup){
            var encode = $('<div/>').html("{{ trans('formGroups_index.deleteconfirm') }}").text();
            var response = confirm(encode);
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

    </script>
@stop