@extends('app', ['page_title' => "Backing Up", 'page_class' => 'backup-start'])

@section('leftNavLinks')
    @include('partials.menu.static', ['name' => 'Backing Up'])
@stop

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-backup path3"></i>
                <span>Creating Backup File</span>
            </h1>
            {{--TODO::Add top bar--}}
            <p class="description">The backup has started, depending on the size of your database, it may take several
                minutes to complete. Do not leave this page or close your browser until completion. When the backup is
                complete, you can see a summary of all the data that was saved. </p>
        </div>
    </section>
@stop

@section('body')
    <section class="backup-progress">
        <div class="form-group">
            <div class="progress-bar-custom">
                <span class="progress-bar-filler progress-fill-js"></span>
            </div>

            <p class="progress-bar-text progress-text-js">Backing up the things… Beep beep beep </p>
        </div>
    </section>
@stop

@section('footer')
    @include('partials.backups.javascripts')

    <script type="text/javascript">
        var startBackupUrl = '{{action('BackupController@create')}}';
        var checkProgressUrl = '{{action('BackupController@checkProgress')}}';
        var finishBackupUrl = '{{action('BackupController@finishBackup')}}';
        var downloadFileUrl = '{{action("BackupController@download",['path'=>$backupLabel])}}';

        var buLabel = '{{ $backupLabel }}';
        var buData = '{{ $metadata }}'; //TODO
        var buFiles = '{{ $files }}'; //TODO

        var CSRFToken = '{{ csrf_token() }}';

        Kora.Backups.Progress();
    </script>
@endsection

