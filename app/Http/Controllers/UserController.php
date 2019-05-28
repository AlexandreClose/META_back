<?php

namespace App\Http\Controllers;

use App\user;
use Illuminate\Http\Request;
use function React\Promise\all;

class UserController extends Controller
{

    public function getAllUsers(){
        $users = user::all();
        return response($users)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }
    public function createUserIfDontExist($uuid){
        $user = user::where('uuid', '=', $uuid)->first();
        if ($user === null) {
            $user = new user();
            $user->uuid = $uuid;
            $user->role = "Désactivé";
            $user->firstname = "";
            $user->lastname = "";
            $user->service = "";
            $user->direction = "";
            $user->mail = "";
            $user->save();
        }
    }

    public function updateUserWithData(Request $request){
        $postbody='';
        // Check for presence of a body in the request
        if (count($request->json()->all())) {
            $postbody = $request->json()->all();
        }
        else{
            abort(400);
        }
        $user = user::where('uuid', '=', $postbody['uuid'])->first();
        if($user == null){
            abort(404);
        }

        if(!$user->validate($postbody)){
            abort(400);
        }

        //TODO: Ajouter la verification du role dans la table role
        $user->role = $postbody["role"];
        $user->firstname = $postbody["firstname"];
        $user->lastname = $postbody["lastname"];
        $user->service = $postbody["service"];
        $user->direction = $postbody["direction"];
        $user->mail = $postbody["mail"];
        $user->phone = $postbody["phone"];
        $user->save();

        return response("success",200);



    }
}
