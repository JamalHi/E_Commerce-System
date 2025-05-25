<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;

class AccessControl
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
        $user = auth()->user();

        $user_role = Role::find($user->role_id);

        $permissionName = $request->route()->getName();

        if(! $user_role->check($permissionName)){
            return response()->json(['message' => 'Access Denied']);
        //    $this->forbiddenResponse('Access Denied');
        }
        return $next($request);
    }
}
