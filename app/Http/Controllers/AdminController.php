<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    protected function assertAdmin(): void
    {
        $role = strtolower(trim((string) (auth()->user()->role ?? '')));
        if ($role !== 'admin') {
            abort(403);
        }
    }

    public function index()
    {
        $this->assertAdmin();

        $now = now();
        $inactiveCutoff = $now->copy()->subMonths(6);
        $hasLastLoginAt = Schema::hasColumn('users', 'last_login_at');
        $hasIsInactive = Schema::hasColumn('users', 'is_inactive');
        $hasInactiveLabeledAt = Schema::hasColumn('users', 'inactive_labeled_at');

        $userSelect = ['id', 'name', 'email', 'role'];
        if ($hasLastLoginAt) {
            $userSelect[] = 'last_login_at';
        }
        if ($hasIsInactive) {
            $userSelect[] = 'is_inactive';
        }
        if ($hasInactiveLabeledAt) {
            $userSelect[] = 'inactive_labeled_at';
        }

        $users = User::query()
            ->select($userSelect)
            ->latest()
            ->take(20)
            ->get();
        $lastOrdersByUser = Order::query()
            ->select('user_id', DB::raw('MAX(created_at) as last_order_at'))
            ->groupBy('user_id')
            ->pluck('last_order_at', 'user_id');
        $usersWithActivity = $users->map(function (User $user) use ($lastOrdersByUser, $inactiveCutoff) {
            $lastOrderAt = isset($lastOrdersByUser[$user->id]) ? Carbon::parse((string) $lastOrdersByUser[$user->id]) : null;
            $lastLoginAt = $hasLastLoginAt && $user->last_login_at ? Carbon::parse((string) $user->last_login_at) : null;
            $lastActiveAt = $lastLoginAt;
            if ($lastOrderAt && (! $lastActiveAt || $lastOrderAt->gt($lastActiveAt))) {
                $lastActiveAt = $lastOrderAt;
            }
            $isAutoInactive = ! $lastActiveAt || $lastActiveAt->lt($inactiveCutoff);

            return (object) [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'last_active_at' => $lastActiveAt,
                'is_auto_inactive' => $isAutoInactive,
                'is_inactive' => $hasIsInactive ? (bool) $user->is_inactive : false,
            ];
        });

        $inactiveCount = $usersWithActivity
            ->filter(fn ($u) => $u->is_auto_inactive || $u->is_inactive)
            ->count();

        $usersByRole = User::query()
            ->select('role', DB::raw('COUNT(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role');

        return view('admin.dashboard', [
            'totalUsers' => (int) User::query()->count(),
            'studentCount' => (int) ($usersByRole['student'] ?? 0),
            'staffCount' => (int) ($usersByRole['staff'] ?? 0),
            'adminCount' => (int) ($usersByRole['admin'] ?? 0),
            'totalOrders' => (int) Order::query()->count(),
            'activeCoupons' => (int) Coupon::query()->where('is_active', true)->count(),
            'inactiveCount' => $inactiveCount,
            'latestUsers' => $usersWithActivity,
        ]);
    }

    public function toggleInactiveLabel(Request $request, User $user)
    {
        $this->assertAdmin();
        if (! Schema::hasColumn('users', 'is_inactive')) {
            return back()->with('error', 'Inactive labeling is not ready. Run migrations first.');
        }

        $validated = $request->validate([
            'is_inactive' => ['required', 'boolean'],
        ]);
        $isInactive = (bool) $validated['is_inactive'];
        $user->update([
            'is_inactive' => $isInactive,
            'inactive_labeled_at' => $isInactive ? now() : null,
        ]);

        return redirect()->route('admin.dashboard')->with('status', 'User inactive label updated.');
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
