<?php

use Illuminate\Http\Request;
use Closure;

class HasRoleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // if (auth()->check()) {
        //     $roles = Role
        // }
    }
}
