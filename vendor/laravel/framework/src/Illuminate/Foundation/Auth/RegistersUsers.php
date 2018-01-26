<?php

namespace Illuminate\Foundation\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use ReCaptcha\ReCaptcha;

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
        //CUSTOM CODE FOR RECAPTCHA
        $recaptcha = new ReCaptcha(env('RECAPTCHA_PRIVATE_KEY'));
        $resp = $recaptcha->verify($request['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

        if($resp->isSuccess());
        else{
            flash()->overlay('ReCAPTCHA incomplete!', 'Whoops.');

            $validator = $this->validator($request->all());
            $this->throwValidationException($request, $validator);
        }
        //END CUSTOM

        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        //CUSTOM CODE FOR EMAIL ACTIVATION
        $token = \Auth::user()->token;

        //CUSTOM CODE FOR SAVING PROFILE
        if( !is_null($request->file('profile')) ) {
            //get the file object
            $file = $request->file('profile');
            $filename = $file->getClientOriginalName();
            //path where file will be stored
            $destinationPath = env('BASE_PATH') . 'storage/app/profiles/'.\Auth::user()->id.'/';
            //store filename in user model
            \Auth::user()->profile = $filename;
            \Auth::user()->save();
            //move the file
            $file->move($destinationPath,$filename);
        }

        Mail::send('emails.activation', compact('token'), function($message)
        {
            $message->from(env('MAIL_FROM_ADDRESS'));
            $message->to(\Auth::user()->email);
            $message->subject('Kora Account Activation');
        });
        //END CUSTOM

        return $this->registered($request, $user)
                        ?: redirect($this->redirectPath());
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
