@extends('app')

@section('content')
  <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-body">

                        <h3>Manage Groups</h3>
                        @foreach($groups as $group)
                            <div class="panel panel-default">

                                <div class="panel-heading">{{$group->name}}</div>

                                <div class="collapseTest" style="display: none">
                                    <div class="panel-body">
                                        <span>Users associated with this group:</span>
                                        <ul class="list-group" id="list{{$group->id}}">
                                        @foreach($group->users()->get() as $user)
                                            <li class="list-group-item" id="list-element{{$group->id}}{{$user->id}}" name="{{$user->username}}">
                                                {{$user->username}} <a href="javascript:void(0)" onclick="removeUser({{$group->id}}, {{$user->id}})">[X]</a>
                                            </li>
                                        @endforeach
                                        </ul>

                                        <select onchange="addUser({{$group->id}})" id="dropdown{{$group->id}}">
                                            <option selected value="0">Add a User</option>
                                            @foreach ($all_users as $user)
                                                @if($group->hasUser($user))
                                                @else
                                                    <option id="{{$user->id}}">{{$user->username}}</option>
                                                @endif
                                            @endforeach
                                        </select>

                                    </div>

                                    <div class="panel-footer">
                                        <a href="javascript:void(0)" onclick="deleteGroup({{$group->id}})">[Delete Group]</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <h3>Create Groups</h3>

                        @include('partials.newGroup')

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer')
    <script>
        $( ".panel-heading" ).on( "click", function() {
            if ($(this).siblings('.collapseTest').css('display') == 'none' ){
                $(this).siblings('.collapseTest').slideDown();
            }else {
                $(this).siblings('.collapseTest').slideUp();
            }
        });

        function removeUser(group, id) {
            var username = $("#list-element"+group+id).attr('name');

            $.ajax({
                url: '{{action('GroupController@removeUser')}}',
                type: 'PATCH',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "user": id,
                    "group": group
                },
                success: function(){
                    $("#dropdown"+group).attr('selected', '0');

                    $("#list-element"+group+id).remove();
                    $("#dropdown"+group).append('<option id="'+id+'">'+username+'</option>');
                }
            });
        }

        function addUser(group) {
            var user = $('#dropdown' +group+ ' option:selected').attr('id');
            var username = $('#dropdown' +group+ ' option:selected').text();

            $.ajax({
                url: '{{action('GroupController@addUser')}}',
                type: 'PATCH',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "user": user,
                    "group": group
                },
                success: function(){ //This ridiculous <li> tag is needed as the user wont be an object instance when its added with jQuery
                    $("#list"+group).append('<li class="list-group-item" id="list-element'+group+user+'" name="'+username+'">'
                                            +username+' <a href="javascript:void(0)" onclick="removeUser('+group+', '+user+')">[X]</a></li>');
                    $("#dropdown"+group+" option[id='"+user+"']").remove();
                }
            });
        }

        function deleteGroup(group) {
            var response = confirm("Are you sure you want to delete this group?");
            if (response) {
                $.ajax({
                    url: '{{action('GroupController@deleteGroup')}}',
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "group": group
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