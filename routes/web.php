<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LandController;
use App\Http\Controllers\RiceVarietyController;
use App\Http\Controllers\SeedClassController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
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

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
});

Route::middleware(['auth', 'role:admin|editor'])
    ->prefix('master')
    ->name('master.')
    ->group(function () {
        Route::resource('rice-varieties', RiceVarietyController::class);
        Route::resource('seed-classes', SeedClassController::class);
        Route::resource('farmers', FarmerController::class);
        Route::resource('lands', LandController::class);
        Route::resource('suppliers', SupplierController::class);
        Route::resource('customers', CustomerController::class);
        Route::resource('warehouses', WarehouseController::class);
        Route::resource('items', ItemController::class);
    });

Route::middleware(['auth', 'role:admin|editor'])->group(function () {
    Route::resource('stocks', StockController::class)->only(['index', 'create', 'store', 'show']);
});

require __DIR__.'/auth.php';
