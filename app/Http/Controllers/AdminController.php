<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Refund;
use App\Models\User;
use App\Models\UserCanteenBalance;
use App\Services\AdminTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    public function __construct(
        protected AdminTransactionService $transactions,
    ) {}

    protected function assertAdmin(): void
    {
        if (strtolower(trim((string) (auth()->user()->role ?? ''))) !== 'admin') {
            abort(403);
        }
    }

    public function index()
    {
        $this->assertAdmin();

        $inactiveCutoff = now()->subMonths(6);
        $hasLastLoginAt = Schema::hasColumn('users', 'last_login_at');
        $hasIsInactive = Schema::hasColumn('users', 'is_inactive');

        $userSelect = ['id', 'name', 'email', 'role'];
        if ($hasLastLoginAt) {
            $userSelect[] = 'last_login_at';
        }
        if ($hasIsInactive) {
            $userSelect[] = 'is_inactive';
        }

        $students = User::query()
            ->select($userSelect)
            ->where('role', 'student')
            ->latest()
            ->take(10)
            ->get();

        $staff = User::query()
            ->select($userSelect)
            ->where('role', 'staff')
            ->latest()
            ->take(10)
            ->get();

        $allUserIds = $students->pluck('id')->merge($staff->pluck('id'));

        $lastOrdersByUser = Order::query()
            ->select('user_id', DB::raw('MAX(created_at) as last_order_at'))
            ->whereIn('user_id', $allUserIds)
            ->groupBy('user_id')
            ->pluck('last_order_at', 'user_id');

        $mapUser = function (User $user) use ($lastOrdersByUser, $inactiveCutoff, $hasLastLoginAt, $hasIsInactive) {
            $lastOrderAt = isset($lastOrdersByUser[$user->id]) ? Carbon::parse((string) $lastOrdersByUser[$user->id]) : null;
            $lastLoginAt = $hasLastLoginAt && $user->last_login_at ? Carbon::parse((string) $user->last_login_at) : null;
            $lastActiveAt = $lastLoginAt;
            if ($lastOrderAt && (! $lastActiveAt || $lastOrderAt->gt($lastActiveAt))) {
                $lastActiveAt = $lastOrderAt;
            }

            return (object) [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'last_active_at' => $lastActiveAt,
                'is_auto_inactive' => ! $lastActiveAt || $lastActiveAt->lt($inactiveCutoff),
                'is_inactive' => $hasIsInactive ? (bool) $user->is_inactive : false,
            ];
        };

        $latestStudents = $students->map($mapUser);
        $latestStaff = $staff->map($mapUser);

        $usersByRole = User::query()->select('role', DB::raw('COUNT(*) as total'))->groupBy('role')->pluck('total', 'role');

        return view('admin.dashboard', [
            'totalUsers' => (int) User::query()->whereIn('role', ['student', 'staff'])->count(),
            'studentCount' => (int) ($usersByRole['student'] ?? 0),
            'staffCount' => (int) ($usersByRole['staff'] ?? 0),
            'adminCount' => (int) ($usersByRole['admin'] ?? 0),
            'totalOrders' => (int) Order::query()->count(),
            'activeCoupons' => (int) Coupon::query()->where('is_active', true)->count(),
            'inactiveCount' => Schema::hasColumn('users', 'is_inactive')
                ? (int) User::query()->whereIn('role', ['student', 'staff'])->where('is_inactive', true)->count()
                : 0,
            'latestStudents' => $latestStudents,
            'latestStaff' => $latestStaff,
            'recentTransactions' => $this->transactions->recent(20),
        ]);
    }

    public function transactions(Request $request)
    {
        $this->assertAdmin();

        $validated = $request->validate([
            'type' => ['nullable', 'string', 'in:order,wallet_load,coin_transfer,refund,payment'],
            'role' => ['nullable', 'string', 'in:student,staff'],
        ]);

        return view('admin.transactions.index', [
            'transactions' => $this->transactions->paginate(30, $validated['type'] ?? null, $validated['role'] ?? null),
            'filters' => ['type' => $validated['type'] ?? null, 'role' => $validated['role'] ?? null],
        ]);
    }

    public function users(Request $request)
    {
        $this->assertAdmin();

        $validated = $request->validate([
            'role' => ['nullable', 'string', 'in:student,staff'],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $users = User::query()
            ->whereIn('role', ['student', 'staff'])
            ->when($validated['role'] ?? null, fn ($q, $role) => $q->where('role', $role))
            ->when($validated['q'] ?? null, function ($q, $search) {
                $term = '%'.trim($search).'%';
                $q->where(fn ($inner) => $inner
                    ->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('student_id', 'like', $term)
                    ->orWhere('phone', 'like', $term));
            })
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => ['role' => $validated['role'] ?? null, 'q' => $validated['q'] ?? null],
        ]);
    }

    public function showUser(Request $request, User $user)
    {
        $this->assertAdmin();

        if (! in_array($user->role, ['student', 'staff'], true)) {
            abort(404);
        }

        $canteenBalances = collect();
        if ($user->role === 'student' && Schema::hasTable('user_canteen_balances')) {
            $canteenBalances = UserCanteenBalance::query()->where('user_id', $user->id)->get();
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Filter transactions
        $recentTransactions = $this->transactions->recent(500, $user->id); // Increased limit for better filtering
        if ($startDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $recentTransactions = $recentTransactions->filter(fn($tx) => $tx->occurred_at && $tx->occurred_at->gte($start));
        }
        if ($endDate) {
            $end = Carbon::parse($endDate)->endOfDay();
            $recentTransactions = $recentTransactions->filter(fn($tx) => $tx->occurred_at && $tx->occurred_at->lte($end));
        }

        // Student spending stats
        $dailySpent = 0;
        $weeklySpent = 0;
        $monthlySpent = 0;

        if ($user->role === 'student') {
            $now = now();
            $todayStart = $now->copy()->startOfDay();
            $weekStart = $now->copy()->startOfWeek();
            $monthStart = $now->copy()->startOfMonth();

            $orders = Order::where('user_id', $user->id)->where('status', 'completed')->get();
            $transfers = \App\Models\CoinTransfer::where('sender_user_id', $user->id)->get();

            $sumSpent = function ($start) use ($orders, $transfers) {
                $orderSum = $orders->where('created_at', '>=', $start)->sum(fn($o) => $o->payable_total ?? $o->total ?? 0);
                $transferSum = $transfers->where('created_at', '>=', $start)->sum('amount');
                return $orderSum + $transferSum;
            };

            $dailySpent = $sumSpent($todayStart);
            $weeklySpent = $sumSpent($weekStart);
            $monthlySpent = $sumSpent($monthStart);
        }

        return view('admin.users.show', [
            'user' => $user,
            'canteenBalances' => $canteenBalances,
            'recentTransactions' => $recentTransactions->values(),
            'orderCount' => $user->orders()->count(),
            'walletLoadCount' => $user->walletLoadsAsStudent()->count(),
            'transferCount' => $user->coinTransfersSent()->count() + $user->coinTransfersReceived()->count(),
            'refundCount' => $user->refundsAsStudent()->count(),
            'dailySpent' => $dailySpent,
            'weeklySpent' => $weeklySpent,
            'monthlySpent' => $monthlySpent,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ]);
    }

    public function editUser(User $user)
    {
        $this->assertAdmin();

        if (! in_array($user->role, ['student', 'staff'], true)) {
            abort(404);
        }

        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $this->assertAdmin();

        if (! in_array($user->role, ['student', 'staff'], true)) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'college' => ['nullable', 'string', 'max:100'],
            'student_id' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'string', 'in:student,staff'],
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.show', $user)->with('status', 'User profile updated successfully.');
    }

    public function reports()
    {
        $this->assertAdmin();

        $now = now();
        $todayStart = $now->copy()->startOfDay();
        $weekStart = $now->copy()->startOfWeek();
        $monthStart = $now->copy()->startOfMonth();

        // Get completed orders with canteen
        $orders = Order::where('status', 'completed')
            ->whereNotNull('canteen_id')
            ->get();

        // Get all unique canteens (colleges) from staff users
        $canteens = \App\Models\User::where('role', 'staff')
            ->whereNotNull('college')
            ->pluck('college')
            ->unique()
            ->filter()
            ->values();

        $canteenReports = collect();
        foreach ($canteens as $canteen) {
            $canteenOrders = $orders->where('canteen_id', $canteen);

            $canteenReports->push((object) [
                'canteen_id' => $canteen,
                'daily_sales' => $canteenOrders->where('created_at', '>=', $todayStart)->sum(fn($o) => $o->payable_total ?? $o->total ?? 0),
                'weekly_sales' => $canteenOrders->where('created_at', '>=', $weekStart)->sum(fn($o) => $o->payable_total ?? $o->total ?? 0),
                'monthly_sales' => $canteenOrders->where('created_at', '>=', $monthStart)->sum(fn($o) => $o->payable_total ?? $o->total ?? 0),
                'total_sales' => $canteenOrders->sum(fn($o) => $o->payable_total ?? $o->total ?? 0),
            ]);
        }

        return view('admin.reports.index', [
            'canteenReports' => $canteenReports->sortByDesc('total_sales')->values(),
        ]);
    }

    public function refunds()
    {
        $this->assertAdmin();

        return view('admin.refunds.index', [
            'refunds' => Refund::query()->with(['staff:id,name,email,role', 'student:id,name,email,role'])->latest()->paginate(30),
            'totalRefunded' => (float) Refund::query()->sum('amount'),
        ]);
    }

    public function toggleInactiveLabel(Request $request, User $user)
    {
        $this->assertAdmin();

        if (! Schema::hasColumn('users', 'is_inactive')) {
            return back()->with('error', 'Inactive labeling is not ready. Run migrations first.');
        }

        $validated = $request->validate(['is_inactive' => ['required', 'boolean']]);
        $isInactive = (bool) $validated['is_inactive'];
        $user->update([
            'is_inactive' => $isInactive,
            'inactive_labeled_at' => $isInactive ? now() : null,
        ]);

        return back()->with('status', 'User inactive label updated.');
    }

    public function coupons()
    {
        $this->assertAdmin();

        return view('admin.coupons.index', [
            'coupons' => Coupon::query()->latest()->paginate(20),
        ]);
    }

    public function storeCoupon(Request $request)
    {
        $this->assertAdmin();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:32', 'unique:coupons,code'],
            'type' => ['required', 'in:fixed,percent'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'min_order_total' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        Coupon::create([
            'code' => strtoupper(trim($validated['code'])),
            'type' => $validated['type'],
            'value' => $validated['value'],
            'min_order_total' => $validated['min_order_total'] ?? 0,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('admin.coupons')->with('status', 'Coupon created.');
    }

    public function updateCoupon(Request $request, Coupon $coupon)
    {
        $this->assertAdmin();

        $validated = $request->validate([
            'type' => ['required', 'in:fixed,percent'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'min_order_total' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $coupon->update([
            'type' => $validated['type'],
            'value' => $validated['value'],
            'min_order_total' => $validated['min_order_total'] ?? 0,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()->route('admin.coupons')->with('status', 'Coupon updated.');
    }
}
