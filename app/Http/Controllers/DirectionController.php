<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\direction;

class DirectionController extends Controller
{
    public function getAllDirections(){
        $directions = DB::table('directions')
            ->join('users', 'users.direction', 'directions.direction')
            ->select('directions.*, count(users.uuid) as user_count')
            ->groupBy('directions.direction')
            ->get();
        return $direction;
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

    public function updateDirection(Request $request){
        $role = $request->get('user')->role;
        if($role != "Administrateur") {
            abort(403);
        }
        $name = $request->get('direction');
        $newName = $request->get('newName');
        $desc = $request->get('desc');
        $direction = direction::where('direction', $name);
        if($direction == null){
            abort(403);
        }
        $direction->direction = $newName != null ? $newName : $name;
        $direction->description = $desc != null ? $desc : $direction->description;
        $direction->save();
    }
}
