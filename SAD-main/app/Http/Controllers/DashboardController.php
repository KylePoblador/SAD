<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        return view('staff.dashboard', [
            'todayOrders'    => 3,
            'revenue'        => 285,
            'availableSeats' => 7,
            'totalSeats'     => 25,
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

    // ✅ FINAL WORKING REPORTS
    public function reports()
    {
        try {
            $today = now()->toDateString();

            // ✅ Revenue (safe)
            $todayRevenue = 0;
            if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'total')) {
                $todayRevenue = DB::table('orders')
                    ->whereDate('created_at', $today)
                    ->sum('total');
            }

            // ✅ Orders count
            $totalOrders = Schema::hasTable('orders')
                ? DB::table('orders')->whereDate('created_at', $today)->count()
                : 0;

            // ✅ Top Selling Items (safe join)
            $topItems = collect();

            if (
                Schema::hasTable('order_items') &&
                Schema::hasTable('products') &&
                Schema::hasTable('orders')
            ) {
                $topItems = DB::table('order_items')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->whereDate('orders.created_at', $today)
                    ->select(
                        'products.name',
                        DB::raw('SUM(order_items.quantity) as sold'),
                        DB::raw('SUM(order_items.quantity * order_items.price) as total')
                    )
                    ->groupBy('products.name')
                    ->orderByDesc('sold')
                    ->get();
            }

            // ✅ Total Sales
            $totalSales = $topItems->sum('total');

            return view('staff.reports', [
                'todayRevenue' => $todayRevenue,
                'totalOrders' => $totalOrders,
                'topItems' => $topItems,
                'totalSales' => $totalSales,
            ]);

        } catch (\Exception $e) {
            return "ERROR: " . $e->getMessage();
        }
    }
}
