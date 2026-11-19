<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LandController;
use App\Http\Controllers\PermissionGroupController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RiceVarietyController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SeedClassController;
use App\Http\Controllers\SeedProductionController;
use App\Http\Controllers\SeedProductionStepController;
use App\Http\Controllers\SeedGrowingHarvestController;
use App\Http\Controllers\SeedGrowingInspectionController;
use App\Http\Controllers\SeedGrowingController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:super-admin|admin'])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('permission-groups', PermissionGroupController::class)->only(['index', 'store', 'update', 'destroy']);
});

Route::middleware(['auth'])
    ->prefix('master')
    ->name('master.')
    ->group(function () {
        Route::resource('rice-varieties', RiceVarietyController::class)->middleware('module.access:master.rice-varieties.manage');
        Route::resource('seed-classes', SeedClassController::class)->middleware('module.access:master.seed-classes.manage');
        Route::resource('farmers', FarmerController::class)->middleware('module.access:master.farmers.manage');
        Route::resource('lands', LandController::class)->middleware('module.access:master.lands.manage');
        Route::resource('suppliers', SupplierController::class)->middleware('module.access:master.suppliers.manage');
        Route::resource('customers', CustomerController::class)->middleware('module.access:master.customers.manage');
        Route::resource('warehouses', WarehouseController::class)->middleware('module.access:master.warehouses.manage');
        Route::resource('items', ItemController::class)->middleware('module.access:master.items.manage');
    });

Route::middleware(['auth'])->group(function () {
    Route::resource('stocks', StockController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->middleware('module.access:stocks.manage');

    Route::resource('purchases', PurchaseController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->middleware('module.access:purchases.manage');

    Route::middleware('module.access:sales.manage')->group(function () {
        Route::patch('sales/{sale}/payment-status', [SaleController::class, 'updatePaymentStatus'])
            ->name('sales.payment-status.update');
        Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
    });

    Route::resource('stock-transfers', StockTransferController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->middleware('module.access:stock-transfers.manage');

    Route::middleware('module.access:seed-growings.manage')->group(function () {
        Route::patch('seed-growings/{seed_growing}/status', [SeedGrowingController::class, 'updateStatus'])
            ->name('seed-growings.status.update');
        Route::resource('seed-growings', SeedGrowingController::class)->only(['index', 'create', 'store', 'show']);
        Route::patch('seed-growings/{seed_growing}/inspections/{inspection}', [SeedGrowingInspectionController::class, 'update'])
            ->name('seed-growings.inspections.update');
        Route::patch('seed-growings/{seed_growing}/harvest', [SeedGrowingHarvestController::class, 'update'])
            ->name('seed-growings.harvest.update');
    });

    Route::middleware('module.access:seed-productions.manage')->group(function () {
        Route::post('seed-productions/{seed_production}/steps', [SeedProductionStepController::class, 'store'])
            ->name('seed-productions.steps.store');
        Route::patch('seed-productions/{seed_production}/steps/{step}', [SeedProductionStepController::class, 'update'])
            ->name('seed-productions.steps.update');
        Route::post('seed-productions/{seed_production}/inputs', [SeedProductionController::class, 'storeInput'])
            ->name('seed-productions.inputs.store');
        Route::patch('seed-productions/{seed_production}/certification', [SeedProductionController::class, 'updateCertification'])
            ->name('seed-productions.certification.update');
        Route::patch('seed-productions/{seed_production}/output', [SeedProductionController::class, 'updateOutput'])
            ->name('seed-productions.output.update');
        Route::resource('seed-productions', SeedProductionController::class)->only(['index', 'create', 'store', 'show']);
    });
});

require __DIR__.'/auth.php';
