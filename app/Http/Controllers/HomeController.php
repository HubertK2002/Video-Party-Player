<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        return view('mainpage');
    }

    public function messages()
    {
        return view('messages');
    }
}
