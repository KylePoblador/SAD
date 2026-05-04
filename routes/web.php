<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Models\ActivityNotification;
use App\Models\UserCanteenBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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

    if (! Auth::check()) {
        return redirect('/');
    }

    if (Auth::user()->role === 'student') {
        return redirect()->route('student.dashboard');
    }
    if (Auth::user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('staff.dashboard');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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
Route::post('/student/orders/{order}/cancel', [StudentController::class, 'cancelPendingOrder'])
    ->middleware(['auth', 'verified'])
    ->name('student.orders.cancel');

Route::get('/student/orders/{order}/receipt', [StudentController::class, 'orderReceipt'])
    ->middleware(['auth', 'verified'])
    ->name('student.orders.receipt');

Route::get('/student/wallet', [StudentController::class, 'wallet'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet');

Route::post('/student/wallet/update/{studentId}', [StudentController::class, 'updateWalletBalance'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet.update');

Route::post('/student/wallet/connection', [StudentController::class, 'addConnection'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet.connection');
Route::get('/student/wallet/connection-search', [StudentController::class, 'searchStudentsForConnection'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet.connection-search');
Route::post('/student/wallet/connection-request', [StudentController::class, 'sendConnectionRequest'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet.connection-request');
Route::post('/student/wallet/connection-request/{connectionRequest}/respond', [StudentController::class, 'respondConnectionRequest'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet.connection-request.respond');
Route::delete('/student/wallet/connection-request/{connectionRequest}', [StudentController::class, 'cancelConnectionRequest'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet.connection-request.cancel');
Route::delete('/student/wallet/connection/{friend}', [StudentController::class, 'removeConnection'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet.connection.remove');
Route::post('/student/wallet/transfer', [StudentController::class, 'transferWallet'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet.transfer');
Route::get('/student/wallet/transfers/{walletTransfer}/receipt', [StudentController::class, 'walletTransferReceipt'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet.transfer.receipt');
Route::get('/student/wallet/transfers/{walletTransfer}/receipt/download', [StudentController::class, 'downloadWalletTransferReceipt'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet.transfer.receipt.download');
Route::post('/student/wallet/qr', [StudentController::class, 'createPaymentQr'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet.qr');
Route::post('/student/wallet/load-qr', [StudentController::class, 'createWalletLoadQr'])
    ->middleware(['auth', 'verified'])
    ->name('student.wallet.load-qr');

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
    Route::get('/staff/scan-pay', [DashboardController::class, 'scanPay'])->name('staff.scan-pay');
    Route::post('/staff/scan-pay', [DashboardController::class, 'processScanPay'])->name('staff.scan-pay.process');
    Route::post('/staff/scan-pay/confirm-wallet-load', [DashboardController::class, 'confirmWalletLoadQr'])->name('staff.scan-pay.confirm-wallet-load');
    Route::get('/staff/scan-pay/wallet-load-receipt/{walletLoadLog}', [DashboardController::class, 'walletLoadReceipt'])
        ->name('staff.scan-pay.wallet-load-receipt');
    Route::get('/staff/scan-pay/wallet-load-receipt/{walletLoadLog}/download', [DashboardController::class, 'downloadWalletLoadReceipt'])
        ->name('staff.scan-pay.wallet-load-receipt.download');

    Route::get('/staff/seats', [DashboardController::class, 'seats'])->name('staff.seats');
    Route::post('/staff/seats/release', [DashboardController::class, 'releaseSeat'])->name('staff.seats.release');
    Route::post('/staff/seats/release-all', [DashboardController::class, 'releaseAllSeats'])->name('staff.seats.release-all');

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
    Route::post('/student/canteen/{college}/mode', [StudentController::class, 'setCanteenMode'])
        ->name('student.canteen.mode');
    Route::post('/student/canteen/{college}/join-reservation', [StudentController::class, 'joinSeatReservation'])
        ->name('student.canteen.join-reservation');

    Route::get('/student/cart', [StudentController::class, 'cartHub'])->name('student.cart.hub');
    Route::get('/student/cart/{college}', [StudentController::class, 'showCart'])->name('student.cart');
    Route::post('/student/cart/{college}/add', [StudentController::class, 'cartAdd'])->name('student.cart.add');
    Route::post('/student/cart/{college}/buy-now', [StudentController::class, 'cartBuyNow'])->name('student.cart.buy-now');
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
        $currentSeat = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->where('user_id', auth()->id())
            ->value('seat_number');
        if ($currentSeat === null) {
            $currentSeat = DB::table('seat_reservations as sr')
                ->join('reservation_participants as rp', 'rp.seat_reservation_id', '=', 'sr.id')
                ->whereRaw('LOWER(TRIM(sr.college)) = ?', [$collegeNorm])
                ->where('rp.user_id', auth()->id())
                ->value('sr.seat_number');
        }

        return view('student.reservation.reserve', [
            'college' => $collegeNorm,
            'occupied' => $occupied,
            'currentSeat' => $currentSeat ? (int) $currentSeat : null,
        ]);
    })->name('student.reserve');

    Route::post('/student/reserve/{college}/cancel', function (Request $request, string $college) {
        $collegeNorm = UserCanteenBalance::normalizedCollege((string) $college);
        if (! array_key_exists($collegeNorm, config('canteens', []))) {
            abort(404);
        }

        DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->where('user_id', $request->user()->id)
            ->delete();

        DB::table('reservation_participants')
            ->where('user_id', $request->user()->id)
            ->whereIn('seat_reservation_id', function ($query) use ($collegeNorm): void {
                $query->select('id')
                    ->from('seat_reservations')
                    ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm]);
            })
            ->delete();

        $canteenLabel = config('canteens')[$collegeNorm]['label'] ?? strtoupper($collegeNorm);
        ActivityNotification::notifyUser(
            $request->user()->id,
            ActivityNotification::TYPE_SEAT_RESERVED,
            'Seat reservation canceled',
            'Your seat reservation at '.$canteenLabel.' was canceled.',
            null
        );

        return redirect()
            ->route('student.canteen', ['college' => $collegeNorm])
            ->with('status', 'Seat reservation canceled.');
    })->name('student.cancel.seat');

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

            DB::table('reservation_participants')
                ->where('user_id', $request->user()->id)
                ->whereIn('seat_reservation_id', function ($query) use ($collegeNorm): void {
                    $query->select('id')
                        ->from('seat_reservations')
                        ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm]);
                })
                ->delete();

            $reservationId = DB::table('seat_reservations')->insertGetId([
                'user_id' => $request->user()->id,
                'host_user_id' => $request->user()->id,
                'reservation_code' => strtoupper(Str::random(8)),
                'college' => $collegeNorm,
                'seat_number' => $validated['seat'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('reservation_participants')->insert([
                'seat_reservation_id' => $reservationId,
                'user_id' => $request->user()->id,
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::patch('/admin/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
});

require __DIR__.'/auth.php';
