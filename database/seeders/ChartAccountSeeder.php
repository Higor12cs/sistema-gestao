<?php

namespace Database\Seeders;

use App\Models\ChartAccount;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ChartAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Tenant $tenant): void
    {
        if (! $tenant) {
            $this->command->error('É necessário fornecer um tenant para criar o plano de contas.');

            return;
        }

        $user = User::where('tenant_id', $tenant->id)->first();

        if (! $user) {
            $this->command->error('Nenhum usuário encontrado para o tenant especificado.');

            return;
        }

        $tenantId = $tenant->id;

        // 1 - ENTRADAS (Nível 1)
        $entradas = $this->createAccount([
            'tenant_id' => $tenantId,
            'code' => '1',
            'name' => 'Entradas',
            'description' => 'Todas as receitas e entradas de recursos',
            'allows_transactions' => false,
            'active' => true,
            'level' => 1,
            'order' => 1,
            'created_by' => $user->id,
        ]);

        // 1.1 - VENDAS (Nível 2)
        $vendas = $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $entradas->id,
            'code' => '1.1',
            'name' => 'Receitas de Vendas',
            'description' => 'Receitas provenientes de vendas de produtos',
            'allows_transactions' => false,
            'active' => true,
            'level' => 2,
            'order' => 1,
            'created_by' => $user->id,
        ]);

        // Nível 3 - Subcategorias de Vendas
        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $vendas->id,
            'code' => '1.1.1',
            'name' => 'Vendas à Vista',
            'description' => 'Receitas de vendas pagas no momento da compra',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 1,
            'created_by' => $user->id,
        ]);

        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $vendas->id,
            'code' => '1.1.2',
            'name' => 'Vendas a Prazo',
            'description' => 'Receitas de vendas parceladas ou com pagamento futuro',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 2,
            'default_receivable' => true,
            'created_by' => $user->id,
        ]);

        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $vendas->id,
            'code' => '1.1.3',
            'name' => 'Vendas Online',
            'description' => 'Receitas de vendas realizadas por e-commerce ou internet',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 3,
            'created_by' => $user->id,
        ]);

        // 1.2 - SERVIÇOS (Nível 2)
        $servicos = $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $entradas->id,
            'code' => '1.2',
            'name' => 'Serviços',
            'description' => 'Receitas provenientes de prestação de serviços',
            'allows_transactions' => false,
            'active' => true,
            'level' => 2,
            'order' => 2,
            'created_by' => $user->id,
        ]);

        // Nível 3 - Subcategorias de Serviços
        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $servicos->id,
            'code' => '1.2.1',
            'name' => 'Consultorias',
            'description' => 'Receitas de serviços de consultoria',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 1,
            'created_by' => $user->id,
        ]);

        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $servicos->id,
            'code' => '1.2.2',
            'name' => 'Manutenção',
            'description' => 'Receitas de serviços de manutenção e suporte',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 2,
            'created_by' => $user->id,
        ]);

        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $servicos->id,
            'code' => '1.2.3',
            'name' => 'Treinamentos',
            'description' => 'Receitas de serviços de treinamento e capacitação',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 3,
            'created_by' => $user->id,
        ]);

        // 1.3 - OUTRAS RECEITAS (Nível 2)
        $outrasReceitas = $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $entradas->id,
            'code' => '1.3',
            'name' => 'Outras Receitas',
            'description' => 'Demais receitas não relacionadas com a atividade principal',
            'allows_transactions' => false,
            'active' => true,
            'level' => 2,
            'order' => 3,
            'created_by' => $user->id,
        ]);

        // Nível 3 - Subcategorias de Outras Receitas
        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $outrasReceitas->id,
            'code' => '1.3.1',
            'name' => 'Juros Recebidos',
            'description' => 'Receitas financeiras de juros e aplicações',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 1,
            'created_by' => $user->id,
        ]);

        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $outrasReceitas->id,
            'code' => '1.3.2',
            'name' => 'Aluguéis',
            'description' => 'Receitas de aluguel de imóveis ou equipamentos',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 2,
            'created_by' => $user->id,
        ]);

        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $outrasReceitas->id,
            'code' => '1.3.3',
            'name' => 'Comissões',
            'description' => 'Receitas de comissões e bonificações',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 3,
            'created_by' => $user->id,
        ]);

        // 2 - SAÍDAS (Nível 1)
        $saidas = $this->createAccount([
            'tenant_id' => $tenantId,
            'code' => '2',
            'name' => 'Saídas',
            'description' => 'Todas as despesas e saídas de recursos',
            'allows_transactions' => false,
            'active' => true,
            'level' => 1,
            'order' => 2,
            'created_by' => $user->id,
        ]);

        // 2.1 - FORNECEDORES (Nível 2)
        $fornecedores = $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $saidas->id,
            'code' => '2.1',
            'name' => 'Fornecedores',
            'description' => 'Despesas com fornecedores de produtos',
            'allows_transactions' => false,
            'active' => true,
            'level' => 2,
            'order' => 1,
            'created_by' => $user->id,
        ]);

        // Nível 3 - Subcategorias de Fornecedores
        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $fornecedores->id,
            'code' => '2.1.1',
            'name' => 'Mercadorias para Revenda',
            'description' => 'Compras de produtos para revenda',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 1,
            'default_purchase' => true,
            'created_by' => $user->id,
        ]);

        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $fornecedores->id,
            'code' => '2.1.2',
            'name' => 'Matéria-Prima',
            'description' => 'Compras de insumos para produção',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 2,
            'created_by' => $user->id,
        ]);

        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $fornecedores->id,
            'code' => '2.1.3',
            'name' => 'Embalagens',
            'description' => 'Despesas com materiais para embalagem',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 3,
            'created_by' => $user->id,
        ]);

        // 2.2 - DESPESAS ADMINISTRATIVAS (Nível 2)
        $despesasAdm = $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $saidas->id,
            'code' => '2.2',
            'name' => 'Despesas Administrativas',
            'description' => 'Despesas relacionadas à administração do negócio',
            'allows_transactions' => false,
            'active' => true,
            'level' => 2,
            'order' => 2,
            'created_by' => $user->id,
        ]);

        // Nível 3 - Subcategorias de Despesas Administrativas
        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $despesasAdm->id,
            'code' => '2.2.1',
            'name' => 'Material de Escritório',
            'description' => 'Despesas com materiais de escritório e suprimentos',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 1,
            'created_by' => $user->id,
        ]);

        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $despesasAdm->id,
            'code' => '2.2.2',
            'name' => 'Serviços de Terceiros',
            'description' => 'Despesas com serviços prestados por terceiros',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 2,
            'created_by' => $user->id,
        ]);

        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $despesasAdm->id,
            'code' => '2.2.3',
            'name' => 'Software e Tecnologia',
            'description' => 'Despesas com software, sistemas e serviços de tecnologia',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 3,
            'created_by' => $user->id,
        ]);

        // 2.3 - DESPESAS COM PESSOAL (Nível 2)
        $despesasPessoal = $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $saidas->id,
            'code' => '2.3',
            'name' => 'Despesas com Pessoal',
            'description' => 'Despesas relacionadas aos funcionários',
            'allows_transactions' => false,
            'active' => true,
            'level' => 2,
            'order' => 3,
            'created_by' => $user->id,
        ]);

        // Nível 3 - Subcategorias de Despesas com Pessoal
        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $despesasPessoal->id,
            'code' => '2.3.1',
            'name' => 'Salários',
            'description' => 'Folha de pagamento e salários',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 1,
            'created_by' => $user->id,
        ]);

        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $despesasPessoal->id,
            'code' => '2.3.2',
            'name' => 'INSS',
            'description' => 'Contribuições previdenciárias',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 2,
            'created_by' => $user->id,
        ]);

        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $despesasPessoal->id,
            'code' => '2.3.3',
            'name' => 'FGTS',
            'description' => 'Fundo de Garantia por Tempo de Serviço',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 3,
            'created_by' => $user->id,
        ]);

        // 2.4 - DESPESAS FISCAIS (Nível 2)
        $despesasFiscais = $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $saidas->id,
            'code' => '2.4',
            'name' => 'Despesas Fiscais',
            'description' => 'Impostos e taxas',
            'allows_transactions' => false,
            'active' => true,
            'level' => 2,
            'order' => 4,
            'created_by' => $user->id,
        ]);

        // Nível 3 - Subcategorias de Despesas Fiscais
        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $despesasFiscais->id,
            'code' => '2.4.1',
            'name' => 'Impostos Federais',
            'description' => 'IRPJ, CSLL, PIS, COFINS, etc.',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 1,
            'created_by' => $user->id,
        ]);

        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $despesasFiscais->id,
            'code' => '2.4.2',
            'name' => 'Impostos Estaduais',
            'description' => 'ICMS e outros',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 2,
            'created_by' => $user->id,
        ]);

        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $despesasFiscais->id,
            'code' => '2.4.3',
            'name' => 'Impostos Municipais',
            'description' => 'ISS, IPTU e outros',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 3,
            'created_by' => $user->id,
        ]);

        // 2.5 - DESPESAS FINANCEIRAS (Nível 2)
        $despesasFinanceiras = $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $saidas->id,
            'code' => '2.5',
            'name' => 'Despesas Financeiras',
            'description' => 'Despesas relacionadas a operações financeiras',
            'allows_transactions' => false,
            'active' => true,
            'level' => 2,
            'order' => 5,
            'created_by' => $user->id,
        ]);

        // Nível 3 - Subcategorias de Despesas Financeiras
        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $despesasFinanceiras->id,
            'code' => '2.5.1',
            'name' => 'Juros Pagos',
            'description' => 'Juros de empréstimos e financiamentos',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 1,
            'created_by' => $user->id,
        ]);

        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $despesasFinanceiras->id,
            'code' => '2.5.2',
            'name' => 'Tarifas Bancárias',
            'description' => 'Despesas com manutenção de contas e serviços bancários',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 2,
            'created_by' => $user->id,
        ]);

        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $despesasFinanceiras->id,
            'code' => '2.5.3',
            'name' => 'IOF',
            'description' => 'Imposto sobre Operações Financeiras',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 3,
            'created_by' => $user->id,
        ]);

        // 2.6 - DESPESAS OPERACIONAIS (Nível 2)
        $despesasOperacionais = $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $saidas->id,
            'code' => '2.6',
            'name' => 'Despesas Operacionais',
            'description' => 'Despesas relacionadas à operação do negócio',
            'allows_transactions' => false,
            'active' => true,
            'level' => 2,
            'order' => 6,
            'created_by' => $user->id,
        ]);

        // Nível 3 - Subcategorias de Despesas Operacionais
        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $despesasOperacionais->id,
            'code' => '2.6.1',
            'name' => 'Água e Esgoto',
            'description' => 'Despesas com serviços de água e esgoto',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 1,
            'created_by' => $user->id,
        ]);

        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $despesasOperacionais->id,
            'code' => '2.6.2',
            'name' => 'Energia Elétrica',
            'description' => 'Despesas com energia elétrica',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 2,
            'created_by' => $user->id,
        ]);

        $this->createAccount([
            'tenant_id' => $tenantId,
            'parent_id' => $despesasOperacionais->id,
            'code' => '2.6.3',
            'name' => 'Telefone e Internet',
            'description' => 'Despesas com telefonia e internet',
            'allows_transactions' => true,
            'active' => true,
            'level' => 3,
            'order' => 3,
            'created_by' => $user->id,
        ]);
    }

    /**
     * Método auxiliar para criar uma conta no plano de contas
     */
    private function createAccount(array $data): ChartAccount
    {
        $account = new ChartAccount;
        $account->id = Str::ulid();

        // Buscar sequential_id específico para este tenant
        $maxSequential = ChartAccount::where('tenant_id', $data['tenant_id'])
            ->max('sequential_id') ?? 0;
        $account->sequential_id = $maxSequential + 1;

        foreach ($data as $key => $value) {
            $account->{$key} = $value;
        }

        $account->save();

        return $account;
    }
}
