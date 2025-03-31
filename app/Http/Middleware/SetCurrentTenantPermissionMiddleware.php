<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentTenantPermissionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            app(PermissionRegistrar::class)->setPermissionsTeamId(Auth::user()->tenant_id);
        }

        return $next($request);
    }
}
