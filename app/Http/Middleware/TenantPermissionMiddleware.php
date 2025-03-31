<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class TenantPermissionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $tenantId = $user->tenant_id;

        app(PermissionRegistrar::class)->setPermissionClass(
            Permission::where('tenant_id', $tenantId)
        );

        app(PermissionRegistrar::class)->setRoleClass(
            Role::where('tenant_id', $tenantId)
        );

        return $next($request);
    }
}
