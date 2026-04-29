<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Models\ActivityNotification;
use App\Models\UserCanteenBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/continue-as/{role}', function (string $role) {
    abort_unless(in_array($role, ['student', 'staff', 'admin'], true), 404);

    if (Auth::check()) {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    return redirect()->route('login', ['role' => $role]);
})->name('continue-as');

Route::get('/force-logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect('/');
})->name('force-logout');

/*
|--------------------------------------------------------------------------
| REDIRECT DASHBOARD (ROLE SWITCHER)
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {

    if (! Auth::check()) {
        return redirect('/');
    }

    $role = strtolower(trim((string) (Auth::user()->role ?? 'student')));
    if ($role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    return $role === 'student'
        ? redirect()->route('student.dashboard')
        : redirect()->route('staff.dashboard');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/admin/dashboard', [AdminController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('admin.dashboard');
Route::get('/admin/coupons', [AdminController::class, 'coupons'])
    ->middleware(['auth', 'verified'])
    ->name('admin.coupons');
Route::post('/admin/coupons', [AdminController::class, 'storeCoupon'])
    ->middleware(['auth', 'verified'])
    ->name('admin.coupons.store');
Route::patch('/admin/coupons/{coupon}', [AdminController::class, 'updateCoupon'])
    ->middleware(['auth', 'verified'])
    ->name('admin.coupons.update');
Route::post('/admin/users/{user}/inactive-label', [AdminController::class, 'toggleInactiveLabel'])
    ->middleware(['auth', 'verified'])
    ->name('admin.users.inactive.toggle');

/*
|--------------------------------------------------------------------------
| STAFF DASHBOARD (NEW CLEAN ROUTE)
|--------------------------------------------------------------------------
*/

