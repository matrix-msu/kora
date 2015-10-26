Welcome to Kora 3! <br/>
Click here to activate your account: <a href="{{action('Auth\UserController@activate', ['token' => \Auth::user()->regtoken])}}">Activate</a>. <br/>
Or use this token on the activation page (case sensitive): {{\Auth::user()->regtoken}}.