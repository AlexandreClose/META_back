<?php /** @noinspection PhpUnused */

namespace App\Http\Controllers;

use App\color;
use App\direction;
use App\role;
use App\service;
use App\theme;
use App\user;
use App\user_theme;
use Exception;
use Illuminate\Http\Request;

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

    public function getUsersName(Int $quantity = null)
    {
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
        $postBody = '';
        // Check for presence of a body in the request
        if (count($request->json()->all())) {
            $postBody = $request->json()->all();
        } else {
            abort(400, "bad json");
        }

        //$user = new  user();
        $user = user::where('uuid', '=', $postBody['uuid'])->first();
        if ($user == null) {
            abort(404);
        }

        if (!$user->validate($postBody)) {
            abort(413);
        }

        $role = role::where('role', $postBody["role"])->first();
        if ($role == null) {
            abort(400, "Role does not exist ! ");
        }
        $user->role = $role->role;
        $user->firstname = $postBody["firstname"];
        $user->lastname = $postBody["lastname"];
        $service = service::where('service', $postBody["service"])->first();
        if ($service == null) {
            abort(400, "No service or does not exist");
        }
        $user->service = $postBody["service"];
        $direction = direction::where('direction', $postBody["direction"])->first();
        if ($direction == null) {
            abort(400, "No direction or does not exist");
        }
        $user->direction = $postBody["direction"];
        $user->mail = $postBody["mail"];
        $user->phone = $postBody["phone"];
        $user->tid = $postBody["tid"];
        $user->save();
    }

    public function addUser(Request $request)
    {

        $role = $request->get('user')->role;
        if ($role != "Administrateur") {
            abort(403);
        }

        $user = new user();
        $postBody = $request->all();
        if (!$user->validate($postBody)) {
            abort(400);
        }

        $user->uuid = $request->get("tid");
        $user->role = $request->get("role");
        $user->firstname = $request->get("firstname");
        $user->lastname = $request->get("lastname");
        $user->service = $request->get("service");
        $user->direction = $request->get("direction");
        $user->mail = $request->get("mail");
        $user->phone = $request->get("phone");
        $user->tid = $request->get("tid");
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
        $postBody = $request->all();
        if (!$userTheme->validate($postBody)) {
            abort(400);
        }

        if (!(user::find($request->get("uuid")) and theme::find($request->get("name")))) {
            abort(404);
        }

        try {
            $userTheme->uuid = $postBody['uuid'];
            $userTheme->name = $postBody['name'];
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

    public function blockUser(Request $request, $uuid)
    {
        $role = $request->get('user')->role;
        if ($role != "Administrateur") {
            abort(403);
        }

        $user = user::where('uuid', $uuid)->first();
        if ($user == null) {
            abort(404);
        }
        $user->role = "Désactivé";
        $user->save();
    }

    public function unblockUser(Request $request, $uuid)
    {
        $role = $request->get('user')->role;
        if ($role != "Administrateur") {
            abort(403);
        }

        $user = user::where('uuid', $uuid)->first();
        if ($user == null) {
            abort(404);
        }
        $user->role = "Utilisateur";
        $user->save();
    }

    public function getAllUserColor(Request $request)
    {
        $user = $request->get('user');
        return response($user->colors)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function addColorToUser(Request $request)
    {
        $user = $request->get('user');
        $color = $request->get('color_code');

        $data = array(
            'user_uuid' => $user->uuid,
            'color_code' => $color,
            'created_at' => NOW(),
            'updated_at' => NOW()
        );
        color::insert($data);
    }

    public function removeColorFromUser(Request $request)
    {
        $user = $request->get('user');
        $color = color::where([['user_uuid', $user->uuid], ['color_code', $request->get('color_code')]]);
        $color->delete();
    }

    public function updateColorUser(Request $request)
    {
        $user = $request->get('user');
        $colors = $request->get('colors');

        color::where('user_uuid', $user->uuid)->delete();

        $data = array();
        foreach ($colors as $color) {
            array_push($data, array(
                'user_uuid' => $user->uuid,
                'color_code' => $color,
                'created_at' => NOW(),
                'updated_at' => NOW()
            ));
        }
        color::insert($data);
    }
}