Route::get('/staff/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('staff.dashboard');

/*
|--------------------------------------------------------------------------
| STUDENT DASHBOARD
|--------------------------------------------------------------------------
*/

Route::get('/student/dashboard', [StudentController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('student.dashboard');
Route::post('/student/orders/{orderId}/feedback', [StudentController::class, 'submitFeedback'])
    ->middleware(['auth', 'verified'])
    ->name('student.feedback.submit');
Route::post('/student/orders/{order}/qr', [StudentController::class, 'generateOrderQr'])
    ->middleware(['auth', 'verified'])
    ->name('student.order.qr');

/*
|--------------------------------------------------------------------------
| STUDENT PROFILE
|--------------------------------------------------------------------------
*/

Route::get('/student/profile', [StudentController::class, 'profile'])
    ->middleware(['auth', 'verified'])
    ->name('student.profile');

Route::get('/student/orders', [StudentController::class, 'orders'])
    ->middleware(['auth', 'verified'])
    ->name('student.orders');

Route::get('/student/orders/{order}/receipt', [StudentController::class, 'orderReceipt'])
    ->middleware(['auth', 'verified'])
    ->name('student.orders.receipt');

Route::get('/student/wallet', [StudentController::class, 'wallet'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet');
Route::get('/student/wallet/receipts/{receipt}', [StudentController::class, 'showWalletReceipt'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet.receipts.show');

Route::post('/student/wallet/update/{studentId}', [StudentController::class, 'updateWalletBalance'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet.update');

Route::post('/student/wallet/deposit-inquiry', [StudentController::class, 'storeWalletDepositInquiry'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet.deposit-inquiry');
Route::get('/student/connect/search', [StudentController::class, 'connectSearch'])
    ->middleware(['auth', 'verified'])
    ->name('student.connect.search');
Route::post('/student/connect/send', [StudentController::class, 'sendCoins'])
    ->middleware(['auth', 'verified'])
    ->name('student.connect.send');

Route::get('/student/notification', [StudentController::class, 'notifications'])
    ->middleware(['auth', 'verified'])
    ->name('student.notification');

Route::get('/student/notification-data', [StudentController::class, 'notificationData'])
    ->middleware(['auth', 'verified'])
    ->name('student.notification.data');

Route::get('/student/notification-stream', [StudentController::class, 'notificationStream'])
    ->middleware(['auth', 'verified'])
    ->name('student.notification.stream');

Route::post('/student/notification/mark-read', [StudentController::class, 'markNotificationRead'])
    ->middleware(['auth', 'verified'])
    ->name('student.notification.mark-read');

Route::post('/student/notification/mark-all-read', [StudentController::class, 'markAllNotificationsRead'])
    ->middleware(['auth', 'verified'])
    ->name('student.notification.mark-all-read');

Route::post('/student/notification/clear-all', [StudentController::class, 'clearAllNotifications'])
    ->middleware(['auth', 'verified'])
    ->name('student.notification.clear-all');

Route::get('/student/unread-count', [StudentController::class, 'unreadNotificationCount'])
    ->middleware(['auth', 'verified'])
    ->name('student.unread-count');

Route::patch('/student/profile', [StudentController::class, 'updateProfile'])
    ->middleware(['auth', 'verified'])
    ->name('student.profile.update');

/*
|--------------------------------------------------------------------------
| STAFF PROFILE
|--------------------------------------------------------------------------
*/

Route::get('/staff/profile', [DashboardController::class, 'profile'])
    ->middleware(['auth', 'verified'])
    ->name('staff.profile');

Route::patch('/staff/profile', [DashboardController::class, 'updateProfile'])
    ->middleware(['auth', 'verified'])
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
    Route::get('/staff/qr-scanner', [DashboardController::class, 'qrScanner'])->name('staff.qr.scanner');
    Route::get('/staff/qr/{token}', [DashboardController::class, 'qrConfirm'])->name('staff.qr.confirm');
    Route::post('/staff/qr/{token}/consume', [DashboardController::class, 'qrConsume'])->name('staff.qr.consume');
    Route::get('/staff/orders/{order}', [DashboardController::class, 'orderDetail'])->name('staff.order.detail');
    Route::patch('/staff/orders/{order}/status', [DashboardController::class, 'updateOrderStatus'])->name('staff.orders.status');
    Route::get('/staff/notification', [DashboardController::class, 'notification'])->name('staff.notification');
    Route::get('/staff/notification-data', [DashboardController::class, 'notificationData'])->name('staff.notification.data');
    Route::post('/staff/notification/mark-read', [DashboardController::class, 'markStaffNotificationRead'])
        ->name('staff.notification.mark-read');

    Route::post('/staff/notification/mark-all-read', [DashboardController::class, 'markAllStaffNotificationsRead'])
        ->name('staff.notification.mark-all-read');

    Route::post('/staff/notification/clear-all', [DashboardController::class, 'clearAllStaffNotifications'])
        ->name('staff.notification.clear-all');
    Route::get('/staff/unread-count', [DashboardController::class, 'unreadStaffNotificationCount'])
        ->name('staff.unread-count');

    Route::get('/staff/menu', [DashboardController::class, 'menu'])->name('staff.menu');
    Route::post('/staff/menu', [DashboardController::class, 'storeMenuItem'])->name('staff.menu.store');
    Route::delete('/staff/menu/{menuItem}', [DashboardController::class, 'destroyMenuItem'])->name('staff.menu.destroy');
    Route::patch('/staff/menu/{menuItem}/toggle', [DashboardController::class, 'toggleMenuItem'])->name('staff.menu.toggle');
    Route::patch('/staff/menu/{menuItem}', [DashboardController::class, 'updateMenuItem'])->name('staff.menu.update');

    Route::get('/staff/wallet', [DashboardController::class, 'wallet'])->name('staff.wallet');

    Route::get('/staff/seats', [DashboardController::class, 'seats'])->name('staff.seats');
    Route::post('/staff/seats/release', [DashboardController::class, 'releaseSeat'])->name('staff.seats.release');
    Route::post('/staff/seats/release-all', [DashboardController::class, 'releaseAllSeats'])->name('staff.seats.release-all');

    Route::get('/staff/feedbacks', [DashboardController::class, 'feedbacks'])->name('staff.feedbacks');
    Route::post('/staff/feedbacks/{feedback}/reply', [DashboardController::class, 'replyFeedback'])->name('staff.feedbacks.reply');

    Route::get('/staff/reports', [DashboardController::class, 'reports'])->name('staff.reports');
    Route::get('/staff/reports/print', [DashboardController::class, 'reportsPrint'])->name('staff.reports.print');

    Route::patch('/staff/deposit-inquiries/{walletDepositInquiry}/done', [DashboardController::class, 'completeDepositInquiry'])
        ->name('staff.deposit-inquiry.done');

    /*
    |--------------------------------------------------------------------------
    | STUDENT CANTEEN ROUTES
    |--------------------------------------------------------------------------
    */

    Route::get('/student/canteen/{college}', [StudentController::class, 'showCanteen'])
        ->name('student.canteen');

    Route::get('/student/cart', [StudentController::class, 'cartHub'])->name('student.cart.hub');
    Route::get('/student/cart/{college}', [StudentController::class, 'showCart'])->name('student.cart');
    Route::post('/student/cart/{college}/add', [StudentController::class, 'cartAdd'])->name('student.cart.add');
    Route::post('/student/cart/{college}/qty', [StudentController::class, 'cartSetQty'])->name('student.cart.qty');
    Route::post('/student/cart/{college}/remove', [StudentController::class, 'cartRemoveItem'])->name('student.cart.remove');
    Route::post('/student/cart/{college}/checkout', [StudentController::class, 'cartCheckout'])->name('student.cart.checkout');

    Route::get('/student/reserve/{college}', function ($college) {
        $collegeNorm = UserCanteenBalance::normalizedCollege((string) $college);
        if (! array_key_exists($collegeNorm, config('canteens', []))) {
            abort(404);
        }

        $occupied = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->pluck('seat_number')
            ->all();

        return view('student.reservation.reserve', [
            'college' => $collegeNorm,
            'occupied' => $occupied,
        ]);
    })->name('student.reserve');

    Route::post('/student/confirm-seat', function (Request $request) {
        $canteenKeys = array_keys(config('canteens', []));
        $request->merge([
            'college' => UserCanteenBalance::normalizedCollege((string) $request->input('college', '')),
        ]);

        $validated = $request->validate([
            'college' => ['required', 'string', Rule::in($canteenKeys)],
            'seat' => ['required', 'integer', 'between:1,25'],
        ]);

        $collegeNorm = UserCanteenBalance::normalizedCollege((string) $validated['college']);
        if (! array_key_exists($collegeNorm, config('canteens', []))) {
            abort(404);
        }

        $alreadyTaken = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->where('seat_number', $validated['seat'])
            ->where('user_id', '!=', $request->user()->id)
            ->exists();

        if ($alreadyTaken) {
            return back()->with('error', 'Seat is already reserved. Please choose another seat.');
        }

        DB::transaction(function () use ($request, $collegeNorm, $validated) {
            DB::table('seat_reservations')
                ->where('user_id', $request->user()->id)
                ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
                ->delete();

            DB::table('seat_reservations')->insert([
                'user_id' => $request->user()->id,
                'college' => $collegeNorm,
                'seat_number' => $validated['seat'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $canteenLabel = config('canteens')[$collegeNorm]['label'] ?? $collegeNorm;
        ActivityNotification::notifyUser(
            $request->user()->id,
            ActivityNotification::TYPE_SEAT_RESERVED,
            'Seat reserved',
            'Seat #'.$validated['seat'].' at '.$canteenLabel.'.',
            null
        );

        return redirect()
            ->route('student.canteen', ['college' => $collegeNorm])
            ->with('seat', $validated['seat']);

    })->name('student.confirm.seat');
});

require __DIR__.'/auth.php';
