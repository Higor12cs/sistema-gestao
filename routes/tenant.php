<?php

declare(strict_types=1);

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountReconciliationController;
use App\Http\Controllers\AccountTransferController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CashFlowController;
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
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\ReceivablePaymentController;
use App\Http\Controllers\Reports\CustomerAbcReportController;
use App\Http\Controllers\Reports\OrderReportController;
use App\Http\Controllers\Reports\PayableReportController;
use App\Http\Controllers\Reports\ProductAbcReportController;
use App\Http\Controllers\Reports\ReceivableReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckRoutePermissionMiddleware;
use App\Http\Middleware\InitializeTenancyBySession;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    'auth',
    InitializeTenancyBySession::class,
    CheckRoutePermissionMiddleware::class,
])->group(function () {
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

    Route::get('/home', fn () => inertia('Home/Index'))->name('home.index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::controller(CustomerController::class)->prefix('clientes')->name('customers.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{customer}/editar', 'edit')->name('edit');
        Route::put('/{customer}', 'update')->name('update');
        Route::delete('/{customer}', 'destroy')->name('destroy');
    });

    Route::controller(SupplierController::class)->prefix('fornecedores')->name('suppliers.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{supplier}/editar', 'edit')->name('edit');
        Route::put('/{supplier}', 'update')->name('update');
        Route::delete('/{supplier}', 'destroy')->name('destroy');
    });

    Route::controller(POSController::class)->prefix('/pdv')->name('pos.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/process-sale', 'processSale')->name('process-sale');
        Route::get('/search-products', 'searchProducts')->name('search-products');
        Route::get('/barcode', 'getProductByBarcode')->name('barcode');
        Route::get('/receipt/{order}', 'printReceipt')->name('receipt');
    });

    Route::controller(OrderController::class)->prefix('pedidos')->name('orders.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{order}/editar', 'edit')->name('edit');
        Route::get('/{order}', 'show')->name('show');
        Route::put('/{order}', 'update')->name('update');
        Route::delete('/{order}', 'destroy')->name('destroy');
        Route::get('/{order}/recebiveis/criar', 'createReceivables')->name('create-receivables');
        Route::post('/{order}/recebiveis', 'storeReceivables')->name('store-receivables');
        Route::get('/imprimir/{order}', 'print')->name('print');
    });

    Route::controller(PurchaseController::class)->prefix('compras')->name('purchases.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{purchase}/editar', 'edit')->name('edit');
        Route::get('/{purchase}', 'show')->name('show');
        Route::put('/{purchase}', 'update')->name('update');
        Route::delete('/{purchase}', 'destroy')->name('destroy');
        Route::get('/{purchase}/pagaveis/criar', 'createPayables')->name('create-payables');
        Route::post('/{purchase}/pagaveis', 'storePayables')->name('store-payables');
    });

    Route::prefix('recebiveis')->group(function () {
        Route::controller(ReceivablePaymentController::class)->prefix('pagamentos')->name('receivables.payments.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/novo', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{payment}', 'show')->name('show');
            Route::delete('/{payment}', 'destroy')->name('destroy');
        });

        Route::controller(ReceivableController::class)->name('receivables.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/criar', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::delete('/', 'destroy')->name('destroy');
            Route::get('/{receivable}/editar', 'edit')->name('edit');
            Route::post('/{receivable}', 'update')->name('update');
        });
    });

    Route::prefix('pagaveis')->group(function () {
        Route::controller(PayablePaymentController::class)->prefix('pagamentos')->name('payables.payments.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/novo', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{payment}', 'show')->name('show');
            Route::delete('/{payment}', 'destroy')->name('destroy');
        });

        Route::controller(PayableController::class)->name('payables.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/criar', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::delete('/', 'destroy')->name('destroy');
            Route::get('/{payable}/editar', 'edit')->name('edit');
            Route::post('/{payable}', 'update')->name('update');
        });
    });

    Route::controller(AccountController::class)->prefix('contas')->name('accounts.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{account}/editar', 'edit')->name('edit');
        Route::put('/{account}', 'update')->name('update');
        Route::delete('/{account}', 'destroy')->name('destroy');
    });

    Route::controller(AccountTransferController::class)->prefix('transferencias')->name('account-transfers.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{accountTransfer}', 'show')->name('show');
        Route::delete('/{accountTransfer}', 'destroy')->name('destroy');
    });

    Route::controller(AccountReconciliationController::class)->prefix('conciliacao')->name('account-reconciliation.')->group(function () {
        Route::get('/', 'selectAccount')->name('select');
        Route::get('/{account}', 'index')->name('index');
        Route::post('/{transaction}', 'update')->name('update');
        Route::post('/', 'bulkUpdate')->name('bulk-update');
    });

    Route::controller(PaymentMethodController::class)->prefix('metodos-pagamento')->name('payment-methods.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{paymentMethod}/editar', 'edit')->name('edit');
        Route::put('/{paymentMethod}', 'update')->name('update');
        Route::delete('/{paymentMethod}', 'destroy')->name('destroy');
    });

    Route::controller(ChartAccountController::class)->prefix('plano-contas')->name('chart-accounts.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{chartAccount}/editar', 'edit')->name('edit');
        Route::put('/{chartAccount}', 'update')->name('update');
        Route::delete('/{chartAccount}', 'destroy')->name('destroy');
    });

    Route::controller(CashFlowController::class)->prefix('fluxo-caixa')->name('cash-flow.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/detalhes-diarios', 'getDailyDetails')->name('daily-details');
    });

    Route::controller(ProductController::class)->prefix('produtos')->name('products.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{product}/editar', 'edit')->name('edit');
        Route::put('/{product}', 'update')->name('update');
        Route::delete('/{product}', 'destroy')->name('destroy');
    });

    Route::controller(StockController::class)->prefix('estoque')->name('stocks.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{stock}/ajuste', 'adjust')->name('adjust');
        Route::post('/{stock}/ajuste', 'storeAdjustment')->name('store-adjustment');
    });

    Route::get('/kardex', [KardexController::class, 'index'])->name('kardex.index');

    Route::controller(BrandController::class)->prefix('marcas')->name('brands.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{brand}/editar', 'edit')->name('edit');
        Route::put('/{brand}', 'update')->name('update');
        Route::delete('/{brand}', 'destroy')->name('destroy');
    });

    Route::controller(SectionController::class)->prefix('secoes')->name('sections.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{section}/editar', 'edit')->name('edit');
        Route::put('/{section}', 'update')->name('update');
        Route::delete('/{section}', 'destroy')->name('destroy');
    });

    Route::controller(GroupController::class)->prefix('grupos')->name('groups.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{group}/editar', 'edit')->name('edit');
        Route::put('/{group}', 'update')->name('update');
        Route::delete('/{group}', 'destroy')->name('destroy');
    });

    Route::prefix('relatorios')->name('reports.')->group(function () {
        Route::prefix('pedidos')->name('orders.')->group(function () {
            Route::get('/', fn () => inertia('Reports/Orders/Index'))->name('index');
            Route::get('/analiticos', fn () => inertia('Reports/Orders/Analytical'))->name('analyticals');
            Route::get('/analiticos/imprimir', [OrderReportController::class, 'analytical'])->name('analyticals.print');
            Route::get('/sinteticos', fn () => inertia('Reports/Orders/Synthetic'))->name('synthetics');
            Route::get('/sinteticos/imprimir', [OrderReportController::class, 'synthetic'])->name('synthetics.print');
        });

        Route::prefix('recebiveis')->name('receivables.')->group(function () {
            Route::get('/', fn () => inertia('Reports/Receivables/Index'))->name('index');
            Route::get('/analiticos', fn () => inertia('Reports/Receivables/Analytical'))->name('analyticals');
            Route::get('/analiticos/imprimir', [ReceivableReportController::class, 'analytical'])->name('analyticals.print');
            Route::get('/sinteticos', fn () => inertia('Reports/Receivables/Synthetic'))->name('synthetics');
            Route::get('/sinteticos/imprimir', [ReceivableReportController::class, 'synthetic'])->name('synthetics.print');
        });

        Route::prefix('pagaveis')->name('payables.')->group(function () {
            Route::get('/', fn () => inertia('Reports/Payables/Index'))->name('index');
            Route::get('/analiticos', fn () => inertia('Reports/Payables/Analytical'))->name('analyticals');
            Route::get('/analiticos/imprimir', [PayableReportController::class, 'analytical'])->name('analyticals.print');
            Route::get('/sinteticos', fn () => inertia('Reports/Payables/Synthetic'))->name('synthetics');
            Route::get('/sinteticos/imprimir', [PayableReportController::class, 'synthetic'])->name('synthetics.print');
        });

        Route::prefix('curva-abc')->name('abc.')->group(function () {
            Route::get('/clientes', fn () => inertia('Reports/CustomerAbc/Index'))->name('customers');
            Route::get('/produtos', fn () => inertia('Reports/ProductAbc/Index'))->name('products');
        });

        Route::get('/curva-abc/clientes/gerar', [CustomerAbcReportController::class, 'generate'])->name('customer-abc.generate');
        Route::get('/curva-abc/produtos/gerar', [ProductAbcReportController::class, 'generate'])->name('product-abc.generate');
    });

    Route::controller(UserController::class)->prefix('usuarios')->name('users.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}/editar', 'edit')->name('edit');
        Route::put('/{user}', 'update')->name('update');
        Route::delete('/{user}', 'destroy')->name('destroy');
    });

    Route::controller(SellerController::class)->prefix('vendedores')->name('sellers.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{seller}/editar', 'edit')->name('edit');
        Route::put('/{seller}', 'update')->name('update');
        Route::delete('/{seller}', 'destroy')->name('destroy');
    });

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
        Route::get('/{configuration}/editar', 'edit')->name('edit');
        Route::put('/{configuration}', 'update')->name('update');
    });
});
