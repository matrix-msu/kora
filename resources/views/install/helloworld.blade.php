@extends('app', ['page_title' => 'Welcome to Kora', 'page_class' => 'welcome-fresh'])

@section('body')
    <div class="content-install">
        <div class="form-container py-100-xl ma-auto">
            <div>
                <img src="{{ config('app.url') }}assets/logos/koraiii-logo-blue.svg">
            </div>

            <div class="kora3 mt-xxl">
                Kora 3
            </div>

            <div class="ready mt-xxl">
                Ready for Initialization
            </div>

            <div class="commander mt-m">
                We are ready to begin the Kora Initialization sequence, Commander.
                Ready when you are.
            </div>

            <form class="form-horizontal" role="form" method="GET" action="{{ url('/install') }}">
                <div class="form-group mt-xxl">
                    <button type="submit" class="btn btn-primary">Begin Initialization Sequence</button>
                </div>
            </form>
        </div>
    </div>
@stop
