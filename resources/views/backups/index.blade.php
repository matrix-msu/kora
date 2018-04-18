@extends('app', ['page_title' => "Backups", 'page_class' => 'backup-management'])

@section('leftNavLinks')
    @include('partials.menu.static', ['name' => 'Backups'])
@stop

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-backup path3"></i>
                <span>Backup Management</span>
            </h1>
            @include('partials.backups.support')
            <p class="description">Brief backup management intro will go here. A backup file will be created and saved
                as a restore point on the server. You can download this file afterwards and save it somewhere safe. You
                can include a name or short description, the start date and time will be added for you. Depending on the
                size of your database, this may take a few minutes to finish.</p>
            <div class="content-sections">
                <a href="#backups" class="backups-link section underline-middle underline-middle-hover toggle-by-name active">Your Backups</a>
                <a href="#filerestore" class="filerestore-link section underline-middle underline-middle-hover toggle-by-name">Restore From Local File</a>
            </div>
        </div>
    </section>
@stop

@section('body')
    @include("partials.backups.createBackupModal")

    <section class="backups-section">
        <section class="filters center">
            <div class="underline-middle search search-js">
                <i class="icon icon-search"></i>
                <input type="text" placeholder="Find a Backup">
                <i class="icon icon-cancel icon-cancel-js"></i>
            </div>
            <div class="pagination-options pagination-options-js">
                <select class="order option-dropdown-js" id="order-dropdown">
                    <option value="nod">Newest to Oldest</option>
                    <option value="otn" {{app('request')->input('order') === 'noa' ? 'selected' : ''}}>Oldest to Newest</option>
                    <option value="nmd" {{app('request')->input('order') === 'nmd' ? 'selected' : ''}}>Name Ascending</option>
                    <option value="nma" {{app('request')->input('order') === 'nma' ? 'selected' : ''}}>Name Descending</option>
                </select>
            </div>
        </section>

        <section class="new-object-button center">
            <input type="button" value="Create New Backup File" class="create-backup-js">
        </section>
    </section>

    <section class="filerestore-section hidden">
        COMING SOON ...
    </section>
@stop

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

                @if(\Illuminate\Support\Facades\Session::has('user_backup_support'))
                    <h1 id="user_backup_support_message">( ͡° ͜ʖ ͡°) </h1>
                    <script>
                        setTimeout(function(){$("#user_backup_support_message").remove();},3000);
                    </script>
                @endif

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
                                        <option value="{{$backup->get("filename")}}">{{$backup->get("date")}} | {{$backup->get("name")}}</option>
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

@section('javascripts')
    @include('partials.backups.javascripts')

    <script type="text/javascript">
        var deleteBackupUrl = '{{action('BackupController@delete')}}';
        var CSRFToken = '{{ csrf_token() }}';

        Kora.Backups.Index();
    </script>
@stop

@section('footer')
    <script>

        {{--function disableRestore(){--}}
            {{--$("#group_restore_submit").hide('slow');--}}
            {{--$("#group_restore_points").hide('slow');--}}
            {{--$("#group_upload_files").hide('slow');--}}
            {{--$("#group_source_select").hide('slow');--}}
            {{--$("#restore_warning").css('display','block');--}}

        {{--}--}}

        {{--function deleteRestore(){--}}

            {{--var encode = $("<div/>").html("{{ trans('backups_index.areyousure') }}").text();--}}
            {{--if(!confirm(encode + "!")){--}}
                {{--return false;--}}
            {{--}--}}

            {{--encode = $("<div/>").html("{{ trans('backups_index.ifyouplan') }}").text();--}}
            {{--if(!confirm(encode + " storage/app/backup/files/backup_file_name/ ")){--}}
                {{--return false;--}}
            {{--}--}}

            {{--var deleteURL = deleteBackupUrl;--}}
            {{--var filename = $("#restore_point").val();--}}
            {{--$.ajax({--}}
                {{--url:deleteURL,--}}
                {{--method:'POST',--}}
                {{--data: {--}}
                    {{--"_token": CSRFToken,--}}
                    {{--"backup_source": "server",--}}
                    {{--"backup_type": "system",--}}
                    {{--"filename": filename--}}
                {{--},--}}
                {{--success: function(data){--}}

                {{--},--}}
                {{--error: function(data){--}}
                    {{--if(data.status == 422){--}}
                        {{--var encode = $('<div/>').html("{{ trans('backups_index.noselect') }}").text();--}}
                        {{--alert(encode);--}}
                    {{--}--}}
                   {{--//location.reload();--}}
                {{--}--}}
            {{--});--}}



        {{--}--}}
        {{--$("#backup_source").on('change',function() {--}}
            {{--if(this.value == 'server') {--}}
                {{--$("#group_upload_files").hide('slow');--}}
                {{--$("#upload_file").val('');--}}
                {{--$("#group_restore_points").show('slow');--}}
                {{--$("#group_restore_delete").show('slow');--}}
            {{--}--}}
            {{--else{--}}
                {{--$("#group_upload_files").show('slow');--}}
                {{--$("#group_restore_delete").hide('slow');--}}
                {{--$("#group_restore_points").hide('slow');--}}
                {{--$("#restore_point").val('');--}}
            {{--}--}}
        {{--});--}}

        {{--$("#btn_deleterestore").on('click',"",deleteRestore);--}}

        {{--//$("#btn_startbackup").on('click',"",disableRestore);--}}
    </script>
@endsection

