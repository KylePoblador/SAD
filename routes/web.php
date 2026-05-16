<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentFriendController;
use App\Models\ActivityNotification;
use App\Models\SeatLayout;
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

Route::get('/admin/refunds', [RefundController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('admin.refunds');

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
| STAFF REFUND MANAGEMENT
|--------------------------------------------------------------------------
*/

Route::get('/staff/refunds', [RefundController::class, 'staffPage'])
    ->middleware(['auth', 'verified'])
    ->name('staff.refunds');

Route::get('/api/students', [RefundController::class, 'getStudents'])
    ->middleware(['auth', 'verified']);

Route::post('/staff/refunds', [RefundController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('staff.refunds.store');

Route::get('/staff/refunds/history', [RefundController::class, 'staffHistory'])
    ->middleware(['auth', 'verified'])
    ->name('staff.refunds.history');

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

Route::get('/student/refunds', [RefundController::class, 'studentPage'])
    ->middleware(['auth', 'verified'])
    ->name('student.refunds');

Route::get('/student/refunds/history/{studentId}', [RefundController::class, 'studentHistory'])
    ->middleware(['auth', 'verified'])
    ->name('student.refunds.history');

Route::post('/student/wallet/update/{studentId}', [StudentController::class, 'updateWalletBalance'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet.update');

Route::post('/student/wallet/load-qr', [StudentController::class, 'generateWalletLoadQr'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet.load-qr.generate');
Route::get('/student/wallet/load-qr/{token}', [StudentController::class, 'showWalletLoadQr'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet.load-qr.show');

Route::get('/student/connect/search', [StudentController::class, 'connectSearch'])
    ->middleware(['auth', 'verified'])
    ->name('student.connect.search');
Route::post('/student/connect/send', [StudentController::class, 'sendCoins'])
    ->middleware(['auth', 'verified'])
    ->name('student.connect.send');

Route::get('/student/friends', [StudentFriendController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('student.friends.index');
Route::get('/student/friends/search', [StudentFriendController::class, 'search'])
    ->middleware(['auth', 'verified'])
    ->name('student.friends.search');
Route::post('/student/friends/add', [StudentFriendController::class, 'add'])
    ->middleware(['auth', 'verified'])
    ->name('student.friends.add');
Route::post('/student/friends/{friendship}/accept', [StudentFriendController::class, 'accept'])
    ->middleware(['auth', 'verified'])
    ->name('student.friends.accept');
Route::delete('/student/friends/{friendship}/reject', [StudentFriendController::class, 'reject'])
    ->middleware(['auth', 'verified'])
    ->name('student.friends.reject');
Route::delete('/student/friends/{friend}/remove', [StudentFriendController::class, 'removeFriend'])
    ->middleware(['auth', 'verified'])
    ->name('student.friends.remove');

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
    Route::get('/staff/wallet-load/{token}', [DashboardController::class, 'walletLoadQrConfirm'])->name('staff.wallet-load.confirm');
    Route::post('/staff/wallet-load/{token}/consume', [DashboardController::class, 'walletLoadQrConsume'])->name('staff.wallet-load.consume');
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
    Route::post('/staff/seats/capacity', [DashboardController::class, 'updateSeatCapacities'])->name('staff.seats.capacity');

    Route::get('/staff/feedbacks', [DashboardController::class, 'feedbacks'])->name('staff.feedbacks');
    Route::post('/staff/feedbacks/{feedback}/reply', [DashboardController::class, 'replyFeedback'])->name('staff.feedbacks.reply');

    Route::get('/staff/reports', [DashboardController::class, 'reports'])->name('staff.reports');
    Route::get('/staff/reports/print', [DashboardController::class, 'reportsPrint'])->name('staff.reports.print');

    /*
    |--------------------------------------------------------------------------
    | STUDENT CANTEEN ROUTES
    |--------------------------------------------------------------------------
    */

    Route::get('/student/canteen/{college}', [StudentController::class, 'showCanteen'])
        ->name('student.canteen');
    Route::post('/student/canteen/{college}/mode', [StudentController::class, 'setOrderMode'])
        ->name('student.canteen.mode');

    Route::get('/student/cart', [StudentController::class, 'cartHub'])->name('student.cart.hub');
    Route::get('/student/cart/{college}', [StudentController::class, 'showCart'])->name('student.cart');
    Route::post('/student/cart/{college}/add', [StudentController::class, 'cartAdd'])->name('student.cart.add');
    Route::post('/student/cart/{college}/qty', [StudentController::class, 'cartSetQty'])->name('student.cart.qty');
    Route::post('/student/cart/{college}/remove', [StudentController::class, 'cartRemoveItem'])->name('student.cart.remove');
    Route::post('/student/cart/{college}/checkout', [StudentController::class, 'cartCheckout'])->name('student.cart.checkout');

    Route::get('/student/reserve/{college}', [StudentController::class, 'reserveSeatForm'])->name('student.reserve');
    Route::post('/student/confirm-seat', [StudentController::class, 'reserveSeatConfirm'])->name('student.confirm-seat');
});

require __DIR__.'/auth.php';
