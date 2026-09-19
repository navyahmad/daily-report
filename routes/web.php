<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\PublicReportController;
use Illuminate\Support\Facades\Route;

// === Public (Guest) Daily Report (Tanpa Login) ===
Route::get('/', [PublicReportController::class, 'create'])->name('report.create');
Route::post('/', [PublicReportController::class, 'store'])->middleware('throttle:30,1')->name('report.store');
Route::get('/success', [PublicReportController::class, 'success'])->name('report.success');
Route::get('/api/employees', [PublicReportController::class, 'getEmployees'])->name('api.employees');

// === Admin / HRD Authentication Routes ===
Route::get('/admin/login', [AuthController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'store'])->middleware('throttle:10,1')->name('admin.login.store');
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// Redirect standard /dashboard to /admin/dashboard
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth:admin_hrd', 'admin.active'])->name('dashboard');

// === Protected Admin / HRD Area ===
Route::prefix('admin')->name('admin.')->middleware(['auth:admin_hrd', 'admin.active'])->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Employee Management
    Route::resource('employees', EmployeeController::class)->except(['show', 'destroy']);
    Route::patch('/employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])
        ->name('employees.toggle-status');

    // Daily Reports Management
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/{report}/edit', [ReportController::class, 'edit'])->name('reports.edit');
    Route::put('/reports/{report}', [ReportController::class, 'update'])->name('reports.update');
    Route::patch('/reports/{report}/cancel', [ReportController::class, 'cancel'])->name('reports.cancel');
    Route::patch('/reports/{report}/restore', [ReportController::class, 'restore'])->name('reports.restore');

    // Super Admin / Admin Account Management (Role 'admin' only)
    Route::middleware('role.admin')->group(function () {
        Route::resource('users', AdminUserController::class)->except(['show', 'destroy']);
        Route::patch('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])
            ->name('users.toggle-status');
        Route::put('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])
            ->name('users.reset-password');
    });
});
