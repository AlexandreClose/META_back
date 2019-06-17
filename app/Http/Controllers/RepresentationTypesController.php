<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\representation_type;

class RepresentationTypesController extends Controller
{
    function getAllRepresentationTypes($quantity = 0){
        if($quantity == 0){
            $types = representation_type::all();
        }
        else{
            $types = representation_type::all()->take($quantity);
        }
        return response($types)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }
}
