@extends('app', ['page_title' => 'Dashboard', 'page_class' => 'dashboard'])

@section('aside-content')
  @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => true, 'openProjectDrawer' => false])
@stop

@section('content')
    <h1>My Dashboard</h1>

    <hr/>



@stop

@section('footer')
    <script>

    </script>
@stop
