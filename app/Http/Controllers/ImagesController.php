<?php

namespace App\Http\Controllers;

class ImagesController extends Controller
{
    public function index()
    {
        return view('images.index', ['section' => 'processor']);
    }

    public function optimizer()
    {
        return view('images.index', ['section' => 'optimizer']);
    }

    public function socialAi()
    {
        return view('images.index', ['section' => 'social-ai']);
    }

    public function marcos()
    {
        return view('marcos.index');
    }
}
