<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Hash;  //to create hash of password
use Session;
use Illuminate\Support\Facades\Auth;   // to use authentication services
use App\Models\User;
use Illuminate\Support\Str;
use DB;
use Carbon\Carbon; 
use Illuminate\Support\Facades\Mail;

class CustomAuthController extends Controller
{
    // it will load login page
    public function index()
    {
        return view("Auth.login");
    }
    public function forgotpassword(){
        return view("Auth.forgot_password");
    }
    //validate login credentials and do authentication
    public function customlogin(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:5',

        ]);
        $credentials = $request->only('email', 'password');
        // only method will convert this values into associative array i.e "email"=>"examle@gmail.com" 

        if (AUTH::attempt($credentials)) {
            // attempt method will check in database weather given credentials match or not it takes associative array as a input
            return redirect()->intended('dashboard')->withSuccess('You Are Login Successfull!');
            //redirect the user to the URL they were attempting to access before being intercepted by the authentication middleware
        }
        return redirect("login")->withErrors('Login details are not valid');
    }

    // load registration page
    public function registration()
    {
        return view("Auth.registration");
    }

    //validate registration details
    public function customRegistration(Request $request)
    {
        $request->validate([
            'name' => 'required',
            // 'mobileno' => 'required',
            'email' => 'required|email|unique:users',
            // 'password' => 'required|confirmed|min:6',
            'password' => 'required',
        ]);

        $data = $request->all();
        $check = $this->create($data);

        return redirect("dashboard")->withSuccess('You have signed-in');
    }
    public function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            // 'lastname' =>'required',
            // 'mobileno' => 'required|digits:10',
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);
    }
    public function dashboard()
    {
        //check weather a user is authenticated or not
        if (Auth::check()) {
            return view('index');
        }

        return redirect("login")->withErrors('You are not allowed to access');
    }

    //logout 
    public function signOut()
    {
        Session::flush();
        Auth::logout();

        return Redirect('login');
    }

    // forgot password methods

    public function submitForgetPasswordForm(Request $request)
      {
        
          $request->validate([
              'email' => 'required|email|exists:users',
          ]);
            
          $token = Str::random(64);
  
          DB::table('password_resets')->insert([
              'email' => $request->email, 
              'token' => $token, 
              'created_at' => Carbon::now()
            ]);
  
          Mail::send('email.forgetPasswordlink', ['token' => $token], function($message) use($request){
              $message->to($request->email);
              $message->subject('Reset Password');
          });
  
          return back()->with('message', 'We have e-mailed your password reset link!');
      }

      public function showResetPasswordForm($token) { 
         return view('Auth.reset_password', ['token' => $token]);
      }

      public function submitResetPasswordForm(Request $request)
      {
          $request->validate([
              'email' => 'required|email|exists:users',
              'password' => 'required|string|min:6|confirmed',
              'password_confirmation' => 'required'
          ]);
  
          $updatePassword = DB::table('password_resets')
                              ->where([
                                'email' => $request->email, 
                                'token' => $request->token
                              ])
                              ->first();
  
          if(!$updatePassword){
              return back()->withInput()->with('error', 'Invalid token!');
          }
  
          $user = User::where('email', $request->email)
                      ->update(['password' => Hash::make($request->password)]);
 
          DB::table('password_resets')->where(['email'=> $request->email])->delete();
  
          return redirect('/login')->with('message', 'Your password has been changed!');
      }
}
