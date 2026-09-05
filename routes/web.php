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

use App\Http\Controllers\Encoder\DashboardController as EncoderDashboardController;
use App\Http\Controllers\Encoder\IncomingController as EncoderIncomingController;
use App\Http\Controllers\Encoder\OutgoingController as EncoderOutgoingController;
use App\Http\Controllers\Encoder\TraceabilityController as EncoderTraceabilityController;
use App\Http\Controllers\Encoder\InventoryController as EncoderInventoryController;

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
        return $user->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('encoder.dashboard');
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

    // Incoming Repair Slips & Batches
    Route::get('/incoming', [AdminIncomingController::class, 'index'])->name('incoming.index');
    Route::get('/incoming/create', [AdminIncomingController::class, 'create'])->name('incoming.create');
    Route::post('/incoming', [AdminIncomingController::class, 'store'])->name('incoming.store');
    Route::get('/incoming/{id}', [AdminIncomingController::class, 'show'])->name('incoming.show');
    Route::get('/incoming/{id}/edit', [AdminIncomingController::class, 'edit'])->name('incoming.edit');
    Route::put('/incoming/{id}', [AdminIncomingController::class, 'update'])->name('incoming.update');
    Route::delete('/incoming/{id}', [AdminIncomingController::class, 'destroy'])->name('incoming.destroy');
    Route::get('/incoming/{id}/print', [AdminIncomingController::class, 'print'])->name('incoming.print');
    Route::get('/incoming/{id}/items', [AdminIncomingController::class, 'getItems'])->name('incoming.items');
    Route::post('/incoming/{id}/save-item', [AdminIncomingController::class, 'saveItem'])->name('incoming.saveItem');
    Route::delete('/incoming/{id}/item/{itemId}', [AdminIncomingController::class, 'deleteItem'])->name('incoming.deleteItem');
    Route::post('/incoming/{id}/save-header', [AdminIncomingController::class, 'saveHeader'])->name('incoming.saveHeader');

    // Outgoing Repair Slips
    Route::get('/outgoing', [AdminOutgoingController::class, 'index'])->name('outgoing.index');
    Route::get('/outgoing/create', [AdminOutgoingController::class, 'create'])->name('outgoing.create');
    Route::post('/outgoing', [AdminOutgoingController::class, 'store'])->name('outgoing.store');
    Route::get('/outgoing/{id}', [AdminOutgoingController::class, 'show'])->name('outgoing.show');
    Route::get('/outgoing/{id}/edit', [AdminOutgoingController::class, 'edit'])->name('outgoing.edit');
    Route::put('/outgoing/{id}', [AdminOutgoingController::class, 'update'])->name('outgoing.update');
    Route::delete('/outgoing/{id}', [AdminOutgoingController::class, 'destroy'])->name('outgoing.destroy');
    Route::get('/outgoing/{id}/print', [AdminOutgoingController::class, 'print'])->name('outgoing.print');

    // Master Inventory
    Route::get('/inventory', [AdminInventoryController::class, 'index'])->name('inventory.index');
    Route::put('/inventory/{id}', [AdminInventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{id}', [AdminInventoryController::class, 'destroy'])->name('inventory.destroy');

    // Repair Traceability Matrix
    Route::get('/traceability', [AdminTraceabilityController::class, 'index'])->name('traceability.index');
    Route::put('/traceability/{id}', [AdminTraceabilityController::class, 'update'])->name('traceability.update');
    Route::get('/traceability/print', [AdminTraceabilityController::class, 'print'])->name('traceability.print');

    // User Accounts Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');
});

// ==========================================
// ENCODER ROUTES (Encoding, Outgoing, Traceability)
// ==========================================
Route::middleware(['encoder'])->prefix('encoder')->name('encoder.')->group(function () {
    Route::get('/dashboard', [EncoderDashboardController::class, 'index'])->name('dashboard');

    // Incoming Slips
    Route::get('/incoming', [EncoderIncomingController::class, 'index'])->name('incoming.index');
    Route::get('/incoming/create', [EncoderIncomingController::class, 'create'])->name('incoming.create');
    Route::post('/incoming', [EncoderIncomingController::class, 'store'])->name('incoming.store');
    Route::get('/incoming/{id}', [EncoderIncomingController::class, 'show'])->name('incoming.show');
    Route::get('/incoming/{id}/edit', [EncoderIncomingController::class, 'edit'])->name('incoming.edit');
    Route::put('/incoming/{id}', [EncoderIncomingController::class, 'update'])->name('incoming.update');
    Route::get('/incoming/{id}/print', [EncoderIncomingController::class, 'print'])->name('incoming.print');
    Route::get('/incoming/{id}/items', [EncoderIncomingController::class, 'getItems'])->name('incoming.items');
    Route::post('/incoming/{id}/save-item', [EncoderIncomingController::class, 'saveItem'])->name('incoming.saveItem');
    Route::delete('/incoming/{id}/item/{itemId}', [EncoderIncomingController::class, 'deleteItem'])->name('incoming.deleteItem');
    Route::post('/incoming/{id}/save-header', [EncoderIncomingController::class, 'saveHeader'])->name('incoming.saveHeader');

    // Outgoing Slips
    Route::get('/outgoing', [EncoderOutgoingController::class, 'index'])->name('outgoing.index');
    Route::get('/outgoing/create', [EncoderOutgoingController::class, 'create'])->name('outgoing.create');
    Route::post('/outgoing', [EncoderOutgoingController::class, 'store'])->name('outgoing.store');
    Route::get('/outgoing/{id}', [EncoderOutgoingController::class, 'show'])->name('outgoing.show');
    Route::get('/outgoing/{id}/edit', [EncoderOutgoingController::class, 'edit'])->name('outgoing.edit');
    Route::put('/outgoing/{id}', [EncoderOutgoingController::class, 'update'])->name('outgoing.update');
    Route::get('/outgoing/{id}/print', [EncoderOutgoingController::class, 'print'])->name('outgoing.print');

    // Traceability Matrix & Diagnostics
    Route::get('/traceability', [EncoderTraceabilityController::class, 'index'])->name('traceability.index');
    Route::put('/traceability/{id}', [EncoderTraceabilityController::class, 'update'])->name('traceability.update');
    Route::get('/traceability/print', [EncoderTraceabilityController::class, 'print'])->name('traceability.print');

    // Inventory View
    Route::get('/inventory', [EncoderInventoryController::class, 'index'])->name('inventory.index');
});
