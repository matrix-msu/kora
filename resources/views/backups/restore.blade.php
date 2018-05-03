@extends('app', ['page_title' => "Restoring Kora", 'page_class' => 'restore-start'])

@section('leftNavLinks')
    @include('partials.menu.static', ['name' => 'Restoring Kora'])
@stop

@section('aside-content')
  @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => false, 'openProjectDrawer' => true])
@stop

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-backup rotate-icon stop-rotation-js"></i>
                <span class="success-title-js">Restore in Progress</span>
            </h1>
            <div class="restore-toolbar">
                <span class="bold">Restore Name:</span>
                <?php
                    $parts = explode('___',$filename);
                    $carbon = new \Carbon\Carbon($parts[1]);
                    $n = $parts[0];
                    $d = $carbon->subDay()->format('m.d.Y');
                    $t = $carbon->format('g:i A');
                ?>
                <span>{{$n}}</span><span class="time">{{$d}}</span><span class="time">{{$t}}</span>
            </div>
            <p class="description success-desc-js">The restore has started, depending on the size of your database, it may take several
                minutes to complete. Do not leave this page or close your browser until completion. When the restore is
                complete, you can see a summary of all the data that was saved. </p>
        </div>
    </section>
@stop

@section('body')
    <section class="restore-progress">
        <div class="form-group">
            <div class="progress-bar-custom">
                <span class="progress-bar-filler progress-fill-js"></span>
            </div>

            <p class="progress-bar-text progress-text-js">Restoring the things… shuffle shuffle shuffle </p>
        </div>
    </section>

    <section class="restore-finish hidden">
        <div class="large-size-warning">
            Your Kora 3 Installation has been restored!
        </div>
    </section>
@stop

@section('footer')
    @include('partials.backups.javascripts')

    <script type="text/javascript">
        var startRestoreUrl = '{{action('BackupController@restoreData')}}';
        var checkProgressUrl = '{{action('BackupController@checkRestoreProgress')}}';
        var finishRestoreUrl = '{{action('BackupController@finishRestore')}}';
        var unlockUsersUrl = '{{action('BackupController@unlockUsers')}}';

        var restoreLabel = '{{ $filename }}';
        var CSRFToken = '{{ csrf_token() }}';

        Kora.Backups.Restore();
    </script>
@stop
