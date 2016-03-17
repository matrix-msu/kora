@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">

                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <strong>{{trans('backups_index.whoops')}}!</strong> {{trans('backups_index.makesure')}}<br>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="post" action={{action("BackupController@startBackup")}}>
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{trans('backups_index.backup')}}
                        </div>
                        <div class="panel-body">
                            <p>
                                {{trans('backups_index.backupnotes')}}.
                            </p>

                            <div class="form-group">
                                <label for="backup_label">{{trans('backups_index.label')}}:</label>
                                <input type="text" class="form-control" id="backup_label" name="backup_label">
                            </div>
                            <div class="form-group">
                                <input type="submit" class="btn btn-primary form-control" id="btn_startbackup" value="{{trans('backups_index.startback')}}">
                            </div>
                        </div>
                    </div>
                </form>

                <form method="post" enctype="multipart/form-data" action={{action("BackupController@startRestore")}}>
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{trans('backups_index.restore')}}
                        </div>

                        <div class="panel-body">
                            <div id="restore_warning" style="display: none;" class="alert alert-warning" role="alert"><strong></strong>{{trans('backups_index.cantdothat')}}.</div>
                            <div id="group_source_select" class="form-group">
                                <label for="backup_source">{{trans('backups_index.restorefrom')}}:</label>
                                <select id="backup_source" name="backup_source" class="form-control">
                                    <option value="server">{{trans('backups_index.restoreserver')}}</option>
                                    <option value="upload">{{trans('backups_index.restorecomp')}}</option>
                                </select>
                            </div>

                            <div class="form-group" id="group_restore_points">
                                <label for="restore_point">{{trans('backups_index.saverestore')}}:</label>
                                <select id="restore_point" name="restore_point" class="form-control">

                                    @foreach($saved_backups as $backup)
                                        <option value={{$backup->get("index")}}>{{$backup->get("date")}} | {{$backup->get("name")}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div style="display: none" class="form-group" id="group_upload_files">
                                <label for="upload_file">{{trans('backups_index.upload')}}:</label>
                                <input type="file" id="upload_file" name="upload_file">
                            </div>

                            <div id="group_restore_submit" class="form-group">
                                <input id="btn_startrestore" type="submit" class="btn btn-primary form-control" value="{{trans('backups_index.startres')}}">
                            </div>

                            <div id="group_restore_delete" class="form-group">
                                <button type="button"  id="btn_deleterestore" class="btn btn-primary btn-danger form-control">{{trans('backups_index.delete')}}</button>
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

        function disableRestore(){
            $("#group_restore_submit").hide('slow');
            $("#group_restore_points").hide('slow');
            $("#group_upload_files").hide('slow');
            $("#group_source_select").hide('slow');
            $("#restore_warning").css('display','block');

        }

        function deleteRestore(){

            var encode = $("<div/>").html("{{ trans('backups_index.areyousure') }}").text();
            if(!confirm(encode + "!")){
                return false;
            }

            encode = $("<div/>").html("{{ trans('backups_index.ifyouplan') }}").text();
            if(!confirm(encode + " storage/app/backup/files/backup_file_name/ ")){
                return false;
            }

            var deleteURL = "{{action('BackupController@delete')}}";
            var deleteIndex = $("#restore_point").val();
            $.ajax({
                url:deleteURL,
                method:'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "restore_point": deleteIndex,
                    "backup_source": "server",
                },
                success: function(data){
                 location.reload();
                },
                error: function(data){
                    if(data.status == 422){
                        var encode = $('<div/>').html("{{ trans('backups_index.noselect') }}").text();
                        alert(encode);
                    }
                   location.reload();
                }
            });



        }
        $("#backup_source").on('change',function() {
            if(this.value == 'server') {
                $("#group_upload_files").hide('slow');
                $("#upload_file").val('');
                $("#group_restore_points").show('slow');
                $("#group_restore_delete").show('slow');
            }
            else{
                $("#group_upload_files").show('slow');
                $("#group_restore_delete").hide('slow');
                $("#group_restore_points").hide('slow');
                $("#restore_point").val('');
            }
        });

        $("#btn_deleterestore").on('click',"",deleteRestore);

        //$("#btn_startbackup").on('click',"",disableRestore);
    </script>
@endsection

@stop

