@extends('app', ['page_title' => 'Update Kora3', 'page_class' => 'update'])

@section('aside-content')
    <?php $openManagement = true ?>
    @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => false, 'openProjectDrawer' => false])
@stop

@section('header')
    <section class="head">
        <a class="back" href="{{ URL::previous() }}"><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-update"></i>
                @if($update)
                    <span>New Update Available!</span>
                @else
                    <span>Kora3 is Up-to-date!</span>
                @endif
            </h1>
            @if($update)
            <p class="description">Before beginning, update your installation via 'git update'. If you manually
                installed Kora3, visit <a href="https://github.com/matrix-msu/Kora3">Github</a> to download and merge the latest release
                file set. Once this is done select “Update Kora” to update your installation. The new update is detailed
                below. Once the update begins, leave the page open until completion.</p>
            @else
            <p class="description">Your installation is up to date with the latest features! You may review the patch notes below.</p>
            @endif
        </div>
    </section>
@stop

@section('body')
    <section class="update-text center">
        <div class="update-version">KORA {{$currVer}}</div>
        @if($update)
            <div class="update-notes">{!! $notes !!}</div>
            <div class="form-group update-button">
                @if($ready)
                    <form method="get" id="update_form" action={{action("UpdateController@runScripts")}}>
                        {!! Form::submit("Update Kora to Version $currVer",['class' => 'btn edit-btn update-submit pre-fixed-js']) !!}
                    </form>
                @else
                    {!! Form::submit('Must update Kora3 file set first.',['class' => 'btn edit-btn update-submit pre-fixed-js disabled']) !!}
                @endif
            </div>
        @else
            <div class="update-notes mb-100-xl">{!! $notes !!}</div>
        @endif
    </section>
@stop

@section('javascripts')
    @include('partials.update.javascripts')
@stop

