<?php

namespace App\Http\Controllers\Auth;

use App\Commands\UserEmails;
use App\Http\Requests\UserRequest;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use ReCaptcha\ReCaptcha;

class RegisterController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('guest');
    }

    /**
     * Override of function in the use class above, RegistersUsers. Handle a registration request for the application.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        if(!self::verifyRegisterRecaptcha($request)) {
            $notification = array(
                'message' => 'ReCaptcha validation error',
                'description' => '',
                'warning' => true,
                'static' => true
            );

            return redirect("/register")->withInput()->with('notification', $notification)->send();
        }

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        if(self::finishRegistration($request))
            $status = 'activation_email_sent';
        else
            $status = 'activation_email_failed';

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 201)
            : redirect($this->redirectPath())->with('status', $status);
    }

    /**
     * Verifies recaptcha token on register. Happens in registration before we verify the other User request data.
     *
     * @param  Request $request - The registration request data
     */
    public static function verifyRegisterRecaptcha($request) {
        $recaptcha = new ReCaptcha(config('auth.recap_private'));
        $resp = $recaptcha->verify($request['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

        if($resp->isSuccess())
            return true;
        else
            return false;
    }

    /**
     * Finishes the registration process by submitting user photo and sending activation email. Happens in registration
     * right after logging in the newly created user.
     *
     * @param  Request $request - The registration request data
     * @return bool - Success of activation email
     */
    public static function finishRegistration($request) {
        $user = \Auth::user();
        $token = $user->token;
        $preferences = array();

        //Metadata stuff
        $preferences['first_name'] = $request->first_name;
        $preferences['last_name'] = $request->last_name;
        $preferences['organization'] = $request->organization;
        $preferences['language'] = 'en';

        //Profile picture
        if(!is_null($request->file('profile'))) {
            //get the file object
            $file = $request->file('profile');
            $filename = $file->getClientOriginalName();
            //path where file will be stored
            $destinationPath = storage_path('app/profiles/'.$user->id.'/');
            //store filename in user model
            $preferences['profile_pic'] = $filename;
            //move the file
            $file->move($destinationPath,$filename);
        } else {
            $preferences['profile_pic'] = '';
        }

        //Assign new user preferences
        $preferences['use_dashboard'] = 1;
        $preferences['logo_target'] = 2;
        $preferences['proj_tab_selection'] = 2;
        $preferences['form_tab_selection'] = 2;
        $preferences['onboarding'] = 1;
        $user->preferences = $preferences;
        $user->save();

        //Send email
        $job = new UserEmails('UserActivationRequest', ['token' => $token, 'email' => $user->email]);
        $job->handle();

        return true;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data) {
        return Validator::make($data, [
            'username' => 'required|max:255|unique:users', //Check to not contain 'a'
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
            'language'=> 'required|alpha|max:2',
            'first_name'=> 'required',
            'last_name'=> 'required',
            'organization'=> 'required',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)  {
        return User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'regtoken' => $data['regtoken']
        ]);
    }

    /**
     * Validates a new user model.
     *
     * @return JsonResponse
     */
    public function validateUserFields(UserRequest $request) {
        return response()->json(["status"=>true, "message"=>"User Valid", 200]);
    }

    /**
     * Generates a registration token for a new user
     *
     * @return string - The token
     */
    public static function makeRegToken() {
        $valid = 'abcdefghijklmnopqrstuvwxyz';
        $valid .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $valid .= '0123456789';

        $token = '';
        for($i = 0; $i < 31; $i++) {
            $token .= $valid[( rand() % 62 )];
        }

        return $token;
    }
}
