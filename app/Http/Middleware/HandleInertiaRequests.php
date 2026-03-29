<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $tenantUser = null;

        if ($user && session()->has('tenant_id')) {
            tenancy()->initialize(session('tenant_id'));
            $tenantUser = TenantUser::find($user->id);
        }

        return array_merge(parent::share($request), [
            'currentTenant' => session('tenant_id') ? Tenant::find(session('tenant_id')) : null,
            'auth' => [
                'user' => fn () => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ] : null,
                'roles' => fn () => $tenantUser
                    ? $tenantUser->getRoleNames()->toArray()
                    : [],
                'permissions' => fn () => $tenantUser
                    ? $tenantUser->getAllPermissions()->pluck('name')->toArray()
                    : [],
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
                'info' => $request->session()->get('info'),
            ],
        ]);
    }
}
