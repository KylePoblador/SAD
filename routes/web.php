<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Models\ActivityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/force-logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/');
});


/*
|--------------------------------------------------------------------------
| REDIRECT DASHBOARD (ROLE SWITCHER)
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {

    if (!Auth::check()) {
        return redirect('/');
    }

    return Auth::user()->role === 'student'
        ? redirect()->route('student.dashboard')
        : redirect()->route('staff.dashboard');
})
->middleware(['auth','verified'])
->name('dashboard');


/*
|--------------------------------------------------------------------------
| STAFF DASHBOARD (NEW CLEAN ROUTE)
|--------------------------------------------------------------------------
*/

Route::get('/staff/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth','verified'])
    ->name('staff.dashboard');


/*
|--------------------------------------------------------------------------
| STUDENT DASHBOARD
|--------------------------------------------------------------------------
*/

Route::get('/student/dashboard', [StudentController::class, 'index'])
    ->middleware(['auth','verified'])
    ->name('student.dashboard');


/*
|--------------------------------------------------------------------------
| STUDENT PROFILE
|--------------------------------------------------------------------------
*/

Route::get('/student/profile', [StudentController::class, 'profile'])
    ->middleware(['auth','verified'])
    ->name('student.profile');

Route::get('/student/orders', [StudentController::class, 'orders'])
    ->middleware(['auth','verified'])
    ->name('student.orders');

Route::get('/student/wallet', [StudentController::class, 'wallet'])
    ->middleware(['auth','verified'])
    ->name('student.wallet');

Route::post('/student/wallet/update/{studentId}', [StudentController::class, 'updateWalletBalance'])
    ->middleware(['auth','verified'])
    ->name('student.wallet.update');

Route::post('/student/wallet/deposit-inquiry', [StudentController::class, 'storeWalletDepositInquiry'])
    ->middleware(['auth','verified'])
    ->name('student.wallet.deposit-inquiry');

Route::get('/student/notification', [StudentController::class, 'notifications'])
    ->middleware(['auth','verified'])
    ->name('student.notification');

Route::get('/student/notification-data', [StudentController::class, 'notificationData'])
    ->middleware(['auth','verified'])
    ->name('student.notification.data');

Route::get('/student/notification-stream', [StudentController::class, 'notificationStream'])
    ->middleware(['auth','verified'])
    ->name('student.notification.stream');

Route::post('/student/notification/mark-read', [StudentController::class, 'markNotificationRead'])
    ->middleware(['auth','verified'])
    ->name('student.notification.mark-read');

Route::post('/student/notification/mark-all-read', [StudentController::class, 'markAllNotificationsRead'])
    ->middleware(['auth','verified'])
    ->name('student.notification.mark-all-read');

Route::post('/student/notification/clear-all', [StudentController::class, 'clearAllNotifications'])
    ->middleware(['auth','verified'])
    ->name('student.notification.clear-all');

Route::get('/student/unread-count', [StudentController::class, 'unreadNotificationCount'])
    ->middleware(['auth','verified'])
    ->name('student.unread-count');

Route::patch('/student/profile', [StudentController::class, 'updateProfile'])
    ->middleware(['auth','verified'])
    ->name('student.profile.update');


/*
|--------------------------------------------------------------------------
| STAFF PROFILE
|--------------------------------------------------------------------------
*/

Route::get('/staff/profile', [DashboardController::class, 'profile'])
    ->middleware(['auth','verified'])
    ->name('staff.profile');

Route::patch('/staff/profile', [DashboardController::class, 'updateProfile'])
    ->middleware(['auth','verified'])
    ->name('staff.profile.update');


