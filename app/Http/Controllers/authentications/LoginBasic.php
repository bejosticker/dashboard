<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginBasic extends Controller
{
  public function index()
  {
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
