<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Analytics extends Controller
{
  public function index()
  {
    if (!\Session::get('data')) {
      return redirect('/auth/login');
    }
    return view('content.dashboard.dashboards-analytics');
  }
}
