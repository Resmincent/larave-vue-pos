<?php

namespace App\Http\Middleware;


use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class HasRoleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $roles = Role::get();
            if ($request->user()->hasAnyRole($roles->pluck('name')->toArray())) {
                return $next($request);
            }
            abort(403, 'Unauthorized');
        } else {
            abort(404, 'User not found');
        }
    }
}
