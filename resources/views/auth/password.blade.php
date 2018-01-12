@extends('app', ['page_title' => 'Reset Password', 'page_class' => 'resetp'])

@section('body')
<div class="content">
  <div class="form-container py-100-xl ma-auto">
		@if (session('status'))
			<div class="alert alert-success">
				{{ session('status') }}
			</div>
		@endif

		<section class="head">
      <h1 class="title text-center">Reset Your Password</h1>
    </section>

		@if (count($errors) > 0)
			<div class="alert alert-danger">
				<strong>{{trans('auth_password.whoops')}}!</strong> {{trans('auth_password.problems')}}.<br><br>
				<ul>
					@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif

		<form class="form-horizontal" role="form" method="POST" action="{{ url('/password/email') }}">
			<input type="hidden" name="_token" value="{{ csrf_token() }}">

			<div class="form-group mt-xl">
				<label for="email">Your E-mail Address</label>
				<input type="email" class="text-input" name="email" value="{{ old('email') }}" placeholder="Enter your email here">
			</div>

			<div class="form-group mt-xl">
				<button type="submit" class="btn btn-primary">Send Password Reset Link</button>
			</div>
		</form>
	</div>
</div>
@endsection
