<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\direction;

class DirectionController extends Controller
{
    public function getAllDirection(){
        return direction::all();
    }
}
