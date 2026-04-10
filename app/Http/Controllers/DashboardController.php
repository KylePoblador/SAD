<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $collegeCode = auth()->user()->college ?: 'ceit';
        $canteenNames = [
            'ceit' => 'CEIT Canteen',
            'cass' => 'CASS Food Hub',
            'chefs' => 'CHEFS Dining',
            'cti' => 'CTI Canteen',
            'cbdem' => 'CBDEM Snack Bar',
        ];
        $staffCollegeName = $canteenNames[$collegeCode] ?? 'Assigned Canteen';

        return view('staff.dashboard', [
            'staffCollegeName' => $staffCollegeName,
            'collegeCode' => strtoupper($collegeCode),
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

    public function orders(Request $request)
    {
        $status = $request->query('status', 'pending');
        $collegeCode = auth()->user()->college ?: 'ceit';

        // Map college codes to canteen names
        $canteenNames = [
            'ceit' => 'CEIT Canteen',
            'cass' => 'CASS Food Hub',
            'chefs' => 'CHEFS Dining',
            'cti' => 'CTI Canteen',
            'cbdem' => 'CBDEM Snack Bar',
        ];
        $canteenName = $canteenNames[$collegeCode] ?? 'Canteen';

        // Fetch actual orders from database filtered by status
        $orders = Order::where('status', $status)
            ->where('canteen_id', $collegeCode)
            ->with('user', 'items')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return (object)[
                    'id' => $order->id,
                    'name' => $order->user->name ?? 'Unknown',
                    'student_id' => $order->user->id ?? 'N/A',
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total' => $order->total,
                    'created_at' => $order->created_at,
                    'items' => $order->items
                ];
            });

        return view('Staff.quickaction.orders', [
            'orders' => $orders,
            'status' => $status,
            'canteenName' => $canteenName
        ]);
    }

    public function orderDetail($orderId)
    {
        // Fetch actual order from database
        $order = Order::with('user', 'items')->find($orderId);

        if (!$order) {
            abort(404, 'Order not found');
        }

        $orderDetail = (object)[
            'id' => $order->id,
            'name' => $order->user->name ?? 'Unknown',
            'student_id' => $order->user->id ?? 'N/A',
            'order_number' => $order->order_number,
            'status' => $order->status,
            'total' => $order->total,
            'created_at' => $order->created_at,
            'items' => $order->items ?? collect([])
        ];

        return view('Staff.quickaction.order', [
            'order' => $orderDetail
        ]);
    }
    public function menu()      { return view('Staff.quickaction.menu'); }
    public function wallet()    { return view('Staff.quickaction.wallet'); }
    public function seats()     { return view('Staff.quickaction.seats'); }
    public function feedbacks() { return view('Staff.quickaction.feedbacks'); }
    public function reports()   { return view('Staff.quickaction.reports'); }
}
