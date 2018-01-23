<?php

namespace Illuminate\Foundation\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    public function getRegister()
    {
        return $this->showRegistrationForm();
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        if (property_exists($this, 'registerView')) {
            return view($this->registerView);
        }

        return view('auth.register');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postRegister(Request $request)
    {
        return $this->register($request);
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

        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            $this->throwValidationException(
                $request, $validator
            );
        }

        Auth::guard($this->getGuard())->login($this->create($request->all()));

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

        return redirect($this->redirectPath());
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return string|null
     */
    protected function getGuard()
    {
        return property_exists($this, 'guard') ? $this->guard : null;
    }
}
