<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create {name} {email} {user_name} {password}';

    protected $description = 'Cria um novo tenant, usuário e os vincula';

    public function handle(): void
    {
        $user = User::firstOrCreate(
            ['email' => $this->argument('email')],
            [
                'name' => $this->argument('user_name'),
                'password' => Hash::make($this->argument('password')),
            ]
        );

        $tenant = Tenant::create([
            'id' => Str::slug($this->argument('name')),
            'name' => $this->argument('name'),
        ]);

        $tenant->users()->attach($user->id);

        tenancy()->initialize($tenant);

        (new PermissionSeeder)->run();

        TenantUser::updateOrCreate(
            ['id' => $user->id],
            ['name' => $user->name, 'email' => $user->email]
        );

        TenantUser::find($user->id)->assignRole('Administrador');

        tenancy()->end();

        $this->info("Tenant '{$tenant->name}' criado e vinculado ao usuário '{$user->email}' como admin.");
    }
}
