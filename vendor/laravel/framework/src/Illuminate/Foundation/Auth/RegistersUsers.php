<?php

namespace Illuminate\Foundation\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;

trait RegistersUsers
{
    use RedirectsUsers;

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        if(!\App\User::verifyRegisterRecaptcha($request)) {
            $notification = array(
                'message' => 'ReCaptcha validation error',
                'description' => '',
                'warning' => true,
                'static' => true
            );

            return redirect("/register")->withInput()->with('notification', $notification)->send();
        }

        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        if(\App\User::finishRegistration($request))
            $status = 'activation_email_sent';
        else
            $status = 'activation_email_failed';


        return $this->registered($request, $user)
                        ?: redirect($this->redirectPath())->with('status', $status);
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        //
    }
}
