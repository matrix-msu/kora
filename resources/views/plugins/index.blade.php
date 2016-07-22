@extends('app')

@section('content')
    <h1>{{trans('plugins_index.header')}}</h1>

    <hr/>

    <div class="panel panel-default">
        <div class="panel-heading">
            <span>PLUGIN NAME</span>
            <span>Active: <input type="checkbox" id="active"></span>
        </div>
        <div class="collapseTest" style="display:none">
            <div class="panel-body">
                <div>Settings</div><hr>
                <div>
                    <ul>
                        <li>Option: <input type="text" id="option_id" name="option_name"></li>
                        <li>Option: <input type="text" id="option_id" name="option_name"></li>
                        <li>Option: <input type="text" id="option_id" name="option_name"></li>
                    </ul>
                </div>
                <div>Users</div><hr>
                <div>
                    <ul>
                        <li>User <a>[X]</a></li>
                        <li>User <a>[X]</a></li>
                        <li>User <a>[X]</a></li>
                        <li><select id="user_select">
                                <option value="user_id">User</option>
                                <option value="user_id">User</option>
                                <option value="user_id">User</option>
                            </select>
                            <input type="button" value="Add User">
                        </li>
                    </ul>
                </div>
            </div>
            <div class="panel-footer">
                <a>[Save Plugin]</a>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <span>PLUGIN NAME</span>
            <span><a>[install]</a></span>
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
    </script>
@stop