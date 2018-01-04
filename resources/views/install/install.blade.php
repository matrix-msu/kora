@extends('app',['page_title' => 'Kora Installation', 'page_class' => 'install'])

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title no-icon">
                <span>Kora 3 Initialization Form</span>
            </h1>
            <p class="description">Fill out the following forms to fully initialize Kora 3</p>
        </div>
    </section>
@stop

@section('body')
    <section class="install-form center">
        <form method="post" id="install_form" action={{action("InstallController@installPartTwo")}}>
            @include('partials.install.form')
        </form>
    </section>
@stop

@section('javascripts')
    @include('partials.install.javascripts')

    <script type="text/javascript">
        var installPartOneURL = '{{action('InstallController@install')}}';

        Kora.Install.Create();
    </script>
@stop

