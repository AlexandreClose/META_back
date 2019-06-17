<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\role;

class RolesController extends Controller
{
    function getAllRoles(){
        $roles = role::all();
        dd($roles);
    }
}
