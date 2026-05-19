<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;

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
    return redirect()->route('login'); // Mengalihkan ke route login
});


// Dashboard umum berdasarkan role
Route::middleware('auth')->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Profil (semua user yang login)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route hanya untuk Admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
});

// Route untuk Admin dan Editor
Route::middleware(['auth', 'role:admin|editor'])->group(function () {
   //Route untuk Admin dan Editor

});

// ==========================================
// ROUTES SISTEM PRODUKSI BENIH PADI
// ==========================================

// Master Data Routes
Route::middleware(['auth', 'role:admin|editor'])->prefix('master')->name('master.')->group(function () {
    // Varietas
    Route::resource('varieties', \App\Http\Controllers\VarietyController::class);
    // Satuan
    Route::resource('units', \App\Http\Controllers\UnitController::class);
    // Gudang
    Route::resource('warehouses', \App\Http\Controllers\WarehouseController::class);
    // Petani
    Route::resource('farmers', \App\Http\Controllers\FarmerController::class);
});

// Barang Masuk Basah Routes
Route::middleware(['auth', 'role:admin|editor'])->prefix('wet-rice')->name('wet-rice.')->group(function () {
    Route::resource('receipts', \App\Http\Controllers\WetRiceReceiptController::class);
});

// Proses Pengeringan Routes
Route::middleware(['auth', 'role:admin|editor'])->prefix('drying')->name('drying.')->group(function () {
    Route::resource('processes', \App\Http\Controllers\DryingProcessController::class);
});

// Stok Kering Routes
Route::middleware(['auth', 'role:admin|editor'])->prefix('dry-rice')->name('dry-rice.')->group(function () {
    Route::resource('stocks', \App\Http\Controllers\DryRiceStockController::class);
});

// Proses Packing Routes
Route::middleware(['auth', 'role:admin|editor'])->prefix('packaging')->name('packaging.')->group(function () {
    Route::resource('processes', \App\Http\Controllers\PackagingProcessController::class);
});

// Stok Terpacking Routes
Route::middleware(['auth', 'role:admin|editor'])->prefix('packed-stocks')->name('packed-stocks.')->group(function () {
    Route::resource('index', \App\Http\Controllers\PackedStockController::class);
});

// Laporan & Stock Movement
Route::middleware(['auth', 'role:admin|editor'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('stock-movements', [\App\Http\Controllers\StockMovementController::class, 'index'])->name('stock-movements.index');
});

require __DIR__.'/auth.php';
