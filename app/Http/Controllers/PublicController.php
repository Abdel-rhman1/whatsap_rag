<?php

namespace App\Http\Controllers;

class PublicController extends Controller
{
    public function landing()
    {
        return view('public.landing', ['plans' => []]);
    }

    public function privacy()
    {
        return view('public.privacy');
    }

    public function terms()
    {
        return view('public.terms');
    }
}
