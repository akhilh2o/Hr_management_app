<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Hash;  //to create hash of password
use Session;
use Illuminate\Support\Facades\Auth;   // to use authentication services
use App\Models\User;

class CustomAuthController extends Controller
{
    // it will load login page
    public function index()
    {
        return view("Auth.login");

    }
    //validate login credentials and do authentication
    public function customlogin(Request $request){

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',

        ]);
        $credentials =$request->only('email','password'); // only method will convert this values into associative array i.e "email"=>"" 

        if(AUTH::attempt($credentials)){  
        // attempt method will check in database weather given credentials match or not it takes associative array as a input
            return redirect()->intended('dashboard')->withSuccess('Signed in');
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
        if(Auth::check()){
            return view('index');
        }
  
        return redirect("login")->withErrors('You are not allowed to access');
    }
    
    //logout 
    public function signOut() {
        Session::flush();
        Auth::logout();
  
        return Redirect('login');
    }   
    
}
