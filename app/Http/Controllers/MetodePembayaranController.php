<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MetodePembayaranController extends Controller
{
    public function index()
    {
        return view('metode-pembayaran.index');
    }
}
