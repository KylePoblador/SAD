<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/force-logout', function() {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/');
});

// Staff Dashboard
Route::get('/dashboard', function () {
    if (Auth::check() && Auth::user()->role === 'student') {
        return redirect()->route('student.dashboard');
    }
    return app(DashboardController::class)->index();
})->middleware(['auth', 'verified'])->name('dashboard');

// Student Dashboard
Route::get('/student/dashboard', [StudentController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('student.dashboard');

// Student Profile
Route::get('/student/profile', [StudentController::class, 'profile'])
    ->middleware(['auth', 'verified'])
    ->name('student.profile');

// Student Profile Update
Route::patch('/student/profile', [StudentController::class, 'updateProfile'])
    ->middleware(['auth', 'verified'])
    ->name('student.profile.update');

// ✅ Student Notifications (ADDED)
Route::get('/student/notifications', [StudentController::class, 'notifications'])
    ->middleware(['auth', 'verified'])
    ->name('student.notifications');

// Staff Profile
Route::get('/staff/profile', [DashboardController::class, 'profile'])
    ->middleware(['auth', 'verified'])
    ->name('staff.profile');

// Staff Profile Update
Route::patch('/staff/profile', [DashboardController::class, 'updateProfile'])
    ->middleware(['auth', 'verified'])
    ->name('staff.profile.update');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Staff routes
    Route::get('/staff/orders',    [DashboardController::class, 'orders'])->name('staff.orders');
    Route::get('/staff/menu',      [DashboardController::class, 'menu'])->name('staff.menu');
    Route::get('/staff/wallet',    [DashboardController::class, 'wallet'])->name('staff.wallet');
    Route::get('/staff/seats',     [DashboardController::class, 'seats'])->name('staff.seats');
    Route::get('/staff/feedbacks', [DashboardController::class, 'feedbacks'])->name('staff.feedbacks');
    Route::get('/staff/reports',   [DashboardController::class, 'reports'])->name('staff.reports');
});

require __DIR__.'/auth.php';