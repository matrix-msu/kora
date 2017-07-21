@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">

                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <strong>{{trans('backups_backup.whoops')}}!</strong> {{trans('backups_backup.makesure')}}<br>
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

                <form method="get" action={{action("BackupController@create")}}>
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{trans('backups_backup.backup')}}
                        </div>

                        <div class="panel-body">
                            <div style="" id="summary">
                                <p>
                                    {{trans('backups_backup.backupnotes')}}.
                                </p>
                                <div class="progress">
                                    <div id="progress" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                                        <span class="sr-only">{{trans('backups_backup.almostdone')}}</span>
                                    </div>
                                </div>
                                <div id="type_message">
                                    Backing up the database...
                                </div>
                            </div>
                            <div style="display:none" id="summary_done">
                                <div>
                                    {{trans('backups_backup.success')}}.
                                </div>
                                <div id="summary_size">

                                </div>
                                @if($type == "system")
                                    <button onclick="download()" type="button" class="btn btn-default">
                                        <span class="glyphicon glyphicon-save" aria-hidden="true"></span> Download
                                    </button>
                                @endif
                            </div>

                            <div style="display:none" id="error_info">
                                <p id="error_message">
                                    {{trans('backups_backup.restoreerror')}}.
                                </p>
                                <ul style="display:none" id="error_list" class="list-group">
                                    <li class="list-group-item">
                                        <span id="error_count" class="badge">{{trans('backups_backup.unknown')}}</span>
                                        <strong class="list-group-item-heading">{{trans('backups_backup.errors')}}</strong>
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
        function backup(){
            window.onbeforeunload = function() {
                return "{{trans('backups_backup.dontleave')}}!";
            }
            @if($type == "system")
                var backupURL ="{{action('BackupController@create')}}";
            @endif
            $.ajax({
                url:backupURL,
                method:'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "backup_name": '{{$backup_label}}'
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
                    $("#download_btn_for_error").fadeIn(1000);
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
                url:"{{action('BackupController@checkProgress',compact('backup_id'))}}",
                method:'GET',
                data:{
                    "_token":"{{csrf_token()}}",
                    "backup_label": "{{$backup_label}}"
                },
            success: function(data) {
                console.log(data);
                $("#progress").removeClass('progress-bar-danger');
                $("#progress").css('width', (((data.overall.progress / (data.overall.overall+ +1)) * 100) + "%"));
                if (data.overall.progress == data.overall.overall) {
                    $('#type_message').text('Backing up your files. This may take a while...');
                    $.ajax({
                        url: "{{action('BackupController@finishBackup')}}",
                        method:'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "backup_label": "{{$backup_label}}"
                        },
                        success: function(data2){
                            $('#summary_size').text("Estimated Pre-Compressed Download Size: "+data2.totalSize);
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

        function download(){
            window.location ='{{action("BackupController@download",['path'=>$backup_label])}}';
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

        $(backup);
        setTimeout(function(){
            checkProgress();
        },10000);
    </script>
@endsection

