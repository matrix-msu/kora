@extends('app', ['page_title' => 'Reset Password', 'page_class' => 'reset'])

@section('content')
<div class="content">
  <div class="form-container py-100-xl ma-auto">
    <section class="head">
			<h1 class="title text-center">Enter your new password</h1>
		</section>

		@if (count($errors) > 0)
			<div class="alert alert-danger">
				<strong>{{trans('auth_reset.whoops')}}!</strong> {{trans('auth_reset.problems')}}.<br><br>
				<ul>
					@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif

		<form class="form-horizontal" role="form" method="POST" action="{{ url('/password/reset') }}">
			<input type="hidden" name="_token" value="{{ csrf_token() }}">
			<input type="hidden" name="token" value="{{ $token }}">

			<div class="form-group">
				<label for="email">Enter Your Email</label>
				<input type="email" class="text-input" name="email" value="{{ old('email') }}">
			</div>

			<div class="form-group">
				<label for="password">Enter New Password</label>
				<input type="password" class="text-input" name="password">
			</div>

			<div class="form-group">
				<label for="password_confirmation">Confirm New Password</label>
				<input type="password" class="text-input" name="password_confirmation">
			</div>

			<div class="form-group">
				<button type="submit" class="btn btn-primary">Set New Password</button>
			</div>
		</form>
	</div>
</div>
@endsection
