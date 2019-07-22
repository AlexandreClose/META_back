<?php

namespace App\Http\Controllers;

use App\theme;
use App\user;
use App\user_theme;
use Exception;
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

        //TODO: Ajouter la verification du role dans la table role
        $user->role = $postbody["role"];
        $user->firstname = $postbody["firstname"];
        $user->lastname = $postbody["lastname"];
        $user->service = $postbody["service"];
        $user->direction = $postbody["direction"];
        $user->mail = $postbody["mail"];
        $user->phone = $postbody["phone"];
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

        $user->uuid = $postbody["uuid"];
        $user->role = $postbody["role"];
        $user->firstname = $postbody["firstname"];
        $user->lastname = $postbody["lastname"];
        $user->service = $postbody["service"];
        $user->direction = $postbody["direction"];
        $user->mail = $postbody["mail"];
        $user->phone = $postbody["phone"];

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
}
