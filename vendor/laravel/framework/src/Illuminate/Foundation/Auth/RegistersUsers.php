<?php

namespace Illuminate\Foundation\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ReCaptcha\ReCaptcha;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;

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
        $recaptcha = new ReCaptcha(env('RECAPTCHA_PRIVATE_KEY'));
        $resp = $recaptcha->verify($request['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

        if($resp->isSuccess());
        else{
            flash()->overlay('ReCAPTCHA incomplete!', 'Whoops.');

            $validator = $this->validator($request->all());
            $this->throwValidationException($request, $validator);
        }


        $validator = $this->validator($request->all());

        if ($validator->fails())
        {
            $this->throwValidationException(
                $request, $validator
            );
        }

        Auth::login($this->create($request->all()));

        //This will not error because of the statement above.
        $token = \Auth::user()->token;

        //save profile pic
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


        return redirect($this->redirectPath());
    }

    public static function makeRegToken(){
        $valid = 'abcdefghijklmnopqrstuvwxyz';
        $valid .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $valid .= '0123456789';

        $token = '';
        for ($i = 0; $i < 31; $i++){
            $token .= $valid[( rand() % 62 )];
        }
        return $token;
    }
}
