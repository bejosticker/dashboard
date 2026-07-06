<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use DB;

class AuthController extends Controller
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
    return Inertia::render('Auth/Login');
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

      if (!$user || !Hash::check($request->input('password'), $user->password)) {
        return redirect()->back()->withErrors(['username' => 'Username atau password salah.']);
      }

      session()->put('data', $user);
      return redirect('/');
  }

  public function logout()
  {
    session()->flush();
    return redirect('/auth/login');
  }
}
