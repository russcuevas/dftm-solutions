<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\IncomingController as AdminIncomingController;
use App\Http\Controllers\Admin\OutgoingController as AdminOutgoingController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\TraceabilityController as AdminTraceabilityController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\ConsumableInventoryController as AdminConsumableInventoryController;
use App\Http\Controllers\Admin\ConsumableCategoryController as AdminConsumableCategoryController;
use App\Http\Controllers\Admin\ConsumableItemController as AdminConsumableItemController;

use App\Http\Controllers\Encoder\DashboardController as EncoderDashboardController;
use App\Http\Controllers\Encoder\IncomingController as EncoderIncomingController;
use App\Http\Controllers\Encoder\OutgoingController as EncoderOutgoingController;
use App\Http\Controllers\Encoder\TraceabilityController as EncoderTraceabilityController;
use App\Http\Controllers\Encoder\InventoryController as EncoderInventoryController;
use App\Http\Controllers\Encoder\ClientController as EncoderClientController;

use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\IncomingController as ClientIncomingController;
use App\Http\Controllers\Client\TraceabilityController as ClientTraceabilityController;
use App\Http\Controllers\Client\OutgoingController as ClientOutgoingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root redirect
Route::get('/', function () {
    if (Auth::check()) {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->isClient()) {
            return redirect()->route('client.dashboard');
        }
        return redirect()->route('encoder.dashboard');
    }
    return redirect()->route('login');
});

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
});

