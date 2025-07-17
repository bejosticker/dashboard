<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use DB;

class LoginBasic extends Controller
{
  public function index()
  {
    $user = DB::table('users')->first();
    if (!$user) {
      DB::table('users')
        ->insert([
          'name' => 'Admin',
          'username' => 'admin',
          'level' => 1,
          'password' => Hash::make('admin')
        ]);
    }
    return view('content.authentications.auth-login-basic');
  }

  public function login(Request $request)
  {
      $request->validate([
          'username' => ['required'],
          'password' => ['required'],
      ]);

      $user = DB::table('users')
        ->where('username', $request->input('username'))
        ->first();

      if (!$user) {
        return redirect()->back()->withError('Username tidak valid');
      }

      if (!Hash::check($request->input('password'), $user->password)) {
        return redirect()->back()->withError('Password tidak valid');
      }

      session()->put('data', $user);
      return redirect('/');
  }
}
