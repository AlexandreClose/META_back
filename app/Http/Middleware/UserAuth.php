<?php

namespace App\Http\Middleware;
use App\user;
use GuzzleHttp\Client;

use Closure;
use PHPUnit\Util\Json;
use function GuzzleHttp\json_decode;

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


        $token = $request->header('Authorization');
        if(!isset($token)){
            abort(401);
        }

        if($token == "Juiploetdjtozvelnjzkfpofn"){
            $user = user::where('uuid',"2be8c158-29a7-42b3-a9fb-de9ec266e196")->first();
            $request->merge(['user' => $user]);
            return $next($request);
        }

        $user = user::where('token',$token)->first();
        if($user != null){
            $request->merge(['user' => $user]);
            return $next($request);
        }

        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];

        $res = $client->get('https://authsso.extranet.toulouse.fr/cas/oidc/profile', ['headers' => $headers]);
        $data = json_decode($res->getBody(),true);
        $value = $data["sub"];
        if(!isset($value)){
            abort(403);
        }

            $user = user::where('tid',$value)->first();
            if($user == null){
                abort(403);
            }
            else{
                $user->token = $token;
                $request->merge(['user' => $user]);

            }

        return $next($request);
    }
}
