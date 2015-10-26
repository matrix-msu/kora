Welcome to Kora 3! <br/>
Click here to activate your account: <a href="{{action('Auth\UserController@activate', ['token' => $token])}}">Activate</a>. <br/>
Or use this token on the activation page (case sensitive): {{$token}}. <br/>
Your user name is (case sensitive): {{$username}}. <br/>
Your password is (case sensitive): {{$password}}.  Note this is a temporary password and should be changed!