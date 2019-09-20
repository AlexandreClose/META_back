<?php /** @noinspection PhpUnused */

namespace App\Http\Controllers;

use App\theme;
use App\user_theme;
use App\dataset;
use App\analysis;
use App\column;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThemeController extends Controller
{
    function getAllThemes()
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
        $postBody = $request->all();
//        if (!$theme->validate($postBody)) {
//            abort(400);
//        }

        $theme->name = $postBody["name"];
        $theme->description = $postBody["description"];
        $theme->save();

        return response("", 200);
    }

    function deleteTheme(Request $request, $name, $newName)
    {
        $role = $request->get('user')->role;
        if ($role != "Administrateur") {
            abort(403,"Unauthorized");
        }


        if (theme::where('name', urldecode($name))->get() == '[]' or theme::where('name', urldecode($newName))->get() == '[]') {
            abort(400,"Bad request");
        }

        $theme = theme::where('name', '=', urldecode($name));
        $theme->delete();

        user_theme::where('name', '=', $name)->update(['name' => $newName]);
        dataset::where('themeName', '=', $name)->update(['themeName' => $newName]);
        column::where('themeName', '=', $name)->update(['themeName' => $newName]);
        analysis::where('theme_name', '=', $name)->update(['theme_name' => $newName]);

        return response('', 200);
    }

    public function updateTheme(Request $request)
    {
        $role = $request->get('user')->role;
        if ($role != "Administrateur") {
            abort(403);
        }
        $name = $request->get('name');
        $newName = $request->get('newName');
        $desc = $request->get('desc');
        $theme = theme::where('name', $name)->first();
        if ($theme == null) {
            abort(403);
        }

        if ($newName != null) {
            user_theme::where('name', '=', $theme->name)->update(['name' => $newName]);
            dataset::where('themeName', '=', $theme->name)->update(['themeName' => $newName]);
            column::where('themeName', '=', $theme->name)->update(['themeName' => $newName]);
            analysis::where('theme_name', '=', $theme->name)->update(['theme_name' => $newName]);
            $theme->name = $newName;
        }
        if ($desc != null) {
            $theme->description = $desc;
        }
        $theme->save();
    }
}
