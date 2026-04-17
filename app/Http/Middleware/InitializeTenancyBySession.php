<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InitializeTenancyBySession
{
    public function handle(Request $request, Closure $next)
    {
        // dd('InitializeTenancyBySession');
        if (session()->has('tenant_id')) {
            tenancy()->initialize(session('tenant_id'));

            return $next($request);
        }

        return redirect()->route('tenant-select.index');
    }
}
