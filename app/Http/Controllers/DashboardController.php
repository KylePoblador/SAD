<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $collegeCode = auth()->user()->college ?: 'ceit';
        $collegeMeta = [
            'ceit' => ['name' => 'CEIT Main Canteen', 'rating' => 4.5, 'todayOrders' => 8, 'revenue' => 1260],
            'cass' => ['name' => 'CASS Food Hub', 'rating' => 4.2, 'todayOrders' => 6, 'revenue' => 980],
            'chefs' => ['name' => 'CHEFS Dining', 'rating' => 4.8, 'todayOrders' => 10, 'revenue' => 1515],
            'cti' => ['name' => 'CTI Canteen', 'rating' => 4.1, 'todayOrders' => 5, 'revenue' => 745],
        ];
        $selectedCollege = $collegeMeta[$collegeCode] ?? $collegeMeta['ceit'];

        $totalSeats = 25;
        $occupiedCount = DB::table('seat_reservations')
            ->where('college', $collegeCode)
            ->count();
        $availableSeats = max($totalSeats - $occupiedCount, 0);

        return view('Staff.dashboard', [
            'staffCollegeName' => $selectedCollege['name'],
            'collegeCode' => strtoupper($collegeCode),
            'todayOrders'    => $selectedCollege['todayOrders'],
            'revenue'        => $selectedCollege['revenue'],
            'availableSeats' => $availableSeats,
            'occupiedSeats'  => $occupiedCount,
            'totalSeats'     => $totalSeats,
            'rating'         => $selectedCollege['rating'],
            'recentOrders'   => [],
        ]);
    }

    public function profile()
    {
        return view('Staff.profile');
    }

    public function updateProfile(Request $request)
    {
        $request->user()->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('staff.profile')->with('status', 'profile-updated');
    }

    public function orders()    { return view('Staff.orders'); }
    public function menu()      { return view('Staff.menu'); }
    public function wallet()    { return view('Staff.wallet'); }
    public function seats()     { return view('Staff.seats'); }
    public function feedbacks() { return view('Staff.feedbacks'); }
    public function reports()   { return view('Staff.reports'); }
}
