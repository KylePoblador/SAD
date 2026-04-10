<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
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

    Route::get('/staff/menu', [DashboardController::class, 'menu'])->name('staff.menu');

    Route::get('/staff/wallet', [DashboardController::class, 'wallet'])->name('staff.wallet');

    Route::get('/staff/seats', [DashboardController::class, 'seats'])->name('staff.seats');

    Route::get('/staff/feedbacks', [DashboardController::class, 'feedbacks'])->name('staff.feedbacks');

    Route::get('/staff/reports', [DashboardController::class, 'reports'])->name('staff.reports');


    /*
    |--------------------------------------------------------------------------
    | STUDENT CANTEEN ROUTES
    |--------------------------------------------------------------------------
    */

    Route::get('/student/canteen/{college}', function ($college) {

        $canteens = [
            'ceit' => 'CEIT Canteen',
            'cass' => 'CASS Food Hub',
            'chefs' => 'CHEFS Dining',
            'cbdem' => 'CBDEM Snack Bar',
            'cti' => 'CTI Canteen'
        ];

        if (!array_key_exists($college, $canteens)) {
            abort(404);
        }

        $totalSeats = 25;
        $occupiedCount = DB::table('seat_reservations')
            ->where('college', $college)
            ->count();
        $availableSeats = max($totalSeats - $occupiedCount, 0);
        $reservedSeat = DB::table('seat_reservations')
            ->where('college', $college)
            ->where('user_id', Auth::id())
            ->value('seat_number');

        return view("student.canteen.$college", [
            'college' => $college,
            'canteenName' => $canteens[$college],
            'totalSeats' => $totalSeats,
            'occupiedCount' => $occupiedCount,
            'availableSeats' => $availableSeats,
            'reservedSeat' => $reservedSeat,
            'hasReservedSeat' => $reservedSeat !== null,
        ]);

    })->name('student.canteen');


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

        return redirect()
            ->route('student.canteen', ['college' => $validated['college']])
            ->with('seat', $validated['seat']);

    })->name('student.confirm.seat');

});

require __DIR__.'/auth.php';
