@extends('app', ['page_title' => 'Activate', 'page_class' => 'activate'])

@section('body')
  <div class="content">
    <div class="form-container center">
      <section class="head">
        <h1 class="title">This account has been disabled!</h1>
		@if (Auth::user())
			<h2 class="sub-title">Cannot access account at this time</h2>
		@endif
        <p class="description">Please contact your administrator to have this account reactivated</p>
      </section>
    </div>
  </div>
@stop
