<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use Database\Seeders\ChartAccountSeeder;
use Database\Seeders\DefaultCustomerSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class RegisterController extends Controller
{
    public function __invoke(Request $request)
    {
        DB::transaction(function () use ($request) {
            $tenant = Tenant::create([
                'name' => $request->tenant_name,
                'trial_ends_at' => now()->addDays(7),
            ]);

            DB::table('users')->insert([
                'id' => Str::uuid(),
                'tenant_id' => $tenant->id,
                'sequential_id' => 1,
                'name' => $request->name,
                'email' => $request->email,
                'email_verified_at' => now(),
                'password' => bcrypt($request->password),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('tenant_sequences')->insert([
                'tenant_id' => $tenant->id,
                'entity_type' => 'users',
                'last_sequence_value' => 1,
            ]);

            $user = $tenant->users()->first();
            app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);

            $adminRole = Role::firstOrCreate([
                'sequential_id' => 1,
                'name' => 'Administrador',
                'tenant_id' => $user->tenant_id,
            ]);

            DB::table('tenant_sequences')->insert([
                'tenant_id' => $user->tenant_id,
                'entity_type' => 'roles',
                'last_sequence_value' => 1,
            ]);

            $adminRole->syncPermissions(Permission::all());
            $user->assignRole($adminRole);

            Auth::login($user);

            (new ChartAccountSeeder)->run($tenant);
            (new DefaultCustomerSeeder)->run($tenant);
        });

        // return to_route('home.index');
        return response()->make('', 409, ['X-Inertia-Location' => route('home.index')]);
    }
}
