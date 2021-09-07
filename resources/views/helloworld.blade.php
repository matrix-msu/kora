@extends('app', ['page_title' => 'Welcome to kora', 'page_class' => 'welcome-fresh'])

@section('body')
    <div class="content-install">
        <div class="form-container py-100-xl ma-auto">
            <img class="logo" src="{{ url('assets/logos/logo_green_text_dark.svg') }}">

            <div class="ready mt-xxl">
                No Database Connection Available
            </div>

            <div class="commander mt-m">
                Please contact your system administrator to have the database connection restored.
            </div>

            <div class="commander mt-m">
                If Kora is not installed, please refer to the <a target="_blank" class="text underline-middle-hover" href="https://github.com/matrix-msu/kora">Kora Github page</a>
                to complete the installation process.
            </div>
        </div>
    </div>
@stop
