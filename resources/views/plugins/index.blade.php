@extends('app')

@section('content')
    <h1>{{trans('plugins_index.header')}}</h1>

    <hr/>

    @foreach($plugins as $plug)
    <div class="panel panel-default">
        <div class="panel-heading">
            <span>{{strtoupper($plug->name)}}</span>
            <span>{{trans('plugins_index.active')}}: @if($plug->active==1)<input type="checkbox" id="{{$plug->id}}" class="active" checked>@else<input type="checkbox" id="{{$plug->id}}" class="active">@endif</span>
        </div>
        <div class="collapseTest" style="display:none">
            <div class="panel-body" plugid="{{$plug->id}}">
                <div>{{trans('plugins_index.settings')}}</div><hr>
                <div>
                    <ul>
                        @foreach($plug->options() as $opt)
                        <li>{{$opt->option}}: <input type="text" class="form-control plugin_option" option="{{$opt->option}}" value="{{$opt->value}}"></li>
                        @endforeach
                    </ul>
                </div>
                <div>Users</div><hr>
                <div id="user_list">
                    @foreach($plug->users() as $user)
                    <div id="user">{{$user->username}} <a uid="{{$user->id}}" username="{{$user->username}}" id="remove_user" class="user_info">[X]</a></div>
                    @endforeach
                    <select id="user_select">
                        @foreach($plug->new_users() as $user)
                        <option value="{{$user->id}}">{{$user->username}}</option>
                        @endforeach
                    </select>
                    <input type="button" value="Add User" id="add_user" class="btn">
                </div>
            </div>
            <div class="panel-footer">
                <span><a id="save_plugin">[{{trans('plugins_index.save')}}]</a></span>
                <span><a onclick="deletePlugin('{{ $plug->name }}', {{ $plug->id }})" href="javascript:void(0)">[{{trans('plugins_index.delete')}}]</a></span>
            </div>
        </div>
    </div>
    @endforeach
    @foreach($newPlugs as $plug)
        <div class="panel panel-default">
            <div class="panel-heading">
                <span>{{$plug}}</span>
                <span><a pName="{{$plug}}" id="install_plugin">[{{trans('plugins_index.install')}}]</a></span>
            </div>
        </div>
    @endforeach
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

        $( ".panel-heading" ).on( "click", "#install_plugin",function() {
            name = $(this).attr('pName');
            $.ajax({
                //We manually create the link in a cheap way because the JS isn't aware of the pid until runtime
                //We pass in a blank project to the action array and then manually add the id
                url: '{{ action('PluginController@install',['']) }}/'+name,
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function (result) {
                    location.reload();
                }
            });
        });

        $( "#user_list" ).on( "click", "#add_user",function() {
            select = $(this).siblings('#user_select');
            opt = select.find(":selected");

            uid = opt.val();
            name = opt.text();

            newDiv = '<div id="user">'+name+' <a uid="'+uid+'" username="'+name+'" id="remove_user" class="user_info">[X]</a></div>';
            select.before(newDiv);

            opt.remove();
            select.val('');
        });

        $( "#user_list" ).on( "click", "#remove_user",function() {
            uid = $(this).attr('uid');
            name = $(this).attr('username');

            div = $(this).parent('#user');
            select = div.siblings('#user_select');

            curr = select.html();
            curr += '<option value="'+uid+'">'+name+'</option>';
            select.html(curr);
            div.remove();
        });

        $( ".panel-footer" ).on( "click", "#save_plugin",function() {
            var options = {};
            var users = [];
            body = $(this).parent(".panel-footer").siblings(".panel-body");
            plugin_id = body.attr('plugid');

            body.find('.plugin_option').each(function () {
                opt = $(this).attr("option");
                value = $(this).val();

                options[opt] =value;
            });

            body.find('.user_info').each(function () {
                uid = $(this).attr('uid');
                users.push(uid);
            });

            //we have the info, make the call!!!
            $.ajax({
                url: '{{ action('PluginController@update') }}',
                type: 'PATCH',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "options": options,
                    "users": users,
                    "plugin_id": plugin_id
                },
                success: function (result) {
                    location.reload();
                }
            });
        });

        $( ".panel-heading" ).on( "click", ".active",function() {
            plid = $(this).attr("id");
            checked = $(this).is(":checked");

            $.ajax({
                url: '{{ action('PluginController@activate') }}',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "plid": plid,
                    "checked": checked
                },
                success: function (result) {
                    location.reload();
                }
            });
        });

        function deletePlugin(pluginName,plid) {
            var encode = $('<div/>').html("{{trans('plugin_index.areyousure')}}").text();
            var response = confirm(encode + pluginName + "?");
            if (response) {
                $.ajax({
                    //We manually create the link in a cheap way because the JS isn't aware of the pid until runtime
                    //We pass in a blank project to the action array and then manually add the id
                    url: '{{ action('PluginController@destroy',['']) }}/'+plid,
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function (result) {
                        location.reload();
                    }
                });
            }
        }
    </script>
@stop