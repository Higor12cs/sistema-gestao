<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfigurationSeeder extends Seeder
{
    public function run(Tenant $tenant): void
    {
        if (!$tenant->exists) {
            $tenants = Tenant::all();

            foreach ($tenants as $tenant) {
                $tenant->configurations()->firstOrCreate(
                    [
                        'name' => 'order_receivable_default_chart_account_id'
                    ],
                    [
                        'sequential_id' => 1,
                        'description' => 'Padrão do Plano de Contas para Recebíveis de Vendas',
                        'value' => '',
                        'type' => 'string',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $tenant->configurations()->firstOrCreate(
                    [
                        'name' => 'purchase_payable_default_chart_account_id'
                    ],
                    [
                        'sequential_id' => 2,
                        'description' => 'Padrão do Plano de Contas para Pagáveis de Compras',
                        'value' => '',
                        'type' => 'string',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            return;
        }

        $tenant->configurations()->firstOrCreate(
            [
                'name' => 'order_receivable_default_chart_account_id'
            ],
            [
                'sequential_id' => 1,
                'description' => 'Padrão do Plano de Contas para Recebíveis de Vendas',
                'value' => '',
                'type' => 'string',
            ]
        );

        $tenant->configurations()->firstOrCreate(
            [
                'name' => 'purchase_payable_default_chart_account_id'
            ],
            [
                'sequential_id' => 2,
                'description' => 'Padrão do Plano de Contas para Pagáveis de Compras',
                'value' => '',
                'type' => 'string',
            ]
        );
    }
}
