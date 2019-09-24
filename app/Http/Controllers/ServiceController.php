<?php /** @noinspection PhpUnused */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\service;

class ServiceController extends Controller
{
    public function getAllServices()
    {
        $services = DB::table('services')
            ->leftJoin('users', 'users.service', 'services.service')
            ->select('services.service', 'services.description', DB::raw('count(users.uuid) as user_count'))
            ->groupBy('services.service')
            ->get();
        return $services;
    }

    public function addService(Request $request)
    {
        $role = $request->get('user')->role;
        if ($role != "Administrateur") {
            abort(403);
        }
        $name = $request->get('service');
        $desc = $request->get('desc');
        $service = new service();
        $service->service = $name;
        $service->description = $desc;
        $service->save();
    }

    public function delService(Request $request, $name)
    {
        $role = $request->get('user')->role;
        if ($role != "Administrateur") {
            abort(403);
        }
        $service = service::where('service', $name)->first();
        if ($service == null) {
            abort(403);
        }
        $service->delete();
    }

    public function updateService(Request $request)
    {
        $role = $request->get('user')->role;
        if ($role != "Administrateur") {
            abort(403);
        }
        $name = $request->get('service');
        $newName = $request->get('newName');
        $desc = $request->get('desc');
        $service = service::where('service', $name)->first();
        if ($service == null) {
            abort(403);
        }
        $service->service = $newName != null ? $newName : $name;
        $service->description = $desc != null ? $desc : $service->description;
        $service->save();
    }
}
