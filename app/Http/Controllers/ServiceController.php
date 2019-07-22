<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\service;

class ServiceController extends Controller
{
    public function getAllServices(){
        return service::all();
    }

    public function addService($request){
        $role = $request->get('user')->role;
        if($role != "Administrateur") {
            abort(403);
        }
        $name = $request->get('service');
        $desc = $request->get('desc');
        $service = new service();
        $service->service = $name;
        $service->description = $desc;
        $service->save();
    }

    public function delService(){
        $role = $request->get('user')->role;
        if($role != "Administrateur") {
            abort(403);
        }
        $name = $request->get('service');
        $service = service::where('service', $name);
        if($service == null){
            abort(403);
        }
        $service->delete();
    }
}
