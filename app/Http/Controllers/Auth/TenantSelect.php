<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantSelect extends Controller
{
    public function index()
    {
        $tenants = Auth::user()->tenants;

        return inertia('Auth/TenantSelect', [
            'tenants' => $tenants,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tenant_id' => ['required', 'string'],
        ]);

        $user = $request->user();
        $tenantId = $request->input('tenant_id');

        if (! $user->tenants()->where('id', $tenantId)->exists()) {
            abort(403, 'Você não tem permissão para acessar este ambiente.');
        }

        session(['tenant_id' => $tenantId]);

        return response()->make('', 409, ['X-Inertia-Location' => route('home.index')]);
    }
}
