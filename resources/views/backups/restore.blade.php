@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">

                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <strong>{{trans('backups_restore.whoops')}}!</strong> {{trans('backups_restore.makesure')}}<br>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div style="display:none" id="user_lockout_notice" class="alert alert-danger" role="alert">
                    <strong>{{trans('backups_restore.userslocked')}}.</strong> <a id="link_unlock_users" href="#" class="alert-link">{{trans('backups_restore.unlockusers')}}</a>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{trans('backups_restore.restore')}}
                    </div>

                    <div class="panel-body">
                        <div style="" id="progress">
                            <p>
                                {{trans('backups_restore.restorenotes')}}.
                            </p>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                                    <span class="sr-only">{{trans('backups_restore.almostdone')}}</span>
                                </div>
                            </div>
                        </div>
                        <div style="display:none" id="summary">
                            <p>
                                {{trans('backups_restore.success')}}.
                            </p>

                        </div>
                        <div style="display:none" id="error_info">
                            <p id="error_message">
                                {{trans('backups_restore.restoreerror')}}.
                            </p>
                              <ul style="display:none" id="error_list" class="list-group">
                                  <li class="list-group-item">
                                      <span id="error_count" class="badge">{{trans('backups_restore.unknown')}}</span>
                                      <strong class="list-group-item-heading">{{trans('backups_restore.errors')}}</strong>
                                  </li>
                              </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('footer')
    <script>
        function restore(){
            window.onbeforeunload = function() {
                return "{{trans('backups_restore.dontleave')}}!";
            }
            @if($type == 'system')
                var restoreURL ="{{action('BackupController@restoreData')}}";
            @elseif($type=="project")
                var restoreURL ="{{action('BackupController@restoreProject')}}";
            @endif
            $.ajax({
                url:restoreURL,
                method:'POST',
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(data){
                    $("#progress").css("display","none");
                    $("#summary").css("display","inline");
                    window.onbeforeunload = null;
                },
                error: function(data){
                    $("#progress").fadeOut();
                    $("#summary").fadeOut();
                    @if($type == 'system')
                    $("#user_lockout_notice").fadeIn(1000);
                    @endif
                    $("#error_info").fadeIn(1000);
                    $("#error_message").text(data.responseJSON.message);
                    if(data.responseJSON.error_list.length >0){
                        $("#error_list").fadeIn(1000);
                    }

                    for(var i=0; i<data.responseJSON.error_list.length; i++) {
                        $("#error_list").append("<li class='list-group-item small'> <span class='glyphicon glyphicon-chevron-right'></span>"+data.responseJSON.error_list[i]+"</li>");
                    }
                    $("#error_count").text(data.responseJSON.error_list.length);
                    window.onbeforeunload = null;
                }
            });
        }
        function download(){
            var encode = $('<div/>').html("{{ trans('backups_restore.nodownload') }}").text();
            alert(encode + ".");
        }

        $("#link_unlock_users").click(function(){
            var unlockURL = "{{action('BackupController@unlockUsers')}}";
            $.ajax({
                url:unlockURL,
                method:'POST',
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(data){
                    //alert("Users are now able to access Kora 3");
                    $("#user_lockout_notice").removeClass("alert-danger").addClass("alert-success");
                    $("#user_lockout_notice").text("Success- users unlocked");
                    $("#user_lockout_notice").fadeOut(1000);
                },
                error: function(data){
                    var encode = $('<div/>').html("{{ trans('backups_restore.unablerestore') }}").text();
                    alert(encode + ".");
                }
            })
        });

        $(restore);
    </script>
@endsection

@stop

