<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class LoginBasic extends Controller
{
  public function index()
  {
    $user = DB::table('users')->first();
    if (!$user) {
      DB::table('users')
        ->insert([
          
        ]);
    }
    return view('content.authentications.auth-login-basic');
  }

  public function login(Request $request)
  {
      $credentials = $request->validate([
          'username' => ['required'],
          'password' => ['required'],
      ]);

      if (Auth::attempt($credentials)) {
          $request->session()->regenerate();
          return redirect()->intended('/');
      }

      return back()->withErrors([
          'username' => 'Login gagal.',
      ]);
  }
}
