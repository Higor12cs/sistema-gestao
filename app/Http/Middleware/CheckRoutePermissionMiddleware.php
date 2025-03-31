<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;

class CheckRoutePermissionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = Route::currentRouteName();

        if (strpos($routeName, 'api.') === 0 || strpos($routeName, '.search') !== false) {
            return $next($request);
        }

        $parts = explode('.', $routeName);

        $resource = count($parts) > 2 ? "{$parts[0]}.{$parts[1]}" : $parts[0];
        $action = count($parts) > 2 ? $parts[2] : $parts[1];

        switch ($action) {
            case 'index':
            case 'show':
                $permissionAction = 'index';
                break;
            case 'create':
            case 'store':
                $permissionAction = 'create';
                break;
            case 'edit':
            case 'update':
                $permissionAction = 'edit';
                break;
            case 'destroy':
                $permissionAction = 'destroy';
                break;
            default:
                $permissionAction = $action;
        }

        $permission = "$resource.$permissionAction";

        if (! Permission::where('name', $permission)->exists()) {
            return $next($request);
        }

        if (! Auth::user()->hasPermissionTo($permission)) {
            throw UnauthorizedException::forPermissions([$permission]);
        }

        return $next($request);
    }
}
