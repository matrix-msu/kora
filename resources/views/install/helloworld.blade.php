@extends('app', ['page_title' => 'Welcome to Kora', 'page_class' => 'welcome-fresh'])

@section('body')
    <div class="content-install">
        <div class="form-container py-100-xl ma-auto">
            <div>
                <img src="{{ url('assets/logos/koraiii-logo-blue.svg') }}">
            </div>

            <div class="kora3 mt-xxl">
                Kora 3
            </div>

            <div class="ready mt-xxl">
                Ready for Initialization
            </div>

            <div class="commander mt-m">
                We are ready to begin the Kora Initialization sequence, Commander. It looks like you still need to run
                the PHP ARTISAN command for completing the installation process. I recommend reviewing the handy-dandy
                <a href="https://github.com/matrix-msu/Kora3">Installation Guide</a>!
            </div>
        </div>
    </div>
@stop
