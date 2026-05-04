<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $catalog = config('canteens', []);
        $collegeFilter = $request->query('college');
        $search = trim((string) $request->query('q', ''));
        $editUserId = (int) $request->query('edit_user', 0);

        $baseUsersQuery = User::query()
            ->select(
                'id',
                'name',
                'email',
                'role',
                'college',
                'student_id',
                'phone',
                'canteen_name',
                'email_verified_at',
                'created_at',
                'updated_at'
            )
            ->whereIn('role', ['student', 'staff']);

        if ($collegeFilter && array_key_exists($collegeFilter, $catalog)) {
            $baseUsersQuery->whereRaw('LOWER(TRIM(college)) = ?', [strtolower($collegeFilter)]);
        }
        if ($search !== '') {
            $baseUsersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('student_id', 'like', '%'.$search.'%');
            });
        }

        $students = (clone $baseUsersQuery)
            ->where('role', 'student')
            ->orderByDesc('created_at')
            ->paginate(12, ['*'], 'students_page')
            ->withQueryString();

        $staffMembers = (clone $baseUsersQuery)
            ->where('role', 'staff')
            ->orderByDesc('created_at')
            ->paginate(12, ['*'], 'staff_page')
            ->withQueryString();

        $collegeCoverage = collect($catalog)->map(function ($info, $slug) {
            return [
                'slug' => $slug,
                'label' => $info['label'] ?? strtoupper($slug),
                'students' => User::query()->where('role', 'student')->whereRaw('LOWER(TRIM(college)) = ?', [$slug])->count(),
                'staff' => User::query()->where('role', 'staff')->whereRaw('LOWER(TRIM(college)) = ?', [$slug])->count(),
            ];
        })->values();

        $inactiveThreshold = now()->subMonths(6);
        User::query()
            ->whereIn('role', ['student', 'staff'])
            ->where('updated_at', '<', $inactiveThreshold)
            ->update(['status' => 'inactive']);

        $managedUsersCount = User::query()->whereIn('role', ['student', 'staff'])->count();
        $inactiveUsersCount = User::query()
            ->whereIn('role', ['student', 'staff'])
            ->where('status', 'inactive')
            ->count();
        $activeUsersCount = User::query()
            ->whereIn('role', ['student', 'staff'])
            ->where('status', 'active')
            ->count();

        $inactiveUsers = User::query()
            ->whereIn('role', ['student', 'staff'])
            ->where('status', 'inactive')
            ->orderBy('updated_at')
            ->limit(8)
            ->get(['id', 'name', 'role', 'college', 'status', 'updated_at']);

        $thisMonthUsers = User::query()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $lastMonthDate = now()->copy()->subMonth();
        $lastMonthUsers = User::query()->whereMonth('created_at', $lastMonthDate->month)->whereYear('created_at', $lastMonthDate->year)->count();

        $thisWeekActiveUsers = User::query()
            ->whereIn('role', ['student', 'staff'])
            ->whereBetween('updated_at', [now()->copy()->startOfWeek(), now()->copy()->endOfWeek()])
            ->count();
        $lastWeekActiveUsers = User::query()
            ->whereIn('role', ['student', 'staff'])
            ->whereBetween('updated_at', [now()->copy()->subWeek()->startOfWeek(), now()->copy()->subWeek()->endOfWeek()])
            ->count();

        $calcGrowth = static function (float|int $current, float|int $previous): array {
            if ((float) $previous <= 0.0) {
                return [
                    'value' => null,
                    'trend' => 'neutral',
                    'label' => 'No baseline',
                ];
            }

            $percent = (($current - $previous) / $previous) * 100;
            $trend = $percent > 0 ? 'up' : ($percent < 0 ? 'down' : 'neutral');

            return [
                'value' => round($percent, 1),
                'trend' => $trend,
                'label' => ($percent > 0 ? '+' : '').round($percent, 1).'%',
            ];
        };

        $usersGrowth = $calcGrowth($thisMonthUsers, $lastMonthUsers);
        $activeUsersGrowth = $calcGrowth($thisWeekActiveUsers, $lastWeekActiveUsers);

        return view('admin.dashboard', [
            'catalog' => $catalog,
            'usersCount' => User::query()->whereIn('role', ['student', 'staff'])->count(),
            'activeUsersCount' => $activeUsersCount,
            'inactiveUsersCount' => $inactiveUsersCount,
            'usersGrowth' => $usersGrowth,
            'activeUsersGrowth' => $activeUsersGrowth,
            'studentsCount' => User::query()->where('role', 'student')->count(),
            'staffCount' => User::query()->where('role', 'staff')->count(),
            'collegeCoverage' => $collegeCoverage,
            'inactiveUsers' => $inactiveUsers,
            'students' => $students,
            'staffMembers' => $staffMembers,
            'collegeFilter' => $collegeFilter,
            'search' => $search,
            'editUser' => $editUserId > 0
                ? User::query()->whereIn('role', ['student', 'staff'])->find($editUserId)
                : null,
        ]);
    }

    public function destroyUser(User $user)
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        if ((int) $user->id === (int) auth()->id()) {
            return back()->with('status', 'admin-deactivate-self-blocked');
        }

        if (! in_array((string) $user->role, ['student', 'staff'], true)) {
            return back()->with('status', 'admin-deactivate-role-blocked');
        }

        $name = $user->name;
        if ((string) ($user->status ?? 'active') === 'inactive') {
            return back()->with('status', 'user-already-inactive')->with('deactivated_user_name', $name);
        }

        $user->update(['status' => 'inactive']);

        return back()->with('status', 'user-deactivated')->with('deactivated_user_name', $name);
    }

    public function updateUser(Request $request, User $user)
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        if (! in_array((string) $user->role, ['student', 'staff'], true)) {
            return back()->with('status', 'admin-edit-role-blocked');
        }

        $validated = $request->validate([
            '_form' => ['nullable', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['student', 'staff'])],
            'college' => ['required', 'string', Rule::in(array_keys(config('canteens', [])))],
            'student_id' => [
                Rule::requiredIf(fn () => $request->input('role') === 'student'),
                Rule::prohibitedIf(fn () => $request->input('role') !== 'student'),
                'nullable',
                'string',
                'max:64',
                Rule::unique('users', 'student_id')->ignore($user->id),
            ],
            'phone' => [
                Rule::requiredIf(fn () => $request->input('role') === 'student'),
                Rule::prohibitedIf(fn () => $request->input('role') !== 'student'),
                'nullable',
                'string',
                'max:32',
            ],
            'canteen_name' => [
                Rule::requiredIf(fn () => $request->input('role') === 'staff'),
                Rule::prohibitedIf(fn () => $request->input('role') !== 'staff'),
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        if ($validated['role'] === 'staff') {
            $takenByOther = User::query()
                ->where('role', 'staff')
                ->where('id', '!=', $user->id)
                ->whereRaw('LOWER(TRIM(college)) = ?', [strtolower((string) $validated['college'])])
                ->exists();
            if ($takenByOther) {
                return back()
                    ->withErrors(['college' => 'This college already has another staff account.'])
                    ->withInput();
            }
        }

        $user->update([
            'name' => $validated['name'],
            'email' => strtolower((string) $validated['email']),
            'role' => $validated['role'],
            'college' => strtolower((string) $validated['college']),
            'student_id' => $validated['role'] === 'student' ? ($validated['student_id'] ?: null) : null,
            'phone' => $validated['role'] === 'student' ? trim((string) $validated['phone']) : '',
            'canteen_name' => $validated['role'] === 'staff' ? trim((string) $validated['canteen_name']) : '',
        ]);

        return redirect()->route('admin.dashboard')->with('status', 'user-updated');
    }
}
