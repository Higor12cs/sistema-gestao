<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DefaultPaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Tenant $tenant): void
    {
        $user = User::where('tenant_id', $tenant->id)->first();

        PaymentMethod::create([
            'tenant_id' => $tenant->id,
            'name' => 'Dinheiro',
            'type' => 'cash',
            'created_by' => $user->id,
        ]);

        PaymentMethod::create([
            'tenant_id' => $tenant->id,
            'name' => 'Crediário',
            'type' => 'cash',
            'created_by' => $user->id,
        ]);

        PaymentMethod::create([
            'tenant_id' => $tenant->id,
            'name' => 'Cartão de Crédito',
            'type' => 'credit_card',
            'created_by' => $user->id,
        ]);

        PaymentMethod::create([
            'tenant_id' => $tenant->id,
            'name' => 'Cartão de Débito',
            'type' => 'debit_card',
            'created_by' => $user->id,
        ]);

        PaymentMethod::create([
            'tenant_id' => $tenant->id,
            'name' => 'PIX',
            'type' => 'pix',
            'created_by' => $user->id,
        ]);
    }
}
