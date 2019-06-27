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
        //TODO: FAIRE LE CAS
        $token = $request->header('Authorization');
        if(!isset($token)){
            abort(401);
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

            $user = user::where('uuid',$value)->first();
            if($user == null){
                abort(403);
            }
            else{
                $user->token = $value;
                $request->merge(['user' => $user]);

            }

        return $next($request);
    }
}
