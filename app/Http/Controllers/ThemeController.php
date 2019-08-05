<?php

namespace App\Http\Controllers;

use App\theme;
use App\user;
use App\user_theme;
use App\dataset;
use App\analysis;
use App\column;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThemeController extends Controller
{
    function getAllThemes($quantity = null)
    {
        $themes = DB::table('themes')
            ->leftJoin('user_theme', 'user_theme.name', '=', 'themes.name')
            ->select('themes.name', 'themes.description', DB::raw('count(user_theme.uuid) as user_count'))
            ->groupBy('themes.name')    
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

    function deleteTheme(Request $request, $newName)
    {
        $role = $request->get('user')->role;
        if ($role != "Administrateur") {
            abort(403);
        }

        $theme = theme::where('name', $request->get("name"))->first();

        user_theme::where('name', '=', $theme->name)->update(['name' => $newName]);
        dataset::where('themeName', '=', $theme->name)->update(['themeName' => $newName]);
        column::where('themeName', '=', $theme->name)->update(['themeName' => $newName]);
        analysis::where('theme_name', '=', $theme->name)->update(['theme_name' => $newName]);


        $theme->delete();

        return response('',200);
    }

    public function updateTheme(Request $request){
        $role = $request->get('user')->role;
        if($role != "Administrateur") {
            abort(403);
        }
        $name = $request->get('theme');
        $newName = $request->get('newName');
        $desc = $request->get('desc');
        $theme = theme::where('name', $name)->first();
        if($theme == null){
            abort(403);
        }

        if($newName != null){
            user_theme::where('name', '=', $theme->name)->update(['name' => $newName]);
            dataset::where('themeName', '=', $theme->name)->update(['themeName' => $newName]);
            column::where('themeName', '=', $theme->name)->update(['themeName' => $newName]);
            analysis::where('theme_name', '=', $theme->name)->update(['theme_name' => $newName]);
            $theme->theme = $newName;
        }
        if ($desc != null){
            $theme->description = $desc;
        }
        $theme->save();
    }
}
