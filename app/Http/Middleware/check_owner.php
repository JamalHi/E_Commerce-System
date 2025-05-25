<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Product;

class check_owner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if($request->header('api_token'))
        {
            $user=User::where('remember_token',$request->header('api_token'))->first();
            $request->user_id=$user->id;

            $pro=Product::where('id',$request->product_id)->first();

            if($pro==null)
            {
                return response()->json("this product doesn't exist",404);
            }
            $owner=$pro->owner_id;
            if($request->user_id==$owner)
            {
            return $next($request);
            }
            else
            {
                return response()->json("can't perform action because the product isn't yours",400);
            }
        }
        else
        {
            return response()->json(['message'=>'you need to login first!']);
        }

    }
}
