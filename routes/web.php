<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\Admin\ServiceRequestAdminController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    /** @var \App\Models\User $user */
    $user = Auth::user();

    if ($user) {
        return redirect($user->getDashboardUrl());
    }

    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profile Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'admin'])
            ->name('admin.dashboard');

        Route::resource('users', UserController::class);

        Route::get('/requests', [ServiceRequestAdminController::class, 'allRequests'])
            ->name('admin.requests');

        Route::post('/requests/{id}/force-assign', [ServiceRequestAdminController::class, 'forceAssign'])
            ->name('admin.force.assign');

        Route::post('/requests/{id}/force-complete', [ServiceRequestAdminController::class, 'forceComplete'])
            ->name('admin.force.complete');
    });


    /*
    |--------------------------------------------------------------------------
    | Manager Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('manager')->middleware('role:manager')->group(function () {

        Route::get('/dashboard', [ManagerController::class, 'dashboard'])
            ->name('manager.dashboard');

        Route::post('/assign/{id}', [ServiceRequestController::class, 'assign'])
            ->name('manager.assign');
    });


    /*
    |--------------------------------------------------------------------------
    | Technician Routes
    |--------------------------------------------------------------------------
    */


    Route::prefix('technician')->middleware('auth')->group(function () {

        Route::get('/dashboard', [TechnicianController::class, 'index'])
            ->name('technician.dashboard');

        Route::get('/requests', [TechnicianController::class, 'index'])
            ->name('technician.requests');

        Route::post('/requests/{id}/start', [TechnicianController::class, 'startWork'])
            ->name('technician.start');

        Route::post('/requests/{id}/complete', [TechnicianController::class, 'completeWork'])
            ->name('technician.complete');
    });


    /*
    |--------------------------------------------------------------------------
    | Customer Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('customer')->middleware('role:customer')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'customer'])
            ->name('customer.dashboard');

        // CREATE REQUEST
        Route::get('/requests/create', [ServiceRequestController::class, 'create'])
            ->name('customer.requests.create');

        // STORE REQUEST
        Route::post('/requests', [ServiceRequestController::class, 'store'])
            ->name('customer.requests.store');
    });


    /*
    |--------------------------------------------------------------------------
    | Workflow Routes (GLOBAL - DO NOT MOVE)
    |--------------------------------------------------------------------------
    */

    // Start Work (Technician)
    Route::post('/requests/{id}/start', [ServiceRequestController::class, 'start'])
        ->name('requests.start');

    // Complete Work (Technician)
    Route::post('/requests/{id}/complete', [ServiceRequestController::class, 'complete'])
        ->name('requests.complete');

    // Reopen Request (Manager)
    Route::post('/requests/{id}/reopen', [ServiceRequestController::class, 'reopen'])
        ->name('requests.reopen');

    // View Request (All roles)
    Route::get('/requests/{id}', [ServiceRequestController::class, 'show'])
        ->name('requests.show');
});



/*
|--------------------------------------------------------------------------
| Force Logout (Debug)
|--------------------------------------------------------------------------
*/

Route::get('/force-logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect('/login');
});

require __DIR__ . '/auth.php';
