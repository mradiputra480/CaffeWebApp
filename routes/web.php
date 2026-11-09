<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HR\AttendanceController;
use App\Http\Controllers\HR\ShiftScheduleController;
use App\Http\Controllers\Inventory\IngredientController;
use App\Http\Controllers\Inventory\RestockController;
use App\Http\Controllers\Manager\AnalyticsController;
use App\Http\Controllers\POS\PaymentController;
use App\Http\Controllers\POS\POSController;
use Illuminate\Support\Facades\Route;

// Public Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// 1-Click Instant Demo Login (for Portfolio Evaluators & Recruiters)
Route::get('/demo/{role}', [AuthController::class, 'demoLogin'])->name('demo.login');

// Authenticated Logout Route
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Root Route: Showcase Landing Page for guests, role portal for authenticated users
Route::get('/', function () {
    if (! auth()->check()) {
        return view('welcome');
    }

    return match (auth()->user()->role) {
        'STAFF' => redirect()->route('pos.index'),
        'HRD' => redirect()->route('hr.roster'),
        'MANAGER' => redirect()->route('manager.dashboard'),
        default => redirect()->route('login'),
    };
});

// Protected Authenticated Routes (All Roles can record own attendance)
Route::middleware('auth')->group(function () {
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    Route::get('/attendance/status', [AttendanceController::class, 'status'])->name('attendance.status');
});

// Protected Role Routes

// 1. Staff Terminal (Staff & Manager) - POS Terminal & Checkout
Route::middleware(['auth', 'role:STAFF,MANAGER'])->group(function () {
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::get('/pos/receipt/{id}', [POSController::class, 'receipt'])->name('pos.receipt');

    // POS API Endpoints
    Route::get('/api/pos/menu', [POSController::class, 'getAvailableMenu'])->name('api.pos.menu');
    Route::post('/api/pos/check-stock', [POSController::class, 'checkStock'])->name('api.pos.check-stock');
    Route::post('/api/pos/orders', [POSController::class, 'processOrder'])->name('api.pos.orders');

    // QRIS Payment Endpoints
    Route::post('/api/qris/generate', [PaymentController::class, 'generateQR'])->name('api.qris.generate');
    Route::get('/api/qris/status/{transactionId}', [PaymentController::class, 'checkStatus'])->name('api.qris.status');
    Route::post('/api/qris/simulate-success', [PaymentController::class, 'simulateSuccess'])->name('api.qris.simulate');
});

// 2. HR Portal (HRD & Manager)
Route::middleware(['auth', 'role:HRD,MANAGER'])->group(function () {
    Route::get('/hr/roster', [AttendanceController::class, 'roster'])->name('hr.roster');
    Route::post('/hr/alerts/{id}/acknowledge', [AttendanceController::class, 'acknowledgeAlert'])->name('hr.alerts.acknowledge');

    // Shift Scheduling CRUD
    Route::post('/hr/shifts', [ShiftScheduleController::class, 'store'])->name('hr.shifts.store');
    Route::put('/hr/shifts/{id}', [ShiftScheduleController::class, 'update'])->name('hr.shifts.update');
    Route::delete('/hr/shifts/{id}', [ShiftScheduleController::class, 'destroy'])->name('hr.shifts.destroy');
});

// 3. Manager Executive Portal (Manager Only)
Route::middleware(['auth', 'role:MANAGER'])->group(function () {
    Route::get('/manager/dashboard', [AnalyticsController::class, 'index'])->name('manager.dashboard');
    Route::get('/manager/export-sales', [AnalyticsController::class, 'exportAudit'])->name('manager.export-sales');
});

// 4. Inventory & Raw Materials Supply Portal (Staff & Manager)
Route::middleware(['auth', 'role:STAFF,MANAGER'])->group(function () {
    Route::get('/inventory', [IngredientController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/intake', [IngredientController::class, 'recordIntake'])->name('inventory.intake');
    Route::post('/inventory/restock', [RestockController::class, 'store'])->name('inventory.restock.store');
});

// 5. Restock Approval & Stock Adjustments (Manager Only)
Route::middleware(['auth', 'role:MANAGER'])->group(function () {
    Route::post('/inventory/restock/{id}/approve', [RestockController::class, 'approve'])->name('inventory.restock.approve');
    Route::post('/inventory/restock/{id}/reject', [RestockController::class, 'reject'])->name('inventory.restock.reject');
    Route::post('/inventory/restock/approve-all', [RestockController::class, 'approveAll'])->name('inventory.restock.approve-all');
    Route::put('/inventory/ingredients/{id}', [IngredientController::class, 'updateThreshold'])->name('inventory.ingredients.update');
});
