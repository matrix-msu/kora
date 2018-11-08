@extends('app',['page_title' => 'Kora Installation', 'page_class' => 'install'])

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title no-icon">
                <span>Kora 3 Initialization Form</span>
            </h1>
            <p class="description">Fill out the following forms to fully initialize Kora 3</p>
            <div class="content-sections">
              <div class="content-sections-scroll">
                <a href="#database" class="database-link section underline-middle underline-middle-hover toggle-by-name active">Database</a>
                <a href="#admin" class="admin-link section underline-middle underline-middle-hover toggle-by-name">Admin</a>
                <a href="#mail" class="mail-link section underline-middle underline-middle-hover toggle-by-name">Mail</a>
                <a href="#recaptcha" class="recaptcha-link section underline-middle underline-middle-hover toggle-by-name">Recaptcha</a>
              </div>
            </div>
        </div>
    </section>
@stop

@section('body')
    <section class="install-form center">
        <form method="post" id="install_form" enctype="multipart/form-data" action={{action("InstallController@installPartTwo")}}>
            @include('partials.install.form')
        </form>
    </section>

    <section class="pagination center">
        <div class="previous page disabled">
            <a href="#">
                <i class="icon icon-chevron left previous-page-js"></i>
                <span class="name underline-middle-hover">Previous Page</span>
            </a>
        </div>
        <div class="pages">
            <a href="#database" class="page-link database-link active">1</a>
            <a href="#admin" class="page-link admin-link">2</a>
            <a href="#mail" class="page-link mail-link">3</a>
            <a href="#recaptcha" class="page-link recaptcha-link">4</a>
        </div>
        <div class="next page">
            <a href="#">
                <i class="icon icon-chevron right next-page-js"></i>
                <span class="name underline-middle-hover">Next Page</span>
            </a>
        </div>
    </section>
@stop

@section('javascripts')
    @include('partials.install.javascripts')

    <script type="text/javascript">
        var installPartOneURL = '{{action('InstallController@install')}}';

        Kora.Install.Create();
    </script>
@stop

