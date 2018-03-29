{{trans('emails_batch-activation.welcome')}} Kora 3! <br/>
{{trans('emails_batch-activation.clickhere')}}: <a href="{{action('Auth\UserController@activate', ['token' => $token])}}">{{trans('emails_batch-activation.activate')}}</a>. <br/>
{{trans('emails_batch-activation.token')}}: {{$token}}. <br/>
{{trans('emails_batch-activation.user')}}: {{$username}}. <br/>
{{trans('emails_batch-activation.pass')}}: {{$password}}.  {{trans('emails_batch-activation.temp')}}!<br/>
{{$personal_message}}
