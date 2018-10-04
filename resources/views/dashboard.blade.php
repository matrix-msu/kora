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
    @include("partials.dashboard.modals")
  <!-- <php var_dump($sections); ?> -->
  <div class="floating-buttons">
    <div class="form-group">
      <a class="btn dot-btn">
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
      <section class="grid center">
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
                      <a class="name underline-middle-hover" href="{{ action('ProjectController@show',['pid' => $block['pid']]) }}">
                        <span>{{ $block['name'] }}</span>
                        <i class="icon icon-arrow-right"></i>
                      </a>
                    </div>
                    <p class="description">
                      {{ $block['description'] }}
                    </p>
                    <div class="element-link-container">
                      @foreach($block['displayedOpts'] as $link)
                        <a target="_blank" href="{{ $link['href'] }}" class="element-link tooltip" tooltip="{{ $link['tooltip'] }}">
                          <i class="icon {{ $link['icon-class']}}"></i>
                        </a>
                      @endforeach
                      <a target="_blank" href="#" class="element-link right options-modal-js">
                        <i class="icon icon-more"></i>
                      </a>
                    </div>
                  </div>
              @elseif($block["type"]=="Quote")
                    <div class="element">
                        <div class="title-container">
                            <i class="icon icon-project"></i>
                            <span>Today's Inspiration</span>
                        </div>
                        <p class="description">
                            {{ $block['quote'] }}
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
        Kora.Dashboard.Index();
    </script>
@stop
