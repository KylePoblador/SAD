<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('staff.dashboard', [
            'todayOrders'    => 3,
            'revenue'        => 285,
            'availableSeats' => 7,
            'totalSeats'     => 50,
            'rating'         => 4.5,
            'recentOrders'   => [],
        ]);
    }

    public function profile()
    {
        return view('staff.profile');
    }

    public function updateProfile(Request $request)
    {
        $request->user()->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('staff.profile')->with('status', 'profile-updated');
    }

    public function orders()    { return view('staff.orders'); }
    public function menu()      { return view('staff.menu'); }
    public function wallet()    { return view('staff.wallet'); }
    public function seats()     { return view('staff.seats'); }
    public function feedbacks() { return view('staff.feedbacks'); }
    public function reports()   { return view('staff.reports'); }
}