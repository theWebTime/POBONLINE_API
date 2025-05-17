<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle($request, Closure $next, ...$roles)
    {
        $roleIds = ['superAdmin' => 1, 'admin' => 2];
        $allowedRoleIds = [];
        foreach ($roles as $role) {
            if (isset($roleIds[$role])) {
                $allowedRoleIds[] = $roleIds[$role];
            }
        }
        $allowedRoleIds = array_unique($allowedRoleIds);

        if (Auth::check()) {
            if (in_array(Auth::user()->role, $allowedRoleIds)) {
                return $next($request);
            }
        }

        return response()->json('unauthorized', 401);
    }
}
