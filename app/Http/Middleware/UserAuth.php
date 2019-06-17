<?php

namespace App\Http\Middleware;
use App\user;

use Closure;

class UserAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //TODO: FAIRE LE CAS
        $token = $request->header('Authorization');
        if(!isset($token)){
            abort(401);
        }

        if($token == "Juiploetdjtozvelnjzkfpofn"){
            $user = user::where('uuid',"2be8c158-29a7-42b3-a9fb-de9ec266e196")->first();
            if($user == null){
                abort(403);
            }
            else{
                $request->merge(['user' => $user]);

            }
        }
        else{
            abort(403);
        }

        return $next($request);
    }
}