/*
|--------------------------------------------------------------------------
| AUTH GROUP
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | STAFF PAGES
    |--------------------------------------------------------------------------
    */

    Route::get('/staff/orders', [DashboardController::class, 'orders'])->name('staff.orders');
    Route::get('/staff/orders/{order}', [DashboardController::class, 'orderDetail'])->name('staff.order.detail');
    Route::get('/staff/notification', [DashboardController::class, 'notification'])->name('staff.notification');
    Route::get('/staff/notification-data', [DashboardController::class, 'notificationData'])->name('staff.notification.data');
    Route::post('/staff/notification/mark-read', [DashboardController::class, 'markStaffNotificationRead'])
        ->name('staff.notification.mark-read');

    Route::post('/staff/notification/mark-all-read', [DashboardController::class, 'markAllStaffNotificationsRead'])
        ->name('staff.notification.mark-all-read');

    Route::post('/staff/notification/clear-all', [DashboardController::class, 'clearAllStaffNotifications'])
        ->name('staff.notification.clear-all');

    Route::get('/staff/menu', [DashboardController::class, 'menu'])->name('staff.menu');
    Route::post('/staff/menu', [DashboardController::class, 'storeMenuItem'])->name('staff.menu.store');
    Route::delete('/staff/menu/{menuItem}', [DashboardController::class, 'destroyMenuItem'])->name('staff.menu.destroy');
    Route::patch('/staff/menu/{menuItem}/toggle', [DashboardController::class, 'toggleMenuItem'])->name('staff.menu.toggle');

    Route::get('/staff/wallet', [DashboardController::class, 'wallet'])->name('staff.wallet');

    Route::get('/staff/seats', [DashboardController::class, 'seats'])->name('staff.seats');
    Route::post('/staff/seats/release', [DashboardController::class, 'releaseSeat'])->name('staff.seats.release');

    Route::get('/staff/feedbacks', [DashboardController::class, 'feedbacks'])->name('staff.feedbacks');

    Route::get('/staff/reports', [DashboardController::class, 'reports'])->name('staff.reports');

    Route::patch('/staff/deposit-inquiries/{walletDepositInquiry}/done', [DashboardController::class, 'completeDepositInquiry'])
        ->name('staff.deposit-inquiry.done');


    /*
    |--------------------------------------------------------------------------
    | STUDENT CANTEEN ROUTES
    |--------------------------------------------------------------------------
    */

    Route::get('/student/canteen/{college}', [StudentController::class, 'showCanteen'])
        ->name('student.canteen');


    Route::get('/student/reserve/{college}', function ($college) {
        $occupied = DB::table('seat_reservations')
            ->where('college', $college)
            ->pluck('seat_number')
            ->all();

        return view('student.reservation.reserve', [
            'college' => $college,
            'occupied' => $occupied,
        ]);
    })->name('student.reserve');


    Route::post('/student/confirm-seat', function (Request $request) {
        $validated = $request->validate([
            'college' => ['required', 'string'],
            'seat' => ['required', 'integer', 'between:1,25'],
        ]);

        $alreadyTaken = DB::table('seat_reservations')
            ->where('college', $validated['college'])
            ->where('seat_number', $validated['seat'])
            ->where('user_id', '!=', $request->user()->id)
            ->exists();

        if ($alreadyTaken) {
            return back()->with('error', 'Seat is already reserved. Please choose another seat.');
        }

        DB::table('seat_reservations')->updateOrInsert(
            [
                'user_id' => $request->user()->id,
                'college' => $validated['college'],
            ],
            [
                'seat_number' => $validated['seat'],
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $canteenLabel = config('canteens')[$validated['college']]['label'] ?? $validated['college'];
        ActivityNotification::notifyUser(
            $request->user()->id,
            ActivityNotification::TYPE_SEAT_RESERVED,
            'Seat reserved',
            'Seat #'.$validated['seat'].' at '.$canteenLabel.'.',
            null
        );

        return redirect()
            ->route('student.canteen', ['college' => $validated['college']])
            ->with('seat', $validated['seat']);

    })->name('student.confirm.seat');

});

require __DIR__.'/auth.php';
