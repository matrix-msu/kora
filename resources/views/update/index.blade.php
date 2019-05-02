@extends('app', ['page_title' => 'Update Kora3', 'page_class' => 'update'])

@section('aside-content')
    <?php $openManagement = true ?>
    @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => false, 'openProjectDrawer' => false])
@stop

@section('header')
    <section class="head">
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
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
                @if($ready)
                    <p class="description">It looks like your file set is up to date. Please run the 'php artisan kora3:update' command to complete
                        your installation update. The new update is detailed below.</p>
                @else
                    <p class="description">Please update your installation via 'git pull'. If you manually
                        installed Kora3, visit <a href="https://github.com/matrix-msu/Kora3">Github</a> to download and merge the latest release
                        file set. Once this is done run the 'php artisan kora3:update' command to complete your installation update. The new update is detailed
                        below.</p>
                @endif
            @else
                <p class="description">Your installation is up to date with the latest features! You may review the patch notes below.</p>
            @endif
        </div>
    </section>
@stop

@section('body')
    <section class="update-text center">
        <div class="update-version">KORA {{$info['version']}}</div>
        <div class="update-notes mt-xl mb-100-xl">
            <div class="note-header">Update Notes:</div>
            <div class="mt-m">{{$info['notes']}}</div>
            <div class="mt-m">New Features:</div>
            <ul class="mt-m">
                @foreach($info['features'] as $feature)
                    <li>{{$feature}}</li>
                @endforeach
            </ul>
            <div class="mt-m">Bug Fixes:</div>
            <ul class="mt-m">
                @foreach($info['bugs'] as $bug)
                    <li>{{$bug}}</li>
                @endforeach
            </ul>
        </div>
    </section>
@stop

@section('javascripts')
    @include('partials.update.javascripts')
@stop

