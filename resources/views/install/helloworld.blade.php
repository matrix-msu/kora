@extends('app', ['page_title' => 'Welcome to kora', 'page_class' => 'welcome-fresh'])

@section('body')
    <div class="content-install">
        <div class="form-container py-100-xl ma-auto">
            <img class="logo" src="{{ url('assets/logos/logo_green_text_dark.svg') }}">

            <div class="ready mt-xxl">
                Ready for Initialization
            </div>

            <div class="commander mt-m">
                We are ready to begin the kora Initialization sequence, Commander.
                Ready when you are.
            </div>

            @if(file_exists(base_path('.env')))
                <form class="form-horizontal" role="form" method="GET" action="{{ url('/install') }}">
                    <div class="form-group mt-xxl">
                        <button type="submit" class="btn btn-primary">Begin Initialization Sequence</button>
                    </div>
                </form>
            @else
                <form class="form-horizontal" role="form" method="GET" action="{{ url('/helloworld') }}">
                    <div class="form-group mt-xxl">
                        <button type="submit" class="btn btn-primary disabled">Copy the ENV example first, then come back!</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@stop
