<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\theme;

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
}
