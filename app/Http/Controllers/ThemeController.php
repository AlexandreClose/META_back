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
        $themes = DB::table('themes')
            ->join('user_theme', 'user_theme.theme', 'themes.theme')
            ->select('themes.theme', 'themes.description', DB::raw('count(user_theme.uuid) as user_count'))
            ->groupBy('themes.theme')
            ->get();
        return response($themes)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
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
