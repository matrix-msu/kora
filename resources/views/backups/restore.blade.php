@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">

                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <strong>Whoops!</strong> Make sure you entered everything correctly<br>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div style="display:none" id="user_lockout_notice" class="alert alert-danger" role="alert">
                    <strong>Users are locked out because the restore failed.</strong> <a id="link_unlock_users" href="#" class="alert-link">Unlock users</a>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        Restore
                    </div>

                    <div class="panel-body">
                        <div style="" id="progress">
                            <p>
                                The restore has started, depending on the size of your database, it may take a few minutes
                                to complete.  Do not leave this page or close your browser until it is completed.
                                When the backup is complete, you can see a summary of all the data that was saved.
                            </p>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                                    <span class="sr-only">99% Complete</span>
                                </div>
                            </div>
                        </div>
                        <div style="display:none" id="summary">
                            <p>
                                The restore has completed successfully.
                            </p>

                        </div>
                        <div style="display:none" id="error_info">
                            <p id="error_message">
                                There was an error during the restore, no error information is available.
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
        function restore(){
            window.onbeforeunload = function() {
                return "Do not leave this page, the restore process will be interrupted!";
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
            alert("There is no download available for you at this time.");
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
                    alert("Unable to restore access to all users.");
                }
            })
        });

        $(restore);
    </script>
@endsection

@stop

