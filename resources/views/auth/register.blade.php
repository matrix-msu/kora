@extends('app', ['page_title' => 'Register', 'page_class' => 'register'])

@section('body')
<div class="container-fluid">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">{{trans('auth_register.register')}}</div>
				<div class="panel-body">
					@if (count($errors) > 0)
						<div class="alert alert-danger">
							<strong>{{trans('auth_register.whoops')}}!</strong> {{trans('auth_register.problems')}}.<br><br>
							<ul>
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif

					<form class="form-horizontal" role="form" method="POST" enctype="multipart/form-data" action="{{ url('/auth/register') }}">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="regtoken" value="{{\App\Http\Controllers\Auth\AuthController::makeRegToken()}}">

						<div class="form-group">
							<label class="col-md-4 control-label">{{trans('auth_register.user')}}</label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="username" value="{{ old('username') }}">
							</div>
						</div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">Profile Picture</label>
                            <div class="col-md-6">
                                <input type="file" accept=".jpeg,.png,.bmp,.gif,.jpg" class="form-control" name="profile" value="profile">
                            </div>
                        </div>

						<div class="form-group">
							<label class="col-md-4 control-label">{{trans('auth_register.email')}}</label>
							<div class="col-md-6">
								<input type="email" class="form-control" name="email" value="{{ old('email') }}">
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">{{trans('auth_register.pass')}}</label>
							<div class="col-md-6">
								<input type="password" class="form-control" name="password">
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">{{trans('auth_register.confirmpass')}}</label>
							<div class="col-md-6">
								<input type="password" class="form-control" name="password_confirmation">
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-md-4 control-label">First name</label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="first_name" value="{{ old('first_name') }}">
							</div>
						</div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">Last name</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="last_name" value="{{ old('last_name') }}">
                            </div>
                        </div>
						
						<div class="form-group">
							<label class="col-md-4 control-label">{{trans('auth_register.org')}}</label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="organization" value="{{ old('organization') }}">
							</div>
						</div>
						{{--
						<div class="form-group">
							<label class="col-md-4 control-label">Language</label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="language" value="{{ App::getLocale() }}">
							</div>
						</div> --}}

                        <div class="form-group">
                            <label class="col-md-4 control-label">{{trans('auth_register.language')}}</label>
                            <div class="col-md-6">
                                <select name="language" class="form-control">
                                    {{$languages_available = Config::get('app.locales_supported')}}
                                    @foreach($languages_available->keys() as $lang)
                                        <option value='{{$languages_available->get($lang)[0]}}'>{{$languages_available->get($lang)[1]}} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div style="padding: 5px" align="center" class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_PUBLIC_KEY') }}"></div>
                        </div>


						<div class="form-group" >
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">
									{{trans('auth_register.register')}}
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@stop

