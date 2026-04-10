<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index()
    {
        $baseCanteens = [
            ['name' => 'CEIT Canteen', 'college' => 'ceit', 'dist' => '50m', 'rating' => '4.5'],
            ['name' => 'CASS Food Hub', 'college' => 'cass', 'dist' => '120m', 'rating' => '4.2'],
            ['name' => 'CHEFS Dining', 'college' => 'chefs', 'dist' => '200m', 'rating' => '4.8'],
            ['name' => 'CTI Canteen', 'college' => 'cti', 'dist' => '20m', 'rating' => '4.1'],
        ];

        $occupiedMap = DB::table('seat_reservations')
            ->select('college', DB::raw('COUNT(*) as occupied_count'))
            ->groupBy('college')
            ->pluck('occupied_count', 'college');

        $totalSeats = 25;
        $canteens = array_map(function (array $canteen) use ($occupiedMap, $totalSeats) {
            $occupied = (int) ($occupiedMap[$canteen['college']] ?? 0);
            $available = max($totalSeats - $occupied, 0);

            return [
                ...$canteen,
                'seats' => $available . '/' . $totalSeats,
                'full' => $available === 0,
            ];
        }, $baseCanteens);

        return view('student.dashboard', [
            'canteens' => $canteens,
        ]);
    }

    public function profile()
    {
        return view('student.profile');
    }

    public function updateProfile(Request $request)
    {
        $request->user()->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('student.profile')->with('status', 'profile-updated');
    }
}
