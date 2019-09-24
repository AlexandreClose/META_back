<?php /** @noinspection PhpUnused */

namespace App\Http\Controllers;


use App\role;

class RolesController extends Controller
{
    function getAllRoles()
    {
        $roles = role::all();
        return $roles;
    }
}
