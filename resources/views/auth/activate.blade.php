@extends('app', ['page_title' => 'Activate', 'page_class' => 'activate'])

@section('body')
  <div class="content">
    <div class="form-container center">
      <section class="head">
        <h1 class="title">Thanks for Signing Up!</h1>
        <h2 class="sub-title">We've sent an email to {{ Auth::user()->email }}</h2>
        <p class="description">Once you receive the email, hit the "Active Account" button and you'll be all set!</p>
      </section>

      <div class="spacer"></div>

      <form class="form-horizontal" role="form" method="POST" enctype="multipart/form-data" action="{{ action('Auth\UserController@activator') }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="regtoken" value="{{\App\Http\Controllers\Auth\RegisterController::makeRegToken()}}">

        <p>You may also enter the activation token provided in the email to activate your account.</p>
        <div class="form-group mt-xl pr-m">
          <label for="activation-token">Activation Token</label>
          <input type="text" class="text-input" name="activationtoken" placeholder="Enter your activation token here">
        </div>

        <div class="form-group mt-xl" >
          <button type="submit" class="btn btn-primary">Activate Account</button>
        </div>
      </form>

      <div class="spacer"></div>

      <section class="row">
        <div class="half">
          <h2 class="mt-0 mb-xl">Didn't get the email?</h2>
          <p class="mb-xl-responsive">Remember to check your spam folder!</p>
        </div>
        <div class="half">
          <form class="form-horizontal" role="form" method="POST" enctype="multipart/form-data" action="{{ action('Auth\UserController@resendActivation') }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="regtoken" value="{{\App\Http\Controllers\Auth\RegisterController::makeRegToken()}}">

            <div class="form-group" >
              <button type="submit" class="btn secondary">Request Another Email</button>
            </div>
          </form>
        </div>
      </section>

      <div class="spacer"></div>

      <section>
        <h2 class="mt-0 mb-xl">Requesting another email doesn't work?</h2>
        <p>Contact the installation admin at <a class="text underline-middle-hover" href="mailto:FIXME">FIXME</a></p>
      </section>
    </div>
  </div>
@stop
