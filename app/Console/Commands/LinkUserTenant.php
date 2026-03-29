<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Console\Command;

class LinkUserTenant extends Command
{
    protected $signature = 'user:link-tenant {email} {tenant_id} {--role=Administrador}';

    protected $description = 'Vincula um usuário a um tenant';

    public function handle(): void
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("Usuário '{$this->argument('email')}' não encontrado.");

            return;
        }

        $tenant = Tenant::find($this->argument('tenant_id'));

        if (! $tenant) {
            $this->error("Tenant '{$this->argument('tenant_id')}' não encontrado.");

            return;
        }

        if ($tenant->users()->where('user_id', $user->id)->exists()) {
            $this->warn('Usuário já está vinculado a este tenant.');

            return;
        }

        $tenant->users()->attach($user->id);

        tenancy()->initialize($tenant);

        $isFirst = TenantUser::count() === 0;

        TenantUser::updateOrCreate(
            ['id' => $user->id],
            ['name' => $user->name, 'email' => $user->email]
        );

        $tenantUser = TenantUser::find($user->id);
        $roleName = $isFirst ? 'Administrador' : $this->option('role');

        if (! Role::where('name', $roleName)->exists()) {
            (new PermissionSeeder)->run();
        }

        $tenantUser->assignRole($roleName);

        tenancy()->end();

        $this->info("Usuário '{$user->email}' vinculado ao tenant '{$tenant->name}' com papel '{$roleName}'.");
    }
}