// ==========================================
// ADMIN ROUTES (Full Access & Management)
// ==========================================
Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Incoming Transmittals
    Route::get('/incoming', [AdminIncomingController::class, 'index'])->name('incoming.index');
    Route::get('/incoming/create', [AdminIncomingController::class, 'create'])->name('incoming.create');
    Route::post('/incoming', [AdminIncomingController::class, 'store'])->name('incoming.store');
    Route::post('/incoming/check-duplicate', [AdminIncomingController::class, 'checkDuplicate'])->name('incoming.checkDuplicate');
    Route::get('/incoming/search-serial', [AdminIncomingController::class, 'searchSerial'])->name('incoming.searchSerial');
    Route::get('/incoming/{id}', [AdminIncomingController::class, 'show'])->name('incoming.show');
    Route::get('/incoming/{id}/edit', [AdminIncomingController::class, 'edit'])->name('incoming.edit');
    Route::put('/incoming/{id}', [AdminIncomingController::class, 'update'])->name('incoming.update');
    Route::delete('/incoming/{id}', [AdminIncomingController::class, 'destroy'])->name('incoming.destroy');
    Route::get('/incoming/{id}/print', [AdminIncomingController::class, 'print'])->name('incoming.print');
    Route::get('/incoming/{id}/items', [AdminIncomingController::class, 'getItems'])->name('incoming.items');
    Route::post('/incoming/{id}/save-item', [AdminIncomingController::class, 'saveItem'])->name('incoming.saveItem');
    Route::delete('/incoming/{id}/item/{itemId}', [AdminIncomingController::class, 'deleteItem'])->name('incoming.deleteItem');
    Route::post('/incoming/{id}/save-header', [AdminIncomingController::class, 'saveHeader'])->name('incoming.saveHeader');

    // Traceability Matrix & Batches
    Route::get('/traceability/lookup', [AdminTraceabilityController::class, 'lookup'])->name('traceability.lookup');
    Route::get('/traceability/create-batch', [AdminTraceabilityController::class, 'createBatch'])->name('traceability.createBatch');
    Route::get('/traceability/create-batch-alt', [AdminTraceabilityController::class, 'createBatch'])->name('traceability.create_batch');
    Route::post('/traceability/batch', [AdminTraceabilityController::class, 'storeBatch'])->name('traceability.storeBatch');
    Route::delete('/traceability/batch/{id}', [AdminTraceabilityController::class, 'destroyBatch'])->name('traceability.destroyBatch');
    Route::post('/traceability/bulk-update', [AdminTraceabilityController::class, 'bulkUpdate'])->name('traceability.bulkUpdate');
    Route::post('/traceability/save-item', [AdminTraceabilityController::class, 'saveItem'])->name('traceability.saveItem');
    Route::get('/traceability', [AdminTraceabilityController::class, 'index'])->name('traceability.index');
    Route::put('/traceability/{id}', [AdminTraceabilityController::class, 'update'])->name('traceability.update');
    Route::get('/traceability/print', [AdminTraceabilityController::class, 'print'])->name('traceability.print');

    // Outgoing Release Slips & Reports (Sourced from Traceability & Direct Slips)
    Route::get('/outgoing', [AdminOutgoingController::class, 'index'])->name('outgoing.index');
    Route::get('/outgoing/create', [AdminOutgoingController::class, 'create'])->name('outgoing.create');
    Route::post('/outgoing', [AdminOutgoingController::class, 'store'])->name('outgoing.store');
    Route::get('/outgoing/batch-data/{id}', [AdminOutgoingController::class, 'getBatchData'])->name('outgoing.batchData');
    Route::post('/outgoing/release', [AdminOutgoingController::class, 'release'])->name('outgoing.release');
    Route::get('/outgoing/print/{id?}', [AdminOutgoingController::class, 'print'])->name('outgoing.print');
    Route::get('/outgoing/{id}/print', [AdminOutgoingController::class, 'print']);
    Route::get('/outgoing/{id}', [AdminOutgoingController::class, 'show'])->name('outgoing.show');
    Route::get('/outgoing/{id}/edit', [AdminOutgoingController::class, 'edit'])->name('outgoing.edit');
    Route::put('/outgoing/{id}', [AdminOutgoingController::class, 'update'])->name('outgoing.update');
    Route::delete('/outgoing/{id}', [AdminOutgoingController::class, 'destroy'])->name('outgoing.destroy');

    // Master Inventory
    Route::get('/inventory', [AdminInventoryController::class, 'index'])->name('inventory.index');
    Route::put('/inventory/{id}', [AdminInventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{id}', [AdminInventoryController::class, 'destroy'])->name('inventory.destroy');

    // User Accounts Management (Internal Staff)
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Client Accounts Management (Customer Portals)
    Route::get('/clients', [AdminClientController::class, 'index'])->name('clients.index');
    Route::post('/clients', [AdminClientController::class, 'store'])->name('clients.store');
    Route::put('/clients/{id}', [AdminClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{id}', [AdminClientController::class, 'destroy'])->name('clients.destroy');

    // Consumable Portal Routes
    Route::prefix('consumables')->name('consumables.')->group(function () {
        // Monthly Ledger & Matrix
        Route::get('/', [AdminConsumableInventoryController::class, 'index'])->name('index');
        Route::post('/daily-log', [AdminConsumableInventoryController::class, 'storeDailyLog'])->name('dailyLog.store');
        Route::post('/beginning-stock', [AdminConsumableInventoryController::class, 'updateBeginningStock'])->name('beginning.update');
        Route::post('/carry-over', [AdminConsumableInventoryController::class, 'carryOverPreviousMonth'])->name('carryover');
        Route::get('/logs', [AdminConsumableInventoryController::class, 'logs'])->name('logs.index');
        Route::delete('/logs/{id}', [AdminConsumableInventoryController::class, 'destroyDailyLog'])->name('logs.destroy');
        Route::get('/print', [AdminConsumableInventoryController::class, 'print'])->name('print');

        // Categories CRUD
        Route::get('/categories', [AdminConsumableCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [AdminConsumableCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{id}', [AdminConsumableCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [AdminConsumableCategoryController::class, 'destroy'])->name('categories.destroy');

        // Items CRUD
        Route::get('/items', [AdminConsumableItemController::class, 'index'])->name('items.index');
        Route::post('/items', [AdminConsumableItemController::class, 'store'])->name('items.store');
        Route::put('/items/{id}', [AdminConsumableItemController::class, 'update'])->name('items.update');
        Route::delete('/items/{id}', [AdminConsumableItemController::class, 'destroy'])->name('items.destroy');
    });
});

// ==========================================
// ENCODER ROUTES (Encoding, Traceability, Outgoing)
// ==========================================
Route::middleware(['encoder'])->prefix('encoder')->name('encoder.')->group(function () {
    Route::get('/dashboard', [EncoderDashboardController::class, 'index'])->name('dashboard');

    // Incoming Transmittals
    Route::get('/incoming', [EncoderIncomingController::class, 'index'])->name('incoming.index');
    Route::get('/incoming/create', [EncoderIncomingController::class, 'create'])->name('incoming.create');
    Route::post('/incoming', [EncoderIncomingController::class, 'store'])->name('incoming.store');
    Route::post('/incoming/check-duplicate', [EncoderIncomingController::class, 'checkDuplicate'])->name('incoming.checkDuplicate');
    Route::get('/incoming/search-serial', [EncoderIncomingController::class, 'searchSerial'])->name('incoming.searchSerial');
    Route::get('/incoming/{id}', [EncoderIncomingController::class, 'show'])->name('incoming.show');
    Route::get('/incoming/{id}/edit', [EncoderIncomingController::class, 'edit'])->name('incoming.edit');
    Route::put('/incoming/{id}', [EncoderIncomingController::class, 'update'])->name('incoming.update');
    Route::get('/incoming/{id}/print', [EncoderIncomingController::class, 'print'])->name('incoming.print');
    Route::get('/incoming/{id}/items', [EncoderIncomingController::class, 'getItems'])->name('incoming.items');
    Route::post('/incoming/{id}/save-item', [EncoderIncomingController::class, 'saveItem'])->name('incoming.saveItem');
    Route::delete('/incoming/{id}/item/{itemId}', [EncoderIncomingController::class, 'deleteItem'])->name('incoming.deleteItem');
    Route::post('/incoming/{id}/save-header', [EncoderIncomingController::class, 'saveHeader'])->name('incoming.saveHeader');

    // Traceability Matrix & Batches
    Route::get('/traceability/lookup', [EncoderTraceabilityController::class, 'lookup'])->name('traceability.lookup');
    Route::get('/traceability/create-batch', [EncoderTraceabilityController::class, 'createBatch'])->name('traceability.createBatch');
    Route::get('/traceability/create-batch-alt', [EncoderTraceabilityController::class, 'createBatch'])->name('traceability.create_batch');
    Route::post('/traceability/batch', [EncoderTraceabilityController::class, 'storeBatch'])->name('traceability.storeBatch');
    Route::delete('/traceability/batch/{id}', [EncoderTraceabilityController::class, 'destroyBatch'])->name('traceability.destroyBatch');
    Route::post('/traceability/bulk-update', [EncoderTraceabilityController::class, 'bulkUpdate'])->name('traceability.bulkUpdate');
    Route::post('/traceability/save-item', [EncoderTraceabilityController::class, 'saveItem'])->name('traceability.saveItem');
    Route::get('/traceability', [EncoderTraceabilityController::class, 'index'])->name('traceability.index');
    Route::put('/traceability/{id}', [EncoderTraceabilityController::class, 'update'])->name('traceability.update');
    Route::get('/traceability/print', [EncoderTraceabilityController::class, 'print'])->name('traceability.print');

    // Outgoing Slips & Reports
    Route::get('/outgoing', [EncoderOutgoingController::class, 'index'])->name('outgoing.index');
    Route::get('/outgoing/create', [EncoderOutgoingController::class, 'create'])->name('outgoing.create');
    Route::post('/outgoing', [EncoderOutgoingController::class, 'store'])->name('outgoing.store');
    Route::get('/outgoing/batch-data/{id}', [EncoderOutgoingController::class, 'getBatchData'])->name('outgoing.batchData');
    Route::post('/outgoing/release', [EncoderOutgoingController::class, 'release'])->name('outgoing.release');
    Route::get('/outgoing/print/{id?}', [EncoderOutgoingController::class, 'print'])->name('outgoing.print');
    Route::get('/outgoing/{id}/print', [EncoderOutgoingController::class, 'print']);
    Route::get('/outgoing/{id}', [EncoderOutgoingController::class, 'show'])->name('outgoing.show');
    Route::get('/outgoing/{id}/edit', [EncoderOutgoingController::class, 'edit'])->name('outgoing.edit');
    Route::put('/outgoing/{id}', [EncoderOutgoingController::class, 'update'])->name('outgoing.update');

    // Inventory View
    Route::get('/inventory', [EncoderInventoryController::class, 'index'])->name('inventory.index');

    // Clients Directory (Create & View)
    Route::get('/clients', [EncoderClientController::class, 'index'])->name('clients.index');
    Route::post('/clients', [EncoderClientController::class, 'store'])->name('clients.store');
});

// ==========================================
// CLIENT PORTAL (Read-Only Tracking & Reports)
// ==========================================
Route::middleware(['client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');

    // Incoming Transmittals (Read-Only)
    Route::get('/incoming', [ClientIncomingController::class, 'index'])->name('incoming.index');
    Route::get('/incoming/{id}', [ClientIncomingController::class, 'show'])->name('incoming.show');
    Route::get('/incoming/{id}/print', [ClientIncomingController::class, 'print'])->name('incoming.print');

    // Traceability Batches (Read-Only Matrix & Unit Search)
    Route::get('/traceability', [ClientTraceabilityController::class, 'index'])->name('traceability.index');
    Route::get('/traceability/print', [ClientTraceabilityController::class, 'print'])->name('traceability.print');

    // Outgoing Release Reports (Read-Only)
    Route::get('/outgoing', [ClientOutgoingController::class, 'index'])->name('outgoing.index');
    Route::get('/outgoing/print', [ClientOutgoingController::class, 'print'])->name('outgoing.print');
});
