<?php

namespace App\Http\Controllers;

use App\Models\Credencial;
use App\Http\Requests\StoreCredencialRequest;
use App\Http\Requests\UpdateCredencialRequest;

class CredencialController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        return view('credenciales.index');
    }



}
