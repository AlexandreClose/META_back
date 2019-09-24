<?php

namespace App\Http\Middleware;
use App\user;
use Carbon\Carbon;
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

        $user = user::where('token',$token,'token_expirate'> Carbon::now())->first();
        if($user == null){
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
            $user->token = $token;
            $user->token_expirate = Carbon::now()->addHours(8);
            $user->save();
        }
        if($user->role == "Désactivé"){
            abort(403);
        }
        $request->merge(['user' => $user]);
        return $next($request);
    }
}
