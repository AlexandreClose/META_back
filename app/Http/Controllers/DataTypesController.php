<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\data_type;

class DataTypesController extends Controller
{
    function getAllDataTypes($quantity = 0){
        if($quantity == 0){
            $dataTypes = data_type::all();
        }
        else{
            $dataTypes = data_type::all()->take($quantity);
        }

        return response($dataTypes)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }
}
