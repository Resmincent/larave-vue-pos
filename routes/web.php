<?php

use App\Http\Controllers\AssignPermissionController;
use App\Http\Controllers\CashSessionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', fn() => Inertia::render('Dashboard'))
        ->name('dashboard');

    Route::resource('categories', CategoryController::class);
    Route::resource('taxes', TaxController::class);
    Route::resource('products', ProductController::class);

    Route::resource('suppliers', SupplierController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('payment-methods', PaymentMethodController::class);

    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('purchases', PurchaseController::class);
    Route::resource('users', UserController::class);

    // Inventories
    Route::get('inventories', [InventoryController::class, 'index'])->name('inventories.index');
    Route::get('inventories/{product}/adjust', [InventoryController::class, 'adjustForm'])->name('inventories.adjust.form');
    Route::post('inventories/{product}/adjust', [InventoryController::class, 'adjust'])->name('inventories.adjust');

    // Assign Permission
    Route::get('assign-permissions', [AssignPermissionController::class, 'index'])->name('assign.permissions.index');
    Route::get('assign-permissions/{role}/edit', [AssignPermissionController::class, 'editRolePermissions'])->name('assign.permissions.edit');
    Route::post('assign-permissions/update', [AssignPermissionController::class, 'updateRolePermissions'])->name('assign.permissions.update');

    // Payments
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');

    // Cash Sessions
    Route::resource('cash-sessions', CashSessionController::class)->only(['index']);
    Route::get('cash-sessions/open', [CashSessionController::class, 'openFrom'])->name('cash.sessions.open.form');
    Route::post('cash-sessions/open', [CashSessionController::class, 'open'])->name('cash.sessions.open');
    Route::get('cash-sessions/{cashSession}/close', [CashSessionController::class, 'closeForm'])->name('cash.sessions.close.form');
    Route::post('cash-sessions/{cashSession}/close', [CashSessionController::class, 'close'])->name('cash.sessions.close');

    // Sales
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('sales/{sale}/void', [SaleController::class, 'void'])->name('sales.void');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
