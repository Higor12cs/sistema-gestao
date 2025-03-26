<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountReconciliationController;
use App\Http\Controllers\AccountTransferController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ChartAccountController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\KardexController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PayableController;
use App\Http\Controllers\PayablePaymentController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\ReceivablePaymentController;
use App\Http\Controllers\Reports\OrderReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckRoutePermissionMiddleware;
use App\Http\Middleware\SetCurrentTenantPermissionMiddleware;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Redirecionamento da raiz
Route::redirect('/', '/home');

// Rotas de autenticação
Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => Inertia::render('Auth/Login'))->name('login');
    Route::post('/login', LoginController::class)->name('login.attempt');
    Route::get('/registrar', fn() => Inertia::render('Auth/Register'))->name('register');
    Route::post('/registrar', RegisterController::class)->name('register.attempt');
});

Route::post('/logout', LogoutController::class)->name('logout')->middleware('auth');

// Rotas protegidas
Route::middleware(['auth', SetCurrentTenantPermissionMiddleware::class, CheckRoutePermissionMiddleware::class])->group(function () {
    // API routes
    Route::prefix('/api')->as('api.')->group(function () {
        Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
        Route::get('/suppliers/search', [SupplierController::class, 'search'])->name('suppliers.search');
        Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
        Route::get('/brands/search', [BrandController::class, 'search'])->name('brands.search');
        Route::get('/sellers/search', [SellerController::class, 'search'])->name('sellers.search');
        Route::get('/sections/search', [SectionController::class, 'search'])->name('sections.search');
        Route::get('/groups/search', [GroupController::class, 'search'])->name('groups.search');
        Route::get('/accounts/search', [AccountController::class, 'search'])->name('accounts.search');
        Route::get('/payment-methods/search', [PaymentMethodController::class, 'search'])->name('payment-methods.search');
        Route::get('/roles/search', [RoleController::class, 'search'])->name('roles.search');
        Route::get('/chart-accounts/search', [ChartAccountController::class, 'search'])->name('chart-accounts.search');
        Route::get('/users/search', [UserController::class, 'search'])->name('users.search');
    });

    // 1. INÍCIO
    Route::get('/home', fn() => Inertia::render('Home/Index'))->name('home.index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // 2. CADASTROS
    // Clientes
    Route::controller(CustomerController::class)->prefix('clientes')->name('customers.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{customer:sequential_id}/editar', 'edit')->name('edit');
        Route::put('/{customer}', 'update')->name('update');
        Route::delete('/{customer}', 'destroy')->name('destroy');
    });

    // Fornecedores
    Route::controller(SupplierController::class)->prefix('fornecedores')->name('suppliers.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{supplier:sequential_id}/editar', 'edit')->name('edit');
        Route::put('/{supplier}', 'update')->name('update');
        Route::delete('/{supplier}', 'destroy')->name('destroy');
    });

    // 3. VENDAS
    // Pedidos
    Route::controller(OrderController::class)->prefix('pedidos')->name('orders.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{order:sequential_id}/editar', 'edit')->name('edit');
        Route::get('/{order:sequential_id}', 'show')->name('show');
        Route::put('/{order}', 'update')->name('update');
        Route::delete('/{order}', 'destroy')->name('destroy');
        Route::get('/{order:sequential_id}/recebiveis/criar', 'createReceivables')->name('create-receivables');
        Route::post('/{order}/recebiveis', 'storeReceivables')->name('store-receivables');
        Route::get('/imprimir/{order:sequential_id}', 'print')->name('print');
    });

    // 4. COMPRAS
    Route::controller(PurchaseController::class)->prefix('compras')->name('purchases.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{purchase:sequential_id}/editar', 'edit')->name('edit');
        Route::get('/{purchase:sequential_id}', 'show')->name('show');
        Route::put('/{purchase}', 'update')->name('update');
        Route::delete('/{purchase}', 'destroy')->name('destroy');
        Route::get('/{purchase:sequential_id}/pagaveis/criar', 'createPayables')->name('create-payables');
        Route::post('/{purchase}/pagaveis', 'storePayables')->name('store-payables');
    });

    // 5. FINANCEIRO
    // Recebíveis e seus pagamentos
    Route::prefix('recebiveis')->group(function () {
        // Pagamentos de recebíveis
        Route::controller(ReceivablePaymentController::class)->prefix('pagamentos')->name('receivables.payments.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/novo', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{payment:sequential_id}', 'show')->name('show');
            Route::delete('/{payment}', 'destroy')->name('destroy');
        });

        // Recebíveis
        Route::controller(ReceivableController::class)->name('receivables.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/criar', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::delete('/', 'destroy')->name('destroy');
            Route::get('/{receivable}/editar', 'edit')->name('edit');
            Route::post('/{receivable}', 'update')->name('update');
        });
    });

    // Pagáveis e seus pagamentos
    Route::prefix('pagaveis')->group(function () {
        // Pagamentos de pagáveis
        Route::controller(PayablePaymentController::class)->prefix('pagamentos')->name('payables.payments.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/novo', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{payment:sequential_id}', 'show')->name('show');
            Route::delete('/{payment}', 'destroy')->name('destroy');
        });

        // Pagáveis
        Route::controller(PayableController::class)->name('payables.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/criar', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::delete('/', 'destroy')->name('destroy');
            Route::get('/{payable}/editar', 'edit')->name('edit');
            Route::post('/{payable}', 'update')->name('update');
        });
    });

    // Contas
    Route::controller(AccountController::class)->prefix('contas')->name('accounts.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{account:sequential_id}/editar', 'edit')->name('edit');
        Route::put('/{account}', 'update')->name('update');
        Route::delete('/{account}', 'destroy')->name('destroy');
    });

    // Transferências entre contas
    Route::controller(AccountTransferController::class)->prefix('transferencias')->name('account-transfers.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{accountTransfer:sequential_id}', 'show')->name('show');
        Route::delete('/{accountTransfer}', 'destroy')->name('destroy');
    });

    // Conciliação bancária
    Route::controller(AccountReconciliationController::class)->prefix('conciliacao')->name('account-reconciliation.')->group(function () {
        Route::get('/', 'selectAccount')->name('select');
        Route::get('/{account}', 'index')->name('index');
        Route::post('/{transaction}', 'update')->name('update');
        Route::post('/', 'bulkUpdate')->name('bulk-update');
    });

    // Métodos de Pagamento
    Route::controller(PaymentMethodController::class)->prefix('formas-pagamento')->name('payment-methods.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{paymentMethod:sequential_id}/editar', 'edit')->name('edit');
        Route::put('/{paymentMethod}', 'update')->name('update');
        Route::delete('/{paymentMethod}', 'destroy')->name('destroy');
    });

    // Planos de Contas
    Route::controller(ChartAccountController::class)->prefix('plano-contas')->name('chart-accounts.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{chartAccount:sequential_id}/editar', 'edit')->name('edit');
        Route::put('/{chartAccount}', 'update')->name('update');
        Route::delete('/{chartAccount}', 'destroy')->name('destroy');
    });

    // 6. ESTOQUE
    // Produtos
    Route::controller(ProductController::class)->prefix('produtos')->name('products.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{product:sequential_id}/editar', 'edit')->name('edit');
        Route::put('/{product}', 'update')->name('update');
        Route::delete('/{product}', 'destroy')->name('destroy');
    });

    // Estoque
    Route::controller(StockController::class)->prefix('estoque')->name('stocks.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{stock:sequential_id}/ajuste', 'adjust')->name('adjust');
        Route::post('/{stock}/ajuste', 'storeAdjustment')->name('store-adjustment');
    });

    // Kardex
    Route::get('/kardex', [KardexController::class, 'index'])->name('kardex.index');

    // Atributos
    // Marcas
    Route::controller(BrandController::class)->prefix('marcas')->name('brands.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{brand:sequential_id}/editar', 'edit')->name('edit');
        Route::put('/{brand}', 'update')->name('update');
        Route::delete('/{brand}', 'destroy')->name('destroy');
    });

    // Seções
    Route::controller(SectionController::class)->prefix('secoes')->name('sections.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{section:sequential_id}/editar', 'edit')->name('edit');
        Route::put('/{section}', 'update')->name('update');
        Route::delete('/{section}', 'destroy')->name('destroy');
    });

    // Grupos
    Route::controller(GroupController::class)->prefix('grupos')->name('groups.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{group:sequential_id}/editar', 'edit')->name('edit');
        Route::put('/{group}', 'update')->name('update');
        Route::delete('/{group}', 'destroy')->name('destroy');
    });

    // 7. RELATÓRIOS
    // Relatórios de pedidos
    Route::prefix('relatorios')->name('reports.')->group(function () {
        Route::prefix('pedidos')->name('orders.')->group(function () {
            Route::get('/', fn() => Inertia::render('Reports/Orders/Index'))->name('index');
            Route::get('/analiticos', fn() => Inertia::render('Reports/Orders/Analytical'))->name('analyticals');
            Route::get('/analiticos/imprimir', [OrderReportController::class, 'analytical'])->name('analyticals.print');
            Route::get('/sinteticos', fn() => Inertia::render('Reports/Orders/Synthetic'))->name('synthetics');
            Route::get('/sinteticos/imprimir', [OrderReportController::class, 'synthetic'])->name('synthetics.print');
        });
    });

    // 8. CONFIGURAÇÕES
    // Usuários
    Route::controller(UserController::class)->prefix('usuarios')->name('users.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{sequential_id}/editar', 'edit')->name('edit');
        Route::put('/{user}', 'update')->name('update');
        Route::delete('/{user}', 'destroy')->name('destroy');
    });

    // Vendedores
    Route::controller(SellerController::class)->prefix('vendedores')->name('sellers.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{seller:sequential_id}/editar', 'edit')->name('edit');
        Route::put('/{seller}', 'update')->name('update');
        Route::delete('/{seller}', 'destroy')->name('destroy');
    });

    // Papéis (Roles)
    Route::controller(RoleController::class)->prefix('papeis')->name('roles.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{role}/editar', 'edit')->name('edit');
        Route::put('/{role}', 'update')->name('update');
        Route::delete('/{role}', 'destroy')->name('destroy');
    });

    Route::controller(ConfigurationController::class)->prefix('configuracoes')->name('configurations.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{configuration:sequential_id}/editar', 'edit')->name('edit');
        Route::put('/{configuration}', 'update')->name('update');
    });
});
