<?php

namespace App\Http\Controllers;

use App\theme;
use App\user;
use App\user_theme;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    function getAllThemes($quantity = null)
    {
        $data = [];
        if ($quantity == null) {
            $themes = theme::all();
        } else {
            $themes = theme::take($quantity)->get();
        }
        foreach ($themes as $theme) {
            $theme = json_decode($theme);
            array_push($data, $theme);
        }
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    function addTheme(Request $request)
    {
        $role = $request->get('user')->role;
        if ($role != "Administrateur") {
            abort(403);
        }

        $theme = new Theme();
        $postbody = $request->all();
        if (!$theme->validate($postbody)) {
            abort(400);
        }

        $theme->name = $postbody["name"];
        $theme->description = $postbody["description"];
        $theme->save();

        return response("", 200);
    }

    function deleteTheme(Request $request)
    {
        $role = $request->get('user')->role;
        if ($role != "Administrateur") {
            abort(403);
        }

        $theme = theme::find($request->get("name"));

        user_theme::where('name', '=', $theme->name)->update(['name' => 'Default']);

        $theme->delete();

        return response('',200);
    }
}
