<?php

namespace App\Http\Controllers;

use App\Models\Images;
use App\Http\Requests\StoreImagesRequest;
use App\Http\Requests\UpdateImagesRequest;

class ImagesController extends Controller
{

    public function index()
    {

        return view('images.index');
    }

}
