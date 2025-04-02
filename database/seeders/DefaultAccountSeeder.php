<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DefaultAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Tenant $tenant): void
    {
        $user = User::where('tenant_id', $tenant->id)->first();

        Account::create([
            'tenant_id' => $tenant->id,
            'name' => 'Caixa',
            'type' => 'cash',
            'created_by' => $user->id,
        ]);
    }
}
