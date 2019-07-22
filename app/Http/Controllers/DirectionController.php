<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\direction;

class DirectionController extends Controller
{
    public function getAllDirection(){
        return direction::all();
    }

    public function addDirection($request){
        $role = $request->get('user')->role;
        if($role != "Administrateur") {
            abort(403);
        }
        $name = $request->get('direction');
        $desc = $request->get('desc');
        $direction = new direction();
        $direction->direction = $name;
        $direction->description = $desc;
        $direction->save();
    }

    public function delDirection(){
        $role = $request->get('user')->role;
        if($role != "Administrateur") {
            abort(403);
        }
        $name = $request->get('direction');
        $direction = direction::where('direction', $name);
        if($direction == null){
            abort(403);
        }
        $direction->delete();
    }
}