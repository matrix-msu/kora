@extends('app', ['page_title' => 'Tokens', 'page_class' => 'tokens'])

@section('aside-content')
  <?php $openManagement = true ?>
  @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => false, 'openProjectDrawer' => false])
@stop

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-token"></i>
                <span>Token Management</span>
            </h1>
            <p class="description">Brief info on user Token management, followed by instructions on how to use the
                token management page will go here.</p>
        </div>
    </section>
@stop

@section('body')
    @include("partials.tokens.modals")

    @if ($tokens->count() > 0)
      <section class="filters center">
          <div class="underline-middle search search-js">
              <i class="icon icon-search"></i>
              <input type="text" placeholder="Find a Token">
              <i class="icon icon-cancel icon-cancel-js"></i>
          </div>
          <div class="sort-options sort-options-js">
              <a href="#all" class="option underline-middle underline-middle-hover active">All</a>
              <a href="#search" class="option underline-middle underline-middle-hover">Search</a>
              <a href="#create" class="option underline-middle underline-middle-hover">Create</a>
              <a href="#edit" class="option underline-middle underline-middle-hover">Edit</a>
              <a href="#delete" class="option underline-middle underline-middle-hover">Delete</a>
          </div>
      </section>
    @endif

    <section class="new-object-button center">
        <input type="button" value="Create New Token" class="create-token-js">
    </section>

    <section class="token-selection center token-js token-selection-js">
        <div class="token-sort token-sort-js active">
          @if ($tokens->count() > 0)
            @foreach($tokens as $index => $token)
                @include("partials.tokens.index")
            @endforeach
          @else
            @include("partials.tokens.no-tokens")
          @endif
        </div>
    </section>
@stop

@section('javascripts')
    @include('partials.tokens.javascripts')

    <script type="text/javascript">
        var CSRFToken = '{{ csrf_token() }}';
        var unProjectUrl = '{{ action('TokenController@getUnassignedProjects') }}';
		var create_url = '{{action('TokenController@create')}}';
        Kora.Tokens.Index();
    </script>
@stop
