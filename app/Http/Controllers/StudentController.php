<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\User;

class StudentController extends Controller
{
    public function index()
    {
        $user = auth()->user();

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
            'walletBalance' => $user->wallet_balance,
        ]);
    }

    public function profile()
    {
        return view('student.profile');
    }

    public function notifications()
    {
        return view('student.notification');
    }

    public function notificationData()
    {
        $orders = Order::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $notifications = $orders->map(function (Order $order) {
            $statusText = match ($order->status) {
                'pending' => 'is pending confirmation',
                'preparing' => 'is being prepared',
                'ready' => 'is ready for pickup',
                'completed' => 'has been completed',
                default => 'was updated',
            };

            return [
                'id' => $order->id,
                'title' => "Order {$order->order_number} {$statusText}",
                'message' => "Total: ₱{$order->total}",
                'time' => $order->created_at->diffForHumans(),
                'status' => $order->status,
                'is_read' => $order->is_read,
            ];
        });

        if ($notifications->isEmpty()) {
            return response()->json([
                'notifications' => [
                    [
                        'id' => null,
                        'title' => 'No notifications yet',
                        'message' => 'Place an order to receive real-time updates here.',
                        'time' => '',
                        'status' => 'empty',
                        'is_read' => true,
                    ],
                ],
                'unread_count' => 0,
            ]);
        }

        $unreadCount = $orders->sum(fn($order) => $order->is_read ? 0 : 1);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function notificationStream()
    {
        return response()->stream(function () {
            while (true) {
                $orders = Order::where('user_id', auth()->id())
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();

                $notifications = $orders->map(function (Order $order) {
                    $statusText = match ($order->status) {
                        'pending' => 'is pending confirmation',
                        'preparing' => 'is being prepared',
                        'ready' => 'is ready for pickup',
                        'completed' => 'has been completed',
                        default => 'was updated',
                    };

                    return [
                        'id' => $order->id,
                        'title' => "Order {$order->order_number} {$statusText}",
                        'message' => "Total: ₱{$order->total}",
                        'time' => $order->created_at->diffForHumans(),
                        'status' => $order->status,
                        'is_read' => $order->is_read,
                    ];
                });

                $unreadCount = $orders->sum(fn($order) => $order->is_read ? 0 : 1);

                echo "data: " . json_encode([
                    'notifications' => $notifications,
                    'unread_count' => $unreadCount,
                ]) . "\n\n";

                ob_flush();
                flush();
                sleep(3); // Real-time updates every 3 seconds
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }

    public function markNotificationAsRead($orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->first();

        if ($order) {
            $order->update(['is_read' => true]);
        }

        return response()->json(['success' => true]);
    }

    public function unreadNotificationCount()
    {
        $count = Order::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    public function updateProfile(Request $request)
    {
        $request->user()->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('student.profile')->with('status', 'profile-updated');
    }

    public function orders()
    {
        $canteens = [
            1 => 'CEIT Canteen',
            2 => 'CASS Food Hub',
            3 => 'CHEFS Dining',
            4 => 'CTI Canteen',
            5 => 'CBDEM Snack Bar',
        ];

        $orders = Order::where('user_id', auth()->id())
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) use ($canteens) {
                $order->canteen = $canteens[$order->canteen_id] ?? 'Unknown Canteen';
                return $order;
            });

        return view('student.order', [
            'orders' => $orders,
        ]);
    }

    public function wallet()
    {
        $user = auth()->user();

        // Get orders for this student to calculate statistics
        $orders = Order::where('user_id', $user->id)->get();

        // Calculate wallet data
        $totalSpent = $orders->sum('total');
        $totalOrders = $orders->count();

        // Get recent transactions from orders
        $recentTransactions = $orders->sortByDesc('created_at')
            ->take(10)
            ->map(function ($order) {
                return [
                    'description' => 'Order ' . $order->order_number,
                    'amount' => $order->total,
                    'type' => 'debit',
                    'date' => $order->created_at->format('M d, Y H:i'),
                ];
            })
            ->values()
            ->toArray();

        $wallet = [
            'balance' => $user->wallet_balance,
            'college' => $user->college,
            'total_spent' => $totalSpent,
            'total_orders' => $totalOrders,
            'recent_transactions' => $recentTransactions,
        ];

        return view('student.wallet', [
            'wallet' => $wallet,
        ]);
    }

    public function updateWalletBalance(Request $request, $studentId)
    {
        $user = auth()->user();

        // Only staff can update wallets
        if ($user->role !== 'staff') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get the student
        $student = \App\Models\User::find($studentId);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Only allow updating students from the same canteen
        if ($student->college !== $user->college) {
            return response()->json(['error' => 'Cannot update student from different canteen'], 403);
        }

        $amount = $request->input('amount');

        if (!$amount || $amount <= 0) {
            return response()->json(['error' => 'Invalid amount'], 400);
        }

        // Update wallet balance
        $student->wallet_balance += $amount;
        $student->save();

        return response()->json([
            'success' => true,
            'new_balance' => $student->wallet_balance,
            'message' => 'Wallet updated successfully'
        ]);
    }
}


