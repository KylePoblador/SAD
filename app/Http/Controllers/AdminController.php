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

        $users = User::query()
            ->select($userSelect)
            ->whereIn('role', ['student', 'staff'])
            ->latest()
            ->take(15)
            ->get();

        $lastOrdersByUser = Order::query()
            ->select('user_id', DB::raw('MAX(created_at) as last_order_at'))
            ->groupBy('user_id')
            ->pluck('last_order_at', 'user_id');

        $latestUsers = $users->map(function (User $user) use ($lastOrdersByUser, $inactiveCutoff, $hasLastLoginAt, $hasIsInactive) {
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
        });

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
            'latestUsers' => $latestUsers,
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

    public function showUser(User $user)
    {
        $this->assertAdmin();

        if (! in_array($user->role, ['student', 'staff'], true)) {
            abort(404);
        }

        $canteenBalances = [];
        if ($user->role === 'student' && Schema::hasTable('user_canteen_balances')) {
            $canteenBalances = UserCanteenBalance::query()->where('user_id', $user->id)->get();
        }

        return view('admin.users.show', [
            'user' => $user,
            'canteenBalances' => $canteenBalances,
            'recentTransactions' => $this->transactions->recent(30, $user->id),
            'orderCount' => $user->orders()->count(),
            'walletLoadCount' => $user->walletLoadsAsStudent()->count(),
            'transferCount' => $user->coinTransfersSent()->count() + $user->coinTransfersReceived()->count(),
            'refundCount' => $user->refundsAsStudent()->count(),
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
