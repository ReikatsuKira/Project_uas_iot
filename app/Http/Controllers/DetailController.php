<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DetailController extends Controller
{
    public function gas()
    {
        return view('detail.gas');
    }

    public function sampah()
    {
        return view('detail.sampah');
    }

    public function pengguna()
    {
        return view('detail.pengguna');
    }
}
