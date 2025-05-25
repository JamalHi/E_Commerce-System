<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class check_login
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

              if($request->hasHeader('api_token'))
              {
                  $user=User::where('remember_token',$request->header('api_token'))->first();

                  $u=Auth::id()->where('accessTokens',$request->header('api_token'))->first();
                   echo $u;
                   echo"hello";

                  $request->user_id=$user->id;
                  //$i=Auth::id();
                  //$request->user_id=$i;
                  //echo $i;
                  //echo "hello";
                  return $next($request);
              }
              else
              {
                  return response()->json(['message'=>'you need to login first!']);
              }
    }
}
