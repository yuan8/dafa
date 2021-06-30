<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Validator;
use Alert;
use Illuminate\Http\Request;
use Auth;
use Hash;
use DB;
use App\Models\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request){

            $valid=Validator::make($request->all(),[
                'email'=>'required|string',
                'password'=>'required|string'
            ]);

            if($valid->fails()){
                Alert::error('','Login Gagal');
                return back()->withInput()->withError();
            }

      
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $agent=User::where('email',$request->email)->orWhere('username',$request->email)->first();


        if($agent){
            if($agent->deleted_at==null){
                 if(Hash::check($request->password,$agent->password)){
                    if($agent->is_active){
                        Auth::login($agent);
                        return $this->sendLoginResponse($request);

                    }else{
                        Alert::error('','User Tidak Diaktivasi');

                    }
                   

                }else{
                Alert::error('','Password Salah');

                }

            }else{
                Alert::error('','User Tidak Aktif');
            }
           
        }

        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);

        
    }
}
