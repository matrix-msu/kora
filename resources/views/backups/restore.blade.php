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

                <form method="get" action={{action("BackupController@restoreData")}}>
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{trans('backups_restore.restore')}}
                        </div>

                        <div class="panel-body">
                            <div style="" id="summary">
                                <p>
                                    {{trans('backups_restore.restorenotes')}}.
                                </p>
                                <div class="progress">
                                    <div id="progress" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                                        <span class="sr-only">{{trans('backups_restore.almostdone')}}</span>
                                    </div>
                                </div>
                            </div>
                            <div style="display:none" id="summary_done">
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
                </form>
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
            @endif
            $.ajax({
                url:restoreURL,
                method:'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    filename: "{{$filename}}"
                },
                success: function(data){
                    console.log(data);
                    $("#progress").css("display","none");
                    $("#summary").css("display","inline");
                    console.log("done");
                    window.onbeforeunload = null;
                },
                error: function(data){
                    $("#progress").fadeOut();
                    $("#summary").fadeOut();
                    $("#user_lockout_notice").fadeIn(1000);
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

        function checkProgress(){
            $.ajax({
                url:"{{action('BackupController@checkRestoreProgress',compact('backup_id'))}}",
                method:'GET',
                data:{
                    "_token":"{{csrf_token()}}",
                    filename: "{{$filename}}"
                },
                success: function(data) {
                    console.log(data);
                    $("#progress").removeClass('progress-bar-danger');
                    $("#progress").css('width', (((data.overall.progress / data.overall.overall) * 100) + "%"));
                    if (data.overall.progress == data.overall.overall) {
                        $.ajax({
                            url: "{{action('BackupController@finishRestore')}}",
                            method:'POST',
                            data: {
                                "_token": "{{ csrf_token() }}",
                                filename: "{{$filename}}"
                            },
                            success: function(data){
                                $('#summary').slideUp();
                                $('#summary_done').slideDown();
                            }
                        });
                    } else {
                        setTimeout(function () {
                            checkProgress();
                        }, 5000);
                    }
                },
                error: function(data){
                    console.log("error checking progress!");
                    $("#progress").addClass('progress-bar-danger');
                    setTimeout(function () {
                        checkProgress();
                    },5000);
                }
            });
        }



        $(restore);
        setTimeout(function(){
            checkProgress();
        },10000);
    </script>
@endsection

