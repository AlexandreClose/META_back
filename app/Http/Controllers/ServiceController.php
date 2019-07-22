<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\service;

class ServiceController extends Controller
{
    public function getAllServices(){
        return service::all();
    }
}
