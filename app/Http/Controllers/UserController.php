<?php

namespace App\Http\Controllers;

use App\theme;
use App\user;
use App\user_theme;
use Exception;
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

        $user = new  user();
        $user::where('uuid', '=', $postbody['uuid'])->first();
        if ($user == null) {
            abort(404);
        }

        if (!$user->validate($postbody)) {
            abort(413);
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
        $user->role = $request->get("role");
        $user->firstname = $request->get("firstname");
        $user->lastname = $request->get("lastname");
        $user->service = $request->get("service");
        $user->direction = $request->get("direction");
        $user->mail = $request->get("mail");
        $user->phone = $request->get("phone");

        $user->save();

        return response("", 200);
    }

    public function addUserTheme(Request $request)
    {
        $role = $request->get('user')->role;
        if ($role != "Administrateur") {
            abort(403);
        }

        $userTheme = new user_theme();
        $postbody = $request->all();
        if (!$userTheme->validate($postbody)) {
            abort(400);
        }

        if (!(user::find($request->get("uuid")) and theme::find($request->get("name")))) {
            abort(404);
        }

        try {
            $userTheme->uuid = $postbody['uuid'];
            $userTheme->name = $postbody['name'];
            $userTheme->save();
        } catch (Exception $e) {
            if (!($e->getCode() === 0)) {
                abort(400);
            }
        }

        return response('', 200);
    }

    public function deleteUserTheme(Request $request)
    {
        $role = $request->get('user')->role;
        if ($role != "Administrateur") {
            abort(403);
        }

        if (user_theme::where('name', '=', $request->get('name'))
            ->where('uuid', '=', $request->get('uuid'))->get() == '[]') {
            abort(400);
        }
        user_theme::where('name', '=', $request->get('name'))
            ->where('uuid', '=', $request->get('uuid'))->delete();

        return response('', 200);
    }

    public function blockUser(Request $request, $uuid){
        $role = $request->get('user')->role;
        if ($role != "Administrateur") {
            abort(403);
        }

        $user = user::where('uuid', $uuid)->first();
        if($user == null){
            abort(404);
        }
        $user->role = "Désactivé";
        $user->save();
    }
}
