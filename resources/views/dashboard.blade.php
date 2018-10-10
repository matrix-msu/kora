@extends('app', ['page_title' => 'Dashboard', 'page_class' => 'dashboard'])

@section('aside-content')
  @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => true, 'openProjectDrawer' => false])
@stop

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-dashboard"></i>
                <span>Your Dashboard</span>
            </h1>
            <div class="content-sections">
                <div class="content-sections-scroll">
                    <a href="#" class="content create-block-js">
                        <i class="icon icon-block-add"></i>
                        <span>Add a New Block</span>
                    </a>
                    <a href="#" class="content">
                        <i class="icon icon-edit"></i>
                        <span>Edit your Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
@stop

@section('body')
    @include('partials.projects.notification')
    @include("partials.dashboard.modals")
    <div class="floating-buttons">
        <div class="form-group">
            <a class="btn dot-btn create-block-js">
                <i class="icon icon-block-add"></i>
            </a>
        </div>
        <div class="form-group">
            <a class="btn dot-btn">
                <i class="icon icon-edit"></i>
            </a>
        </div>
    </div>
    @foreach($sections as $section)
        <section class="grid">
            <h1 class="header">
                <span class="left title">{{ $section['title'] }}</span>
                <div class="line-container">
                    <span class="line"></span>
                </div>
            </h1>
            <div class="container">
                @foreach($section['blocks'] as $block)
                    @if($block["type"]=="Project")
                        <div class="element">
                            <div class="title-container">
                                <i class="icon icon-project"></i>
                                <a class="name underline-middle-hover"
                                   href="{{ action('ProjectController@show',['pid' => $block['pid']]) }}">
                                    <span>{{ $block['name'] }}</span>
                                    <i class="icon icon-arrow-right"></i>
                                </a>
                            </div>
                            <p class="description">
                                {{ $block['description'] }}
                            </p>
                            <div class="element-link-container">
                                @foreach($block['displayedOpts'] as $link)
                                    <a href="{{ $link['href'] }}" class="element-link tooltip"
                                       tooltip="{{ $link['tooltip'] }}">
                                        <i class="icon {{ $link['icon-class']}}"></i>
                                    </a>
                                @endforeach
                                <a href="#" class="element-link right options-modal-js">
                                    <i class="icon icon-more"></i>
                                </a>
                            </div>
                        </div>
                    @elseif($block["type"]=="Form")
                        <div class="element">
                            <div class="title-container">
                                <i class="icon icon-form"></i>
                                <a class="name underline-middle-hover"
                                   href="{{ action('FormController@show',['pid' => $block['pid'],'fid' => $block['fid']]) }}">
                                    <span>{{ $block['name'] }}</span>
                                    <i class="icon icon-arrow-right"></i>
                                </a>
                            </div>
                            <p class="description fp-style">
                                <span class="fp-header">Project: </span>{{ $block['projName'] }}
                            </p>
                            <p class="description form-desc">
                                {{ $block['description'] }}
                            </p>
                            <div class="element-link-container">
                                @foreach($block['displayedOpts'] as $link)
                                    <a href="{{ $link['href'] }}" class="element-link tooltip"
                                       tooltip="{{ $link['tooltip'] }}">
                                        <i class="icon {{ $link['icon-class']}}"></i>
                                    </a>
                                @endforeach
                                <a href="#" class="element-link right options-modal-js">
                                    <i class="icon icon-more"></i>
                                </a>
                            </div>
                        </div>
                    @elseif($block["type"]=="Record")
                        <div class="element">
                            <div class="title-container">
                                <i class="icon icon-form"></i>
                                <a class="name underline-middle-hover"
                                   href="{{ action('RecordController@show',['pid' => $block['pid'],'fid' => $block['fid'], 'rid' => $block['rid']]) }}">
                                    <span>{{ $block['kid'] }}</span>
                                    <i class="icon icon-arrow-right"></i>
                                </a>
                            </div>
                            <p class="description fp-style">
                                <span class="fp-header">Project: </span>{{ $block['projName'] }}
                            </p>
                            <p class="description fp-style">
                                <span class="fp-header">Form: </span>{{ $block['formName'] }}
                            </p>
                            <p class="description fp-style">
                                <span class="fp-header">{{ $block['fieldName'] }}: </span>{{ $block['fieldData'] }}
                            </p>
                            <div class="element-link-container">
                                @foreach($block['displayedOpts'] as $link)
                                    <a href="{{ $link['href'] }}" class="element-link tooltip"
                                       tooltip="{{ $link['tooltip'] }}">
                                        <i class="icon {{ $link['icon-class']}}"></i>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @elseif($block["type"]=="Quote")
                        <div class="element">
                            <div class="title-container">
                                <span class="no-link-no-icon">Today's Inspiration</span>
                            </div>
                            <p class="description quote-text">
                                {{ $block['quote'] }}
                            </p>
                            <p class="description quote-author">
                                {{ $block['author'] }}
                            </p>
                        </div>
                    @elseif($block["type"]=="Note")
                        <div class="element">
                            <div class="title-container">
                                <span class="no-link-no-icon">{{ $block['title'] }}</span>
                            </div>
                            <p class="description note-desc">
                                {{ $block['content'] }}
                            </p>
                        </div>
                    @elseif($block["type"]=="Twitter")
                        <div class="element">
                            <div class="title-container">
                                <span class="no-link-no-icon">Kora Twitter</span>
                            </div>
                            <p class="description note-desc">
                                Coming soon...
                            </p>
                        </div>
                    @endif
                @endforeach
            </div>
        </section>
    @endforeach
@stop

@section('javascripts')
    @include('partials.dashboard.javascripts')

    <script>
        var validationUrl = '{{ action('DashboardController@validateBlockFields') }}';
        var state = {{$state}};

        Kora.Dashboard.Index();
    </script>
@stop
