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

                <form method="get" action={{action("BackupController@create")}}>
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Backup
                        </div>

                        <div class="panel-body">
                            <div style="" id="progress">
                                <p>
                                    The backup has started, depending on the size of your database, it may take a few minutes
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
                                    The backup has completed successfully.
                                </p>
                                @if($type == "system")
                                    <button onclick="download()" type="button" class="btn btn-default">
                                        <span class="glyphicon glyphicon-save" aria-hidden="true"></span> Download
                                    </button>
                                @endif
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
                                @if($type == "system")
                                    <button id="download_btn_for_error" style="display:none" onclick="download()" type="button" class="btn btn-default">
                                        <span class="glyphicon glyphicon-save" aria-hidden="true"></span> Download
                                    </button>
                                @endif
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
                return "Do not leave this page, the backup process will be interrupted!";
            }

            @if($type == "system")
                var backupURL ="{{action('BackupController@create')}}";
            @elseif($type == "project")
                var backupURL = "{{action('BackupController@createProject')}}";
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
        function download(){
                window.location ='{{action("BackupController@download")}}';
        }
$(backup);
    </script>
@endsection

@stop

