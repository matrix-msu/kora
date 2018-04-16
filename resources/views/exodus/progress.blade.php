@extends('app', ['page_title' => "K2 Exodus Transfer", 'page_class' => 'kora-exodus-transfer'])

@section('leftNavLinks')
    @include('partials.menu.static', ['name' => 'K2 Exodus Transfer'])
@stop

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-exodus"></i>
                <span>Transfering ...</span>
            </h1>
            <p class="description">The transfer has started, depending on the size of your database, it may take a while
                to complete. Do not leave this page or close your browser until completion. When the backup is complete,
                you can see a summary of all the data that was saved.</p>
        </div>
    </section>
@stop

@section('body')
    <section class="exodus-progress">
        <div class="form-group">
            <div class="progress-bar-custom">
                <span class="progress-bar-filler progress-fill-js"></span>
            </div>

            <p class="progress-bar-text progress-text-js">Transfering the things… Fingers Crossed this Actually Works …  </p>
        </div>
    </section>
@endsection

@section('javascripts')
    @include('partials.exodus.javascripts')

    <script type="text/javascript">
        var startExodusUrl = '{{action('ExodusController@startExodus')}}';
        var finishExodusUrl = '{{action('ExodusController@finishExodus')}}';
        var checkProgressUrl = '{{action('ExodusController@checkProgress')}}';
        var unlockUsersUrl = '{{action('ExodusController@unlockUsers')}}';
        var projectsUrl = '{{action('ProjectController@index')}}';

        var host = '{{ $host }}';
        var user = '{{ $user }}';
        var name = '{{ $name }}';
        var pass = '{{ $pass }}';
        var migrateUsers = {{ $migrateUsers }};
        var migrateTokens = {{ $migrateTokens }};
        var projects = '{{ implode(',',$projects) }}';
        var filePath = '{{ $filePath }}';

        var CSRFToken = '{{ csrf_token() }}';

        Kora.Exodus.Transfer();
    </script>
@stop

