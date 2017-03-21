@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">

                <div style="display:none" id="user_lockout_notice" class="alert alert-danger" role="alert">
                    <strong>Users are locked out because the restore failed.</strong> <a id="link_unlock_users" href="#" class="alert-link">Unlock users</a>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        Kora Exodus
                    </div>

                    <div class="panel-body">
                        <div style="" id="summary">
                            <p>
                                This process may take a while depending on the size of your Kora 2 installation.
                            </p>
                            <div class="progress">
                                <div id="progress" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                                    <span class="sr-only">Almost there...</span>
                                </div>
                            </div>
                        </div>
                        <div style="display:none" id="summary_done">
                            <p>
                                Success! Your projects and data have been migrated.
                            </p>
                        </div>

                        <div style="display:none" id="error_info">
                            <p id="error_message">
                                There was an error during the migration process.
                            </p>
                            <ul style="display:none" id="error_list" class="list-group">
                                <li class="list-group-item">
                                    <span id="error_count" class="badge">Unknown</span>
                                    <strong class="list-group-item-heading">Errors</strong>
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
        function migrate(){
            window.onbeforeunload = function() {
                return "Do not leave this page, the kora 2 exodus process will be interrupted!";
            }
            $.ajax({
                url:"{{action('ExodusController@startExodus')}}",
                method:'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "host": '{{ $host }}',
                    "user": '{{ $user }}',
                    "name": '{{ $name }}',
                    "pass": '{{ $pass }}',
                    "migrateUsers": {{ $migrateUsers }},
                    "migrateTokens": {{ $migrateTokens }},
                    "projects": '{{ implode(',',$projects) }}',
                    "filePath": '{{ $filePath }}'
                },
                success: function(data){
                    $("#progress").css("display","none");
                    $("#summary").css("display","inline");
                    window.onbeforeunload = null;
                    setTimeout(function () {
                        checkProgress();
                    }, 5000);
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
                url:"{{action('ExodusController@checkProgress')}}",
                method:'GET',
                data:{
                    "_token":"{{csrf_token()}}"
                },
                success: function(data) {
                    console.log(data);
                    $("#progress").removeClass('progress-bar-danger');
                    if(data=='inprogress'){
                        setTimeout(function () {
                            checkProgress();
                        }, 5000);
                    }
                    $("#progress").css('width', (((data.overall.progress / data.overall.overall) * 100) + "%"));
                    if (data.overall.progress == data.overall.overall && data.overall.overall!=0) {
                        $('#summary').slideUp();
                        $('#summary_done').slideDown();
                        unlockUsers();
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

        function unlockUsers(){
            var unlockURL = "{{action('ExodusController@unlockUsers')}}";
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
                    var encode = $('<div/>').html("Unable to restore access to all users").text();
                    alert(encode + ".");
                }
            })
        }

        $("#link_unlock_users").click(function(){
            unlockUsers();
        });

        $(migrate);
        setTimeout(function () {
            checkProgress();
        },30000);
    </script>
@endsection

