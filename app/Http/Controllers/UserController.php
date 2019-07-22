<?php

namespace App\Http\Controllers;

use App\user;
use App\service;
use App\role;
use App\direction;
use Illuminate\Http\Request;
use function React\Promise\all;

class UserController extends Controller
{

    public function getAllUsers(Request $request)
    {
        $role = $request->get('user')->role;
        if ($role != "Administrateur") {
            abort(403);
        }
        $users = user::all();
        return response($users)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function createUserIfDontExist(Request $request, $uuid)
    {
        $role = $request->get('user')->role;
        if ($role != "Administrateur") {
            abort(403);
        }
        $user = user::where('uuid', '=', $uuid)->first();
        if ($user === null) {
            $user = new user();
            $user->uuid = $uuid;
            $user->role = "Désactivé";
            $user->tid = "";
            $user->firstname = "";
            $user->lastname = "";
            $user->service = "";
            $user->direction = "";
            $user->mail = "";
            $user->save();
        }
    }

    public function getConnectedUserData(Request $request)
    {
        return response($request->get('user'))->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function getUsersName(Request $request, Int $quantity = null)
    {
        $users = [];
        if ($quantity == null) {
            $users = user::all('uuid', 'firstname', 'lastname');
        } else {
            $users = user::all('uuid', 'firstname', 'lastname')->take($quantity);
        }
        return response($users)->header('Content-Type', 'application/json')->header('charset', 'utf-8');

    }

    public function updateUserWithData(Request $request)
    {
        $role = $request->get('user')->role;
        if ($role != "Administrateur") {
            abort(403);
        }
        $postbody = '';
        // Check for presence of a body in the request
        if (count($request->json()->all())) {
            $postbody = $request->json()->all();
        } else {
            abort(400);
        }
        $user = user::where('uuid', '=', $postbody['uuid'])->first();
        if ($user == null) {
            abort(404);
        }

        if (!$user->validate($postbody)) {
            abort(400);
        }

        $role = role::where('role',$postbody["role"])->first();
        if($role == null){
            abort(400);
        }
        $user->role = $postbody["role"];
        $user->firstname = $postbody["firstname"];
        $user->lastname = $postbody["lastname"];
        $service = service::where('service',$postbody["service"])->first();
        if($service == null){
            abort(400);
        }
        $user->service = $postbody["service"];
        $direction = direction::where('direction',$postbody["direction"])->first();
        if($direction == null){
            abort(400);
        }
        $user->direction = $postbody["direction"];
        $user->mail = $postbody["mail"];
        $user->phone = $postbody["phone"];
        $user->tid = $postbody["tid"];
        $user->save();

        return response("success", 200);


    }

    public function addUser(Request $request)
    {
        $role = $request->get('user')->role;
        if ($role != "Administrateur") {
            abort(403);
        }

        $user = new user();
        $postbody = $request->all();
        if (!$user->validate($postbody)) {
            abort(400);
        }

        $user->uuid = $request->get("uuid");
        
        $role = role::where('role',$request->get("role"))->first();
        if($role == null){
            abort(400);
        }
        $user->role = $request->get("role");
        $user->firstname = $request->get("firstname");
        $user->lastname = $request->get("lastname");
        $service = service::where('service',$request->get("service"))->first();
        if($service == null){
            abort(400);
        }
        $user->service = $request->get("service");
        $direction = direction::where('direction',$request->get("direction"))->first();
        if($direction == null){
            abort(400);
        }
        $user->direction = $request->get("direction");
        $user->mail = $request->get("mail");
        $user->phone = $request->get("phone");
        $user->tid = $request->get("tid");

        $user->save();

        return response("", 200);
    }
}
