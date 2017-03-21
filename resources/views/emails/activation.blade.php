{{trans('emails_activation.welcome')}} Kora 3! <br/>
{{trans('emails_activation.clickhere')}}: <a href="{{action('Auth\UserController@activate', ['token' => \Auth::user()->regtoken])}}">{{trans('emails_activation.activate')}}</a>. <br/>
{{trans('emails_activation.token')}}: {{\Auth::user()->regtoken}}.