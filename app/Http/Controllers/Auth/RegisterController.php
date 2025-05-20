<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use Database\Seeders\ChartAccountSeeder;
use Database\Seeders\DefaultAccountSeeder;
use Database\Seeders\DefaultCustomerSeeder;
use Database\Seeders\DefaultPaymentMethodSeeder;
use Database\Seeders\TestSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

            $user = $tenant->users()->create([
                'sequential_id' => 1,
                'name' => $request->name,
                'email' => $request->email,
                'email_verified_at' => now(),
                'password' => bcrypt($request->password),
            ]);

            DB::table('tenant_sequences')->insert([
                'tenant_id' => $tenant->id,
                'entity_type' => 'users',
                'last_sequence_value' => 1,
            ]);

            app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);

            $admin = Role::firstOrCreate([
                'sequential_id' => 1,
                'name' => 'Administrador',
                'tenant_id' => $user->tenant_id,
            ]);

            DB::table('tenant_sequences')->insert([
                'tenant_id' => $user->tenant_id,
                'entity_type' => 'roles',
                'last_sequence_value' => 1,
            ]);

            $admin->syncPermissions(Permission::all());
            $user->assignRole($admin);

            Auth::login($user);

            (new DefaultCustomerSeeder)->run($tenant);
            (new ChartAccountSeeder)->run($tenant);

            if ($user->email === 'test@example.com') {
                (new (TestSeeder::class))->run($tenant, $user);
            } else {
                (new (DefaultAccountSeeder::class))->run($tenant);
                (new (DefaultPaymentMethodSeeder::class))->run($tenant);
            }
        });

        return response()->make('', 409, ['X-Inertia-Location' => route('home.index')]);
    }
}
