<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Brand;
use App\Models\ChartAccount;
use App\Models\Customer;
use App\Models\Group;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payable;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Receivable;
use App\Models\Section;
use App\Models\Seller;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestSeeder extends Seeder
{
    public function run(Tenant $tenant, User $user): void
    {
        $this->createSellers($tenant, $user);
        $brands = $this->createBrands($tenant, $user);
        $sections = $this->createSections($tenant, $user);
        $groups = $this->createGroups($tenant, $user, $sections);
        $suppliers = $this->createSuppliers($tenant, $user);
        $paymentMethods = $this->createPaymentMethods($tenant, $user);
        $accounts = $this->createAccounts($tenant, $user);
        $products = $this->createProducts($tenant, $user, $brands, $groups);
        $customers = $this->createCustomers($tenant, $user);

        $defaultReceivableAccount = ChartAccount::where('tenant_id', $tenant->id)
            ->where('default_receivable', true)
            ->first();

        $defaultPurchaseAccount = ChartAccount::where('tenant_id', $tenant->id)
            ->where('default_purchase', true)
            ->first();

        if (! $defaultReceivableAccount || ! $defaultPurchaseAccount) {
            $this->command->warn('Default chart accounts not found. Make sure ChartAccountSeeder has been run.');
        }

        $this->createPurchases(
            $tenant,
            $user,
            $suppliers,
            $products,
            $paymentMethods,
            $accounts,
            $defaultPurchaseAccount ? $defaultPurchaseAccount->id : null
        );

        $this->createOrders(
            $tenant,
            $user,
            $customers,
            $products,
            $paymentMethods,
            $accounts,
            $defaultReceivableAccount ? $defaultReceivableAccount->id : null
        );
    }

    private function createSellers(Tenant $tenant, User $user): array
    {
        $sellers = [
            ['name' => 'João Silva'],
            ['name' => 'Maria Oliveira'],
            ['name' => 'Pedro Santos'],
            ['name' => 'Ana Souza'],
            ['name' => 'Carlos Ferreira'],
        ];

        $createdSellers = [];

        foreach ($sellers as $sellerData) {
            $seller = Seller::create([
                'tenant_id' => $tenant->id,
                'name' => $sellerData['name'],
                'active' => true,
                'created_by' => $user->id,
            ]);

            $createdSellers[] = $seller;
        }

        return $createdSellers;
    }

    private function createBrands(Tenant $tenant, User $user): array
    {
        $brands = [
            ['name' => 'Nike'],
            ['name' => 'Adidas'],
            ['name' => 'Puma'],
            ['name' => 'Under Armour'],
            ['name' => 'Asics'],
            ['name' => 'Reebok'],
            ['name' => 'New Balance'],
            ['name' => 'Mizuno'],
            ['name' => 'Oakley'],
            ['name' => 'Wilson'],
        ];

        $createdBrands = [];

        foreach ($brands as $brandData) {
            $brand = Brand::create([
                'tenant_id' => $tenant->id,
                'name' => $brandData['name'],
                'active' => true,
                'created_by' => $user->id,
            ]);

            $createdBrands[$brand->name] = $brand;
        }

        return $createdBrands;
    }

    private function createSections(Tenant $tenant, User $user): array
    {
        $sections = [
            ['name' => 'Calçados'],
            ['name' => 'Roupas'],
            ['name' => 'Acessórios'],
            ['name' => 'Equipamentos'],
        ];

        $createdSections = [];

        foreach ($sections as $sectionData) {
            $section = Section::create([
                'tenant_id' => $tenant->id,
                'name' => $sectionData['name'],
                'active' => true,
                'created_by' => $user->id,
            ]);

            $createdSections[$sectionData['name']] = $section;
        }

        return $createdSections;
    }

    private function createGroups(Tenant $tenant, User $user, array $sections): array
    {
        $groups = [
            'Calçados' => [
                ['name' => 'Tênis de Corrida'],
                ['name' => 'Chuteiras'],
                ['name' => 'Tênis de Basquete'],
                ['name' => 'Tênis Casual'],
                ['name' => 'Sandálias'],
            ],
            'Roupas' => [
                ['name' => 'Camisetas'],
                ['name' => 'Shorts'],
                ['name' => 'Calças'],
                ['name' => 'Jaquetas'],
                ['name' => 'Agasalhos'],
            ],
            'Acessórios' => [
                ['name' => 'Meias'],
                ['name' => 'Bonés'],
                ['name' => 'Mochilas'],
                ['name' => 'Óculos'],
                ['name' => 'Relógios'],
            ],
            'Equipamentos' => [
                ['name' => 'Bolas'],
                ['name' => 'Raquetes'],
                ['name' => 'Luvas'],
                ['name' => 'Proteções'],
                ['name' => 'Fitness'],
            ],
        ];

        $createdGroups = [];

        foreach ($groups as $sectionName => $sectionGroups) {
            foreach ($sectionGroups as $groupData) {
                $group = Group::create([
                    'tenant_id' => $tenant->id,
                    'section_id' => $sections[$sectionName]->id,
                    'name' => $groupData['name'],
                    'active' => true,
                    'created_by' => $user->id,
                ]);

                $createdGroups[$groupData['name']] = $group;
            }
        }

        return $createdGroups;
    }

    private function createSuppliers(Tenant $tenant, User $user): array
    {
        $suppliers = [
            [
                'first_name' => 'Distribuidora',
                'last_name' => 'Esportiva',
                'legal_name' => 'Distribuidora Esportiva LTDA',
                'cpf_cnpj' => '12.345.678/0001-90',
                'ie' => '123456789',
                'email' => 'contato@distribuidoraesportiva.com.br',
                'phone' => '(11) 3456-7890',
                'whatsapp' => '(11) 98765-4321',
                'address' => 'Avenida das Esportivas',
                'number' => '1000',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
            ],
            [
                'first_name' => 'Importadora',
                'last_name' => 'Athletic',
                'legal_name' => 'Importadora Athletic S/A',
                'cpf_cnpj' => '98.765.432/0001-10',
                'ie' => '987654321',
                'email' => 'compras@importadoraathletic.com.br',
                'phone' => '(21) 2345-6789',
                'whatsapp' => '(21) 97654-3210',
                'address' => 'Rua do Esporte',
                'number' => '500',
                'neighborhood' => 'Barra',
                'city' => 'Rio de Janeiro',
                'state' => 'RJ',
            ],
            [
                'first_name' => 'Fornecedora',
                'last_name' => 'Olímpica',
                'legal_name' => 'Fornecedora Olímpica EIRELI',
                'cpf_cnpj' => '45.678.901/0001-23',
                'ie' => '456789012',
                'email' => 'vendas@fornecedoraolimpica.com.br',
                'phone' => '(31) 3456-7890',
                'whatsapp' => '(31) 97654-3210',
                'address' => 'Avenida dos Atletas',
                'number' => '700',
                'neighborhood' => 'Savassi',
                'city' => 'Belo Horizonte',
                'state' => 'MG',
            ],
            [
                'first_name' => 'Suprimentos',
                'last_name' => 'Esportivos',
                'legal_name' => 'Suprimentos Esportivos ME',
                'cpf_cnpj' => '56.789.012/0001-34',
                'ie' => '567890123',
                'email' => 'atendimento@suprimentosesportivos.com.br',
                'phone' => '(41) 3456-7890',
                'whatsapp' => '(41) 97654-3210',
                'address' => 'Rua das Quadras',
                'number' => '300',
                'neighborhood' => 'Batel',
                'city' => 'Curitiba',
                'state' => 'PR',
            ],
            [
                'first_name' => 'Global',
                'last_name' => 'Sports',
                'legal_name' => 'Global Sports Importação LTDA',
                'cpf_cnpj' => '67.890.123/0001-45',
                'ie' => '678901234',
                'email' => 'comercial@globalsports.com.br',
                'phone' => '(51) 3456-7890',
                'whatsapp' => '(51) 97654-3210',
                'address' => 'Avenida dos Campeões',
                'number' => '1500',
                'neighborhood' => 'Moinhos',
                'city' => 'Porto Alegre',
                'state' => 'RS',
            ],
        ];

        $createdSuppliers = [];

        foreach ($suppliers as $supplierData) {
            $supplier = Supplier::create([
                'tenant_id' => $tenant->id,
                'first_name' => $supplierData['first_name'],
                'last_name' => $supplierData['last_name'],
                'legal_name' => $supplierData['legal_name'],
                'cpf_cnpj' => $supplierData['cpf_cnpj'],
                'ie' => $supplierData['ie'],
                'email' => $supplierData['email'],
                'phone' => $supplierData['phone'],
                'whatsapp' => $supplierData['whatsapp'],
                'address' => $supplierData['address'],
                'number' => $supplierData['number'],
                'neighborhood' => $supplierData['neighborhood'],
                'city' => $supplierData['city'],
                'state' => $supplierData['state'],
                'country' => 'Brasil',
                'active' => true,
                'created_by' => $user->id,
            ]);

            $createdSuppliers[] = $supplier;
        }

        return $createdSuppliers;
    }

    private function createProducts(Tenant $tenant, User $user, array $brands, array $groups): array
    {
        $products = [
            [
                'name' => 'Nike Air Zoom Pegasus 38',
                'brand' => 'Nike',
                'group' => 'Tênis de Corrida',
                'description' => 'Tênis de corrida com amortecimento responsivo e confortável para treinos diários.',
                'sku' => 'NK-PG38-001',
                'cost' => 300.00,
                'price' => 599.90,
            ],
            [
                'name' => 'Adidas Ultraboost 21',
                'brand' => 'Adidas',
                'group' => 'Tênis de Corrida',
                'description' => 'Tênis com tecnologia Boost para corridas de alto desempenho e conforto superior.',
                'sku' => 'AD-UB21-001',
                'cost' => 350.00,
                'price' => 699.90,
            ],
            [
                'name' => 'Asics Gel Nimbus 23',
                'brand' => 'Asics',
                'group' => 'Tênis de Corrida',
                'description' => 'Tênis com amortecimento GEL para corridas de longa distância.',
                'sku' => 'AS-GN23-001',
                'cost' => 320.00,
                'price' => 649.90,
            ],
            [
                'name' => 'Nike Mercurial Superfly 8',
                'brand' => 'Nike',
                'group' => 'Chuteiras',
                'description' => 'Chuteira de campo com cano alto para velocidade e controle.',
                'sku' => 'NK-MS8-001',
                'cost' => 280.00,
                'price' => 549.90,
            ],
            [
                'name' => 'Adidas Predator Freak',
                'brand' => 'Adidas',
                'group' => 'Chuteiras',
                'description' => 'Chuteira de campo com tecnologia Demonskin para controle excepcional da bola.',
                'sku' => 'AD-PF-001',
                'cost' => 290.00,
                'price' => 579.90,
            ],
            [
                'name' => 'Puma Future Z',
                'brand' => 'Puma',
                'group' => 'Chuteiras',
                'description' => 'Chuteira de campo com ajuste adaptável e tração superior.',
                'sku' => 'PM-FZ-001',
                'cost' => 250.00,
                'price' => 499.90,
            ],
            [
                'name' => 'Nike LeBron 18',
                'brand' => 'Nike',
                'group' => 'Tênis de Basquete',
                'description' => 'Tênis de basquete com amortecimento Max Air e Zoom Air para conforto e desempenho.',
                'sku' => 'NK-LB18-001',
                'cost' => 380.00,
                'price' => 799.90,
            ],
            [
                'name' => 'Under Armour Curry 8',
                'brand' => 'Under Armour',
                'group' => 'Tênis de Basquete',
                'description' => 'Tênis leve e responsivo desenvolvido para o jogo rápido e ágil.',
                'sku' => 'UA-C8-001',
                'cost' => 350.00,
                'price' => 699.90,
            ],
            [
                'name' => 'Nike Dri-FIT Running',
                'brand' => 'Nike',
                'group' => 'Camisetas',
                'description' => 'Camiseta de corrida com tecnologia de absorção de suor para manter o corpo seco.',
                'sku' => 'NK-DFR-001',
                'cost' => 45.00,
                'price' => 99.90,
            ],
            [
                'name' => 'Adidas Own The Run',
                'brand' => 'Adidas',
                'group' => 'Camisetas',
                'description' => 'Camiseta para corrida com tecnologia Climacool para manejo de umidade.',
                'sku' => 'AD-OTR-001',
                'cost' => 50.00,
                'price' => 119.90,
            ],
            [
                'name' => 'Puma Run Favorite',
                'brand' => 'Puma',
                'group' => 'Camisetas',
                'description' => 'Camiseta leve para corrida com tecido de secagem rápida.',
                'sku' => 'PM-RF-001',
                'cost' => 40.00,
                'price' => 89.90,
            ],
            [
                'name' => 'Nike Flex Stride',
                'brand' => 'Nike',
                'group' => 'Shorts',
                'description' => 'Short de corrida com forro interno e bolsos funcionais.',
                'sku' => 'NK-FS-001',
                'cost' => 60.00,
                'price' => 129.90,
            ],
            [
                'name' => 'Adidas Run It',
                'brand' => 'Adidas',
                'group' => 'Shorts',
                'description' => 'Short leve com tecnologia de gestão de umidade para corrida.',
                'sku' => 'AD-RI-001',
                'cost' => 55.00,
                'price' => 119.90,
            ],
            [
                'name' => 'Nike Featherlight',
                'brand' => 'Nike',
                'group' => 'Bonés',
                'description' => 'Boné leve com tecnologia Dri-FIT e ajuste traseiro.',
                'sku' => 'NK-FL-001',
                'cost' => 35.00,
                'price' => 79.90,
            ],
            [
                'name' => 'Adidas Baseball',
                'brand' => 'Adidas',
                'group' => 'Bonés',
                'description' => 'Boné estilo baseball com logo bordado e ajuste traseiro.',
                'sku' => 'AD-BB-001',
                'cost' => 30.00,
                'price' => 69.90,
            ],
            [
                'name' => 'Nike Brasilia',
                'brand' => 'Nike',
                'group' => 'Mochilas',
                'description' => 'Mochila espaçosa com múltiplos compartimentos para treino.',
                'sku' => 'NK-BR-001',
                'cost' => 90.00,
                'price' => 199.90,
            ],
            [
                'name' => 'Adidas Power 5',
                'brand' => 'Adidas',
                'group' => 'Mochilas',
                'description' => 'Mochila com compartimento para laptop e bolsos organizadores.',
                'sku' => 'AD-P5-001',
                'cost' => 85.00,
                'price' => 189.90,
            ],
            [
                'name' => 'Oakley Sutro',
                'brand' => 'Oakley',
                'group' => 'Óculos',
                'description' => 'Óculos esportivos com lentes Prizm para ciclismo e corrida.',
                'sku' => 'OK-ST-001',
                'cost' => 250.00,
                'price' => 499.90,
            ],
            [
                'name' => 'Nike Strike',
                'brand' => 'Nike',
                'group' => 'Bolas',
                'description' => 'Bola de futebol com design de alta visibilidade e construção durável.',
                'sku' => 'NK-ST-001',
                'cost' => 45.00,
                'price' => 99.90,
            ],
            [
                'name' => 'Wilson NBA Official',
                'brand' => 'Wilson',
                'group' => 'Bolas',
                'description' => 'Bola oficial da NBA para jogos indoor e outdoor.',
                'sku' => 'WL-NBA-001',
                'cost' => 120.00,
                'price' => 249.90,
            ],
            [
                'name' => 'Wilson Pro Staff',
                'brand' => 'Wilson',
                'group' => 'Raquetes',
                'description' => 'Raquete de tênis profissional com controle e precisão excepcionais.',
                'sku' => 'WL-PS-001',
                'cost' => 350.00,
                'price' => 799.90,
            ],
        ];

        $createdProducts = [];

        foreach ($products as $productData) {
            $brandId = null;
            if (isset($brands[$productData['brand']])) {
                $brandId = $brands[$productData['brand']]->id;
            }

            $groupId = null;
            if (isset($groups[$productData['group']])) {
                $groupId = $groups[$productData['group']]->id;
            }

            $product = Product::create([
                'tenant_id' => $tenant->id,
                'brand_id' => $brandId,
                'group_id' => $groupId,
                'name' => $productData['name'],
                'description' => $productData['description'],
                'sku' => $productData['sku'],
                'cost' => $productData['cost'],
                'price' => $productData['price'],
                'active' => true,
                'created_by' => $user->id,
            ]);

            $createdProducts[] = $product;
        }

        return $createdProducts;
    }

    private function createCustomers(Tenant $tenant, User $user): array
    {
        $customers = [
            [
                'first_name' => 'Roberto',
                'last_name' => 'Almeida',
                'email' => 'roberto.almeida@email.com',
                'phone' => '(11) 98765-4321',
                'birth_date' => '1985-06-15',
                'cpf_cnpj' => '123.456.789-00',
                'address' => 'Rua das Flores',
                'number' => '123',
                'neighborhood' => 'Jardim Primavera',
                'city' => 'São Paulo',
                'state' => 'SP',
            ],
            [
                'first_name' => 'Amanda',
                'last_name' => 'Costa',
                'email' => 'amanda.costa@email.com',
                'phone' => '(21) 97654-3210',
                'birth_date' => '1990-03-22',
                'cpf_cnpj' => '987.654.321-00',
                'address' => 'Avenida Central',
                'number' => '456',
                'neighborhood' => 'Centro',
                'city' => 'Rio de Janeiro',
                'state' => 'RJ',
            ],
            [
                'first_name' => 'Carlos',
                'last_name' => 'Ferreira',
                'email' => 'carlos.ferreira@email.com',
                'phone' => '(31) 96543-2109',
                'birth_date' => '1978-11-10',
                'cpf_cnpj' => '456.789.123-00',
                'address' => 'Rua dos Ipês',
                'number' => '789',
                'neighborhood' => 'Funcionários',
                'city' => 'Belo Horizonte',
                'state' => 'MG',
            ],
            [
                'first_name' => 'Juliana',
                'last_name' => 'Santos',
                'email' => 'juliana.santos@email.com',
                'phone' => '(41) 95432-1098',
                'birth_date' => '1995-07-30',
                'cpf_cnpj' => '789.123.456-00',
                'address' => 'Rua Bonita',
                'number' => '1001',
                'neighborhood' => 'Água Verde',
                'city' => 'Curitiba',
                'state' => 'PR',
            ],
            [
                'first_name' => 'Fernando',
                'last_name' => 'Oliveira',
                'email' => 'fernando.oliveira@email.com',
                'phone' => '(51) 94321-0987',
                'birth_date' => '1982-04-05',
                'cpf_cnpj' => '321.654.987-00',
                'address' => 'Avenida dos Lagos',
                'number' => '222',
                'neighborhood' => 'Menino Deus',
                'city' => 'Porto Alegre',
                'state' => 'RS',
            ],
            [
                'first_name' => 'Patrícia',
                'last_name' => 'Lima',
                'email' => 'patricia.lima@email.com',
                'phone' => '(81) 93210-9876',
                'birth_date' => '1988-09-17',
                'cpf_cnpj' => '654.987.321-00',
                'address' => 'Rua da Praia',
                'number' => '333',
                'neighborhood' => 'Boa Viagem',
                'city' => 'Recife',
                'state' => 'PE',
            ],
            [
                'first_name' => 'Marcos',
                'last_name' => 'Souza',
                'email' => 'marcos.souza@email.com',
                'phone' => '(71) 92109-8765',
                'birth_date' => '1975-12-03',
                'cpf_cnpj' => '852.741.963-00',
                'address' => 'Avenida Beira Mar',
                'number' => '444',
                'neighborhood' => 'Barra',
                'city' => 'Salvador',
                'state' => 'BA',
            ],
            [
                'first_name' => 'Luciana',
                'last_name' => 'Cardoso',
                'email' => 'luciana.cardoso@email.com',
                'phone' => '(85) 91098-7654',
                'birth_date' => '1993-02-28',
                'cpf_cnpj' => '741.852.963-00',
                'address' => 'Rua das Dunas',
                'number' => '555',
                'neighborhood' => 'Meireles',
                'city' => 'Fortaleza',
                'state' => 'CE',
            ],
            [
                'first_name' => 'Gustavo',
                'last_name' => 'Mendes',
                'email' => 'gustavo.mendes@email.com',
                'phone' => '(91) 90987-6543',
                'birth_date' => '1980-08-14',
                'cpf_cnpj' => '963.852.741-00',
                'address' => 'Avenida da Paz',
                'number' => '666',
                'neighborhood' => 'Umarizal',
                'city' => 'Belém',
                'state' => 'PA',
            ],
            [
                'first_name' => 'Camila',
                'last_name' => 'Rocha',
                'email' => 'camila.rocha@email.com',
                'phone' => '(92) 98765-4321',
                'birth_date' => '1998-05-20',
                'cpf_cnpj' => '159.753.852-00',
                'address' => 'Rua da Floresta',
                'number' => '777',
                'neighborhood' => 'Adrianópolis',
                'city' => 'Manaus',
                'state' => 'AM',
            ],
        ];

        $createdCustomers = [];

        foreach ($customers as $customerData) {
            $customer = Customer::create([
                'tenant_id' => $tenant->id,
                'first_name' => $customerData['first_name'],
                'last_name' => $customerData['last_name'],
                'email' => $customerData['email'],
                'phone' => $customerData['phone'],
                'birth_date' => $customerData['birth_date'],
                'cpf_cnpj' => $customerData['cpf_cnpj'],
                'address' => $customerData['address'],
                'number' => $customerData['number'],
                'neighborhood' => $customerData['neighborhood'],
                'city' => $customerData['city'],
                'state' => $customerData['state'],
                'country' => 'Brasil',
                'active' => true,
                'created_by' => $user->id,
            ]);

            $createdCustomers[] = $customer;
        }

        return $createdCustomers;
    }

    private function createPaymentMethods(Tenant $tenant, User $user): array
    {
        $paymentMethods = [
            ['name' => 'À VISTA', 'type' => 'cash'],
            ['name' => 'A PRAZO', 'type' => 'credit'],
            ['name' => 'CHEQUE PRAZO', 'type' => 'check'],
            ['name' => 'BOLETO', 'type' => 'billet'],
        ];

        $createdPaymentMethods = [];

        foreach ($paymentMethods as $paymentMethodData) {
            $paymentMethod = PaymentMethod::create([
                'tenant_id' => $tenant->id,
                'name' => $paymentMethodData['name'],
                'type' => $paymentMethodData['type'],
                'active' => true,
                'created_by' => $user->id,
            ]);

            $createdPaymentMethods[] = $paymentMethod;
        }

        return $createdPaymentMethods;
    }

    private function createAccounts(Tenant $tenant, User $user): array
    {
        $accounts = [
            [
                'name' => 'Caixa',
                'type' => 'cash',
                'current_balance' => 0,
                'active' => true,
            ],
            [
                'name' => 'Banco Itaú',
                'type' => 'checking',
                'bank_name' => 'Itaú',
                'agency' => '1234',
                'account_number' => '56789-0',
                'current_balance' => 0,
                'active' => true,
            ],
            [
                'name' => 'Banco Bradesco',
                'type' => 'checking',
                'bank_name' => 'Bradesco',
                'agency' => '5678',
                'account_number' => '12345-6',
                'current_balance' => 0,
                'active' => true,
            ],
        ];

        $createdAccounts = [];

        foreach ($accounts as $accountData) {
            $account = Account::create([
                'tenant_id' => $tenant->id,
                'name' => $accountData['name'],
                'type' => $accountData['type'],
                'bank_name' => $accountData['bank_name'] ?? null,
                'agency' => $accountData['agency'] ?? null,
                'account_number' => $accountData['account_number'] ?? null,
                'current_balance' => $accountData['current_balance'] ?? 0,
                'active' => $accountData['active'],
                'created_by' => $user->id,
            ]);

            $createdAccounts[] = $account;
        }

        return $createdAccounts;
    }

    private function createPurchases(
        Tenant $tenant,
        User $user,
        array $suppliers,
        array $products,
        array $paymentMethods,
        array $accounts,
        ?string $defaultChartAccountId
    ): void {
        if (! $defaultChartAccountId) {
            $this->command->warn('Default Purchase ChartAccount not found. Payables might cause errors.');

            return;
        }

        $startDate = Carbon::now()->subDays(90);
        $endDate = Carbon::now();

        for ($i = 0; $i < 30; $i++) {
            $supplier = $suppliers[array_rand($suppliers)];
            $purchaseDate = Carbon::createFromTimestamp(
                rand($startDate->timestamp, $endDate->timestamp)
            );

            DB::transaction(function () use ($tenant, $user, $supplier, $purchaseDate, $products, $paymentMethods, $i, $defaultChartAccountId) {
                $totalCost = 0;
                $discount = rand(0, 50);
                $fees = rand(0, 30);

                $purchase = Purchase::create([
                    'tenant_id' => $tenant->id,
                    'supplier_id' => $supplier->id,
                    'issue_date' => $purchaseDate,
                    'discount' => $discount,
                    'fees' => $fees,
                    'total_cost' => 0,
                    'observation' => "Compra de produtos #{$i} - ".$supplier->legal_name,
                    'created_by' => $user->id,
                ]);

                $numItems = rand(2, 5);
                $selectedProducts = array_rand($products, $numItems);
                if (! is_array($selectedProducts)) {
                    $selectedProducts = [$selectedProducts];
                }

                foreach ($selectedProducts as $productIndex) {
                    $product = $products[$productIndex];
                    $quantity = rand(1, 10);
                    $unitCost = $product->cost * (rand(90, 110) / 100);
                    $itemDiscount = rand(0, 20);
                    $itemFees = rand(0, 10);
                    $itemTotalCost = ($unitCost * $quantity) - $itemDiscount + $itemFees;

                    PurchaseItem::create([
                        'tenant_id' => $tenant->id,
                        'purchase_id' => $purchase->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_cost' => $unitCost,
                        'discount' => $itemDiscount,
                        'fees' => $itemFees,
                        'total_cost' => $itemTotalCost,
                        'created_by' => $user->id,
                    ]);

                    $totalCost += $itemTotalCost;
                }

                $finalTotalCost = $totalCost - $discount + $fees;
                $purchase->update([
                    'total_cost' => $finalTotalCost,
                ]);

                $paymentType = rand(0, 1);
                $paymentMethod = null;

                if ($paymentType == 0) {
                    foreach ($paymentMethods as $method) {
                        if ($method->name === 'CHEQUE PRAZO') {
                            $paymentMethod = $method;
                            break;
                        }
                    }
                } else {
                    foreach ($paymentMethods as $method) {
                        if ($method->name === 'BOLETO') {
                            $paymentMethod = $method;
                            break;
                        }
                    }
                }

                $dueDate = $purchaseDate->copy()->addDays(30);

                Payable::create([
                    'tenant_id' => $tenant->id,
                    'purchase_id' => $purchase->id,
                    'chart_account_id' => $defaultChartAccountId,
                    'supplier_id' => $supplier->id,
                    'payment_method_id' => $paymentMethod->id,
                    'is_manual' => false,
                    'issue_date' => $purchaseDate,
                    'due_date' => $dueDate,
                    'total_amount' => $finalTotalCost,
                    'paid_amount' => 0,
                    'fees' => 0,
                    'discount' => 0,
                    'remaining_amount' => $finalTotalCost,
                    'status' => 'pending',
                    'description' => 'Pagamento via '.$paymentMethod->name,
                    'created_by' => $user->id,
                ]);
            });
        }
    }

    private function createOrders(
        Tenant $tenant,
        User $user,
        array $customers,
        array $products,
        array $paymentMethods,
        array $accounts,
        ?string $defaultChartAccountId
    ): void {
        if (! $defaultChartAccountId) {
            $this->command->warn('Default Receivable ChartAccount not found. Receivables might cause errors.');

            return;
        }

        $startDate = Carbon::now()->subDays(60);
        $endDate = Carbon::now();

        for ($i = 0; $i < 50; $i++) {
            $customer = $customers[array_rand($customers)];
            $orderDate = Carbon::createFromTimestamp(
                rand($startDate->timestamp, $endDate->timestamp)
            );

            DB::transaction(function () use ($tenant, $user, $customer, $orderDate, $products, $paymentMethods, $i, $defaultChartAccountId) {
                $totalCost = 0;
                $totalPrice = 0;
                $discount = rand(0, 100);
                $fees = rand(0, 50);

                $order = Order::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $customer->id,
                    'issue_date' => $orderDate,
                    'discount' => $discount,
                    'fees' => $fees,
                    'total_cost' => 0,
                    'total_price' => 0,
                    'observation' => "PEDIDO CLIENTE #{$i} - ".$customer->first_name.' '.$customer->last_name,
                    'created_by' => $user->id,
                ]);

                $numItems = rand(1, 4);
                $selectedProducts = array_rand($products, $numItems);
                if (! is_array($selectedProducts)) {
                    $selectedProducts = [$selectedProducts];
                }

                foreach ($selectedProducts as $productIndex) {
                    $product = $products[$productIndex];
                    $quantity = rand(1, 3);
                    $unitCost = $product->cost;
                    $unitPrice = $product->price;
                    $itemDiscount = rand(0, 30);
                    $itemFees = rand(0, 15);

                    $itemTotalCost = $unitCost * $quantity;
                    $itemTotalPrice = ($unitPrice * $quantity) - $itemDiscount + $itemFees;

                    OrderItem::create([
                        'tenant_id' => $tenant->id,
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_cost' => $unitCost,
                        'unit_price' => $unitPrice,
                        'discount' => $itemDiscount,
                        'fees' => $itemFees,
                        'total_cost' => $itemTotalCost,
                        'total_price' => $itemTotalPrice,
                        'created_by' => $user->id,
                    ]);

                    $totalCost += $itemTotalCost;
                    $totalPrice += $itemTotalPrice;
                }

                $finalTotalPrice = $totalPrice - $discount + $fees;
                $order->update([
                    'total_cost' => $totalCost,
                    'total_price' => $finalTotalPrice,
                ]);

                $paymentType = rand(0, 1);
                $paymentMethod = null;

                if ($paymentType == 0) {
                    foreach ($paymentMethods as $method) {
                        if ($method->name === 'À VISTA') {
                            $paymentMethod = $method;
                            break;
                        }
                    }
                } else {
                    foreach ($paymentMethods as $method) {
                        if ($method->name === 'A PRAZO') {
                            $paymentMethod = $method;
                            break;
                        }
                    }
                }

                $dueDate = ($paymentMethod->name === 'À VISTA')
                    ? $orderDate
                    : $orderDate->copy()->addDays(30);

                Receivable::create([
                    'tenant_id' => $tenant->id,
                    'order_id' => $order->id,
                    'customer_id' => $customer->id,
                    'chart_account_id' => $defaultChartAccountId,
                    'payment_method_id' => $paymentMethod->id,
                    'is_manual' => false,
                    'issue_date' => $orderDate,
                    'due_date' => $dueDate,
                    'total_amount' => $finalTotalPrice,
                    'paid_amount' => 0,
                    'fees' => 0,
                    'discount' => 0,
                    'remaining_amount' => $finalTotalPrice,
                    'status' => 'pending',
                    'description' => 'Pagamento '.$paymentMethod->name,
                    'created_by' => $user->id,
                ]);
            });
        }
    }
}
