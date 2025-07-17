<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KulakController extends Controller
{
    public function index()
    {
        return view('kulak.index');
    }
}
