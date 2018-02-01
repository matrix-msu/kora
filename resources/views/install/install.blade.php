@extends('app',['page_title' => 'Kora Installation', 'page_class' => 'install'])

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title no-icon">
                <span>Kora 3 Initialization Form</span>
            </h1>
            <p class="description">Fill out the following forms to fully initialize Kora 3</p>
            <div class="content-sections">
                <a href="#database" class="section underline-middle underline-middle-hover toggle-by-name active">Database</a>
                <a href="#admin" class="section underline-middle underline-middle-hover toggle-by-name">Admin</a>
                <a href="#mail" class="section underline-middle underline-middle-hover toggle-by-name">Mail</a>
                <a href="#recaptcha" class="section underline-middle underline-middle-hover toggle-by-name">Recaptcha</a>
                <a href="#base" class="section underline-middle underline-middle-hover toggle-by-name">Base</a>
            </div>
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

