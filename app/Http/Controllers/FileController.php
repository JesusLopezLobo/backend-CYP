<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileController extends Controller
{
    public function index(){
        return response()->download(public_path('defecto.png'), 'User Image');
    }
}
