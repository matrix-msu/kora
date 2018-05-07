@extends('app', ['page_title' => "K2 Exodus", 'page_class' => 'kora-exodus'])

@section('leftNavLinks')
    @include('partials.menu.static', ['name' => 'K2 Exodus'])
@stop

@section('aside-content')
  <?php $openManagement = true ?>
  @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => false, 'openProjectDrawer' => false])
@stop

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-exodus"></i>
                <span>Kora 2 Exodus</span>
            </h1>
            <p class="description">Transfer an entire Kora 2 installation to Kora 3. Note: This process can take some
                time. While the Kora 2 DB doesn't have to be on the same server, it is REQUIRED for Kora 2 records files
                to be on the same server. If your Kora 2 file set is on a separate server, please copy the "Kora2/files"
                directory to the same server as Kora3 and point the system path below to that directory.</p>
            <div class="content-sections">
                <a href="#database" class="database-link underline-middle active">Database Information</a>
                <span class="progression-tab"></span>
                <a href="#projects" class="projects-link">Project Selection</a>
            </div>
        </div>
    </section>
@stop

@section('body')
    {!! Form::open(['url' => action('ExodusController@migrate'), 'id' => 'k2_form']) !!}
        <section class="exodus-database">
            <div class="form-group">
                {!! Form::label('host', 'Kora 2 Database Host') !!}
                {!! Form::text('host','', ['class' => 'text-input db-host-js', 'placeholder' => 'Enter DB Host']) !!}
            </div>

            <div class="form-group mt-xl">
                {!! Form::label('name', 'Kora 2 Database Name (Default Schema)') !!}
                {!! Form::text('name','', ['class' => 'text-input db-name-js', 'placeholder' => 'Enter DB Name']) !!}
            </div>

            <div class="form-group mt-xl">
                {!! Form::label('user', 'Kora 2 Database User') !!}
                {!! Form::text('user','', ['class' => 'text-input db-user-js', 'placeholder' => 'Enter DB User']) !!}
            </div>

            <div class="form-group mt-xl">
                {!! Form::label('pass', 'Kora 2 Database Password') !!}
                {!! Form::password('pass', ['class' => 'text-input db-pass-js', 'placeholder' => 'Enter DB Password']) !!}
            </div>

            <div class="form-group mt-xl">
                {!! Form::label('filePath', trans('exodus_index.files').': ') !!}
                {!! Form::text('filePath','', ['class' => 'text-input file-path-js', 'placeholder' => '/{system_path}/{Kora2}/files']) !!}
            </div>

            <div class="form-group mt-xxxl">
                <input type="button" class="btn secondary get-projects-js" value="Analyze System">
            </div>
        </section>

        <section class="exodus-projects hidden">
            <div class="form-group">
                <label for="users">Migrate System Users?</label>
                <div class="check-box">
                    <input type="checkbox" value="1" class="check-box-input" name="users" />
                    <div class="check-box-background"></div>
                    <span class="check"></span>
                    <span class="placeholder">Set exodus to migrate Kora 2 Users</span>
                    <span class="placeholder-alt">Exodus will migrate Kora 2 Users</span>
                </div>
            </div>

            <div class="form-group mt-xl">
                <label for="tokens">Migrate System Tokens?</label>
                <div class="check-box">
                    <input type="checkbox" value="1" class="check-box-input" name="tokens" />
                    <div class="check-box-background"></div>
                    <span class="check"></span>
                    <span class="placeholder">Set exodus to migrate Kora 2 Tokens</span>
                    <span class="placeholder-alt">Exodus will migrate Kora 2 Tokens</span>
                </div>
            </div>

            <div class="form-group mt-xl">
                {!! Form::label('projects', 'Select Project(s) to Migrate') !!}
                {!! Form::select('projects[]',array(),null,['class' => 'multi-select project-select-js', 'Multiple', 'data-placeholder' => 'Select Projects']) !!}
            </div>

            <div class="form-group mt-xxxl">
                <input type="submit" class="btn set-disabled-js" value="Begin Transfer">
            </div>
        </section>
    {!! Form::close() !!}
@stop

@section('javascripts')
    @include('partials.exodus.javascripts')

    <script type="text/javascript">
        var getProjectListUrl = '{{action('ExodusController@getProjectList')}}';
        var CSRFToken = '{{ csrf_token() }}';

        Kora.Exodus.Index();
    </script>
@stop
