<x-app-layout>
    <x-slot name="title">
        CoinMeal Administrator
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-[42px] font-semibold leading-tight text-slate-900">CoinMeal Administrator Console</h2>
                <span
                    class="mt-2 inline-flex rounded-lg bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">System-wide
                    access</span>
            </div>
            <div class="w-full max-w-xl space-y-3 md:w-auto">
                <div class="flex items-center justify-start gap-2 md:justify-end">
                    <span class="text-sm text-slate-600">{{ auth()->user()->name }}</span>
                    <a href="{{ route('profile.edit') }}"
                        class="rounded-lg bg-indigo-100 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-200">
                        Edit Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="rounded-lg bg-rose-100 px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-200">
                            Logout
                        </button>
                    </form>
                </div>
                <form method="GET" action="{{ route('admin.dashboard') }}">
                    <div class="flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m21 21-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" />
                        </svg>
                        <input type="text" name="q" value="{{ $search }}"
                            placeholder="Search users and colleges..."
                            class="w-full border-0 p-0 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0">
                    </div>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-100 py-6">
        <div class="mx-auto max-w-[1500px] space-y-6 px-5">
            @if (session('status') === 'user-deactivated')
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900">User
                    <strong>{{ session('deactivated_user_name') }}</strong> has been set to inactive.</div>
            @endif
            @if (session('status') === 'user-already-inactive')
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">User
                    <strong>{{ session('deactivated_user_name') }}</strong> is already inactive.</div>
            @endif
            @if (session('status') === 'admin-deactivate-self-blocked')
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">You cannot
                    deactivate your own admin account.</div>
            @endif
            @if (session('status') === 'admin-deactivate-role-blocked')
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">Only
                    student/staff accounts can be deactivated here.</div>
            @endif
            @if (session('status') === 'user-updated')
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900">User
                    updated successfully.</div>
            @endif
            @if (session('status') === 'admin-edit-role-blocked')
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">Only
                    student/staff accounts can be edited here.</div>
            @endif
            @if ($errors->any() && old('_form') === 'admin-user-edit')
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="hidden items-center justify-end gap-2">
                <a href="{{ route('profile.edit') }}"
                    class="rounded-lg bg-indigo-100 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-200">
                    Edit Profile
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="rounded-lg bg-rose-100 px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-200">
                        Logout
                    </button>
                </form>
            </div>

            <section>
                <h3 class="text-[42px] font-semibold text-slate-900">System Overview</h3>
                <div class="mt-4 grid gap-5 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm text-slate-500">Total Users</p>
                        <p class="mt-2 text-[48px] font-semibold leading-none text-slate-900">
                            {{ number_format($usersCount) }}</p>
                        @php
                            $usersGrowthClass =
                                ($usersGrowth['trend'] ?? 'neutral') === 'up'
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : (($usersGrowth['trend'] ?? 'neutral') === 'down'
                                        ? 'bg-rose-100 text-rose-700'
                                        : 'bg-slate-100 text-slate-600');
                        @endphp
                        <span
                            class="mt-4 inline-flex rounded-full px-3 py-1 text-sm font-medium {{ $usersGrowthClass }}">
                            {{ $usersGrowth['label'] ?? 'No baseline' }} this month
                        </span>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm text-slate-500">Active Users</p>
                        <p class="mt-2 text-[48px] font-semibold leading-none text-slate-900">
                            {{ number_format($activeUsersCount) }}</p>
                        @php
                            $activeGrowthClass =
                                ($activeUsersGrowth['trend'] ?? 'neutral') === 'up'
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : (($activeUsersGrowth['trend'] ?? 'neutral') === 'down'
                                        ? 'bg-rose-100 text-rose-700'
                                        : 'bg-slate-100 text-slate-600');
                        @endphp
                        <span
                            class="mt-4 inline-flex rounded-full px-3 py-1 text-sm font-medium {{ $activeGrowthClass }}">
                            {{ $activeUsersGrowth['label'] ?? 'No baseline' }} this week
                        </span>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm text-slate-500">Inactive Users</p>
                        <p class="mt-2 text-[48px] font-semibold leading-none text-slate-900">
                            {{ number_format($inactiveUsersCount) }}</p>
                        <span
                            class="mt-4 inline-flex rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-700">Needs
                            review</span>
                    </div>
                </div>
            </section>

            <section>
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-[42px] font-semibold text-slate-900">College Coverage</h3>
                    <form method="GET" action="{{ route('admin.dashboard') }}">
                        <select name="college" onchange="this.form.submit()"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-lg font-semibold text-slate-700">
                            <option value="">Filter by College</option>
                            @foreach ($catalog as $slug => $info)
                                <option value="{{ $slug }}" @selected($collegeFilter === $slug)>{{ $info['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="grid gap-5 lg:grid-cols-4">
                    @foreach ($collegeCoverage as $coverage)
                        @php
                            $healthy = $coverage['staff'] > 0;
                        @endphp
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <h4 class="text-[38px] font-semibold leading-tight text-slate-900">
                                    {{ $coverage['label'] }}</h4>
                                <span
                                    class="rounded-full px-3 py-1 text-sm font-medium {{ $healthy ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $healthy ? 'Healthy' : 'Needs Review' }}</span>
                            </div>
                            <div class="mt-6 space-y-2 text-2xl text-slate-700">
                                <p>Students <span class="float-right text-slate-900">{{ $coverage['students'] }}</span>
                                </p>
                                <p>Staff <span class="float-right text-slate-900">{{ $coverage['staff'] }}</span></p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-amber-200 bg-amber-50 px-6 py-3 text-sm text-amber-800">User becomes
                    INACTIVE after 6 months without account activity. Inactive users are retained for admin review
                    before deletion.</div>
                <div class="px-6 pb-6 pt-5">
                    <h3 class="text-[40px] font-semibold text-slate-900">Staff Accounts</h3>
                    <p class="text-xl text-slate-600">Manage all staff users across all colleges</p>

                    @if ($editUser)
                        @php
                            $selectedRole = old('role', $editUser->role);
                        @endphp
                        <div id="admin-edit-user" class="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-4">
                            <div class="mb-3 flex items-center justify-between">
                                <h4 class="font-semibold text-blue-900">Edit user: {{ $editUser->name }}</h4>
                                <a href="{{ route('admin.dashboard', request()->except('edit_user')) }}"
                                    class="text-sm font-medium text-blue-700 hover:underline">Cancel</a>
                            </div>
                            <form method="POST" action="{{ route('admin.users.update', $editUser) }}"
                                class="grid gap-3 md:grid-cols-3" id="admin-user-edit-form">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="_form" value="admin-user-edit">
                                <input type="text" name="name" value="{{ old('name', $editUser->name) }}"
                                    required placeholder="Name"
                                    class="rounded-lg border border-blue-200 px-3 py-2 text-sm">
                                <input type="email" name="email" value="{{ old('email', $editUser->email) }}"
                                    required placeholder="Email"
                                    class="rounded-lg border border-blue-200 px-3 py-2 text-sm">
                                <select name="role" id="edit-role-select"
                                    class="rounded-lg border border-blue-200 px-3 py-2 text-sm" required>
                                    <option value="student" @selected(old('role', $editUser->role) === 'student')>Student</option>
                                    <option value="staff" @selected(old('role', $editUser->role) === 'staff')>Staff</option>
                                </select>
                                <select name="college" class="rounded-lg border border-blue-200 px-3 py-2 text-sm"
                                    required>
                                    @foreach ($catalog as $slug => $info)
                                        <option value="{{ $slug }}" @selected(old('college', $editUser->college) === $slug)>
                                            {{ $info['label'] }}</option>
                                    @endforeach
                                </select>
                                <input type="text" data-role-field="student" name="student_id"
                                    value="{{ old('student_id', $editUser->student_id) }}" placeholder="Student ID"
                                    @disabled($selectedRole !== 'student')
                                    class="{{ $selectedRole === 'student' ? '' : 'hidden' }} rounded-lg border border-blue-200 px-3 py-2 text-sm">
                                <input type="text" data-role-field="student" name="phone"
                                    value="{{ old('phone', $editUser->phone) }}" placeholder="Phone"
                                    @disabled($selectedRole !== 'student')
                                    class="{{ $selectedRole === 'student' ? '' : 'hidden' }} rounded-lg border border-blue-200 px-3 py-2 text-sm">
                                <input type="text" data-role-field="staff" name="canteen_name"
                                    value="{{ old('canteen_name', $editUser->canteen_name) }}"
                                    placeholder="Canteen name"
                                    @disabled($selectedRole !== 'staff')
                                    class="{{ $selectedRole === 'staff' ? '' : 'hidden' }} rounded-lg border border-blue-200 px-3 py-2 text-sm">
                                <div class="md:col-span-3 flex justify-end">
                                    <button type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-lg border border-indigo-800 !bg-indigo-700 px-5 py-2.5 text-sm font-semibold !text-white opacity-100 shadow-md transition hover:!bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 active:!bg-indigo-900 md:w-auto">
                                        Confirm Edit
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <form method="GET" action="{{ route('admin.dashboard') }}"
                        class="mt-4 grid gap-3 md:grid-cols-3">
                        <input type="text" name="q" value="{{ $search }}"
                            placeholder="Search name/email/student ID"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <select name="college" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">All colleges</option>
                            @foreach ($catalog as $slug => $info)
                                <option value="{{ $slug }}" @selected($collegeFilter === $slug)>{{ $info['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Apply
                            Filter</button>
                    </form>

                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full text-base">
                            <thead>
                                <tr
                                    class="border-y border-slate-200 bg-slate-50 text-left text-sm uppercase tracking-wide text-slate-600">
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">College</th>
                                    <th class="px-4 py-3">Canteen</th>
                                    <th class="px-4 py-3">Last Login</th>
                                    <th class="px-4 py-3">Last Activity</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($staffMembers as $user)
                                    @php
                                        $collegeLabel = data_get($catalog, (string) $user->college . '.label');
                                        $inactive = (string) ($user->status ?? 'active') === 'inactive';
                                    @endphp
                                    <tr class="border-b border-slate-200 text-slate-700">
                                        <td class="px-4 py-4">{{ $user->name }}</td>
                                        <td class="px-4 py-4">{{ $user->email }}</td>
                                        <td class="px-4 py-4">
                                            {{ $collegeLabel ?: strtoupper((string) $user->college) }}</td>
                                        <td class="px-4 py-4">{{ $user->canteen_name ?: '—' }}</td>
                                        <td class="px-4 py-4">
                                            {{ optional($user->updated_at)->format('Y-m-d') ?: '—' }}</td>
                                        <td class="px-4 py-4">
                                            {{ optional($user->updated_at)->format('Y-m-d') ?: '—' }}</td>
                                        <td class="px-4 py-4">
                                            <span
                                                class="rounded-full px-3 py-1 text-xs font-semibold {{ $inactive ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $inactive ? 'INACTIVE' : 'ACTIVE' }}</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="inline-flex gap-2">
                                                <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['edit_user' => $user->id])) }}#admin-edit-user"
                                                    class="rounded-lg bg-indigo-100 px-3 py-1.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-200">Edit</a>
                                                <form method="POST"
                                                    action="{{ route('admin.users.destroy', $user) }}"
                                                    data-confirm-delete
                                                    data-confirm-title="Set account to inactive?"
                                                    data-confirm-message="The user will no longer be able to login until reactivated by admin.">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="rounded-lg bg-rose-100 px-3 py-1.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-700 hover:text-white">Set Inactive</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-4 text-center text-slate-500">No staff
                                            accounts found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $staffMembers->links() }}</div>
                </div>
                <div class="border-t border-amber-200 bg-amber-50 px-6 py-3 text-sm text-amber-800">User becomes
                    INACTIVE after 6 months without account activity. Inactive users are retained for admin review
                    before deletion.</div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="px-6 pb-6 pt-5">
                    <h3 class="text-[40px] font-semibold text-slate-900">Student Accounts</h3>
                    <p class="text-xl text-slate-600">Manage all student users across all colleges</p>

                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full text-base">
                            <thead>
                                <tr class="border-y border-slate-200 bg-slate-50 text-left text-sm uppercase tracking-wide text-slate-600">
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Student ID</th>
                                    <th class="px-4 py-3">College</th>
                                    <th class="px-4 py-3">Phone</th>
                                    <th class="px-4 py-3">Last Activity</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($students as $user)
                                    @php
                                        $collegeLabel = data_get($catalog, (string) $user->college . '.label');
                                        $inactive = (string) ($user->status ?? 'active') === 'inactive';
                                    @endphp
                                    <tr class="border-b border-slate-200 text-slate-700">
                                        <td class="px-4 py-4">{{ $user->name }}</td>
                                        <td class="px-4 py-4">{{ $user->email }}</td>
                                        <td class="px-4 py-4">{{ $user->student_id ?: '—' }}</td>
                                        <td class="px-4 py-4">{{ $collegeLabel ?: strtoupper((string) $user->college) }}</td>
                                        <td class="px-4 py-4">{{ $user->phone ?: '—' }}</td>
                                        <td class="px-4 py-4">{{ optional($user->updated_at)->format('Y-m-d') ?: '—' }}</td>
                                        <td class="px-4 py-4">
                                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $inactive ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $inactive ? 'INACTIVE' : 'ACTIVE' }}</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="inline-flex gap-2">
                                                <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['edit_user' => $user->id])) }}#admin-edit-user"
                                                    class="rounded-lg bg-indigo-100 px-3 py-1.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-200">Edit</a>
                                                <form method="POST"
                                                    action="{{ route('admin.users.destroy', $user) }}"
                                                    data-confirm-delete
                                                    data-confirm-title="Set account to inactive?"
                                                    data-confirm-message="The user will no longer be able to login until reactivated by admin.">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="rounded-lg bg-rose-100 px-3 py-1.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-700 hover:text-white">Set Inactive</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-4 text-center text-slate-500">No student accounts found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $students->links() }}</div>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-amber-200 bg-amber-50/50">
                <div class="px-6 py-4">
                    <h3 class="text-[38px] font-semibold text-slate-900">Users Inactive for 6+ Months</h3>
                    <p class="text-lg text-slate-600">Accounts are auto-marked inactive after 6 months without activity. Review and manage user accounts here.</p>
                </div>
                <div class="overflow-x-auto bg-white">
                    <table class="min-w-full text-base">
                        <thead>
                            <tr
                                class="border-y border-amber-200 bg-amber-50 text-left text-sm uppercase tracking-wide text-slate-600">
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">College</th>
                                <th class="px-4 py-3">Last Activity</th>
                                <th class="px-4 py-3">Inactive Duration</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inactiveUsers as $user)
                                @php
                                    $collegeLabel = data_get($catalog, (string) $user->college . '.label');
                                    $inactiveMonths = max(1, now()->diffInMonths($user->updated_at));
                                @endphp
                                <tr class="border-b border-slate-200 text-slate-700">
                                    <td class="px-4 py-4">{{ $user->name }}</td>
                                    <td class="px-4 py-4"><span
                                            class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold uppercase text-slate-600">{{ $user->role }}</span>
                                    </td>
                                    <td class="px-4 py-4">{{ $collegeLabel ?: strtoupper((string) $user->college) }}
                                    </td>
                                    <td class="px-4 py-4">{{ optional($user->updated_at)->format('Y-m-d') ?: '—' }}
                                    </td>
                                    <td class="px-4 py-4 font-semibold text-amber-700">{{ $inactiveMonths }} months
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="inline-flex gap-2">
                                            <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['edit_user' => $user->id])) }}#admin-edit-user"
                                                class="rounded-lg bg-indigo-100 px-3 py-1.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-200">View
                                                Profile</a>
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                data-confirm-delete
                                                data-confirm-title="Set account to inactive?"
                                                data-confirm-message="The user will no longer be able to login until reactivated by admin.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="rounded-lg bg-rose-100 px-3 py-1.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-700 hover:text-white">Set
                                                    Inactive</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-4 text-center text-slate-500">No inactive users
                                        beyond 6 months.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-amber-200 bg-rose-50 px-6 py-3 text-sm text-rose-700">Inactive accounts stay in the system. Admin may choose manual deletion only if needed.</div>
            </section>

            <section>
                <h3 class="text-[36px] font-semibold text-slate-900">Reports &amp; Admin Actions</h3>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h4 class="text-xl font-semibold text-slate-900">Daily Collections Report</h4>
                        <p class="text-base text-slate-600">View today's transactions</p>
                    </div>
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                        <h4 class="text-xl font-semibold text-slate-900">Inactive Users Report</h4>
                        <p class="text-base text-slate-600">6+ months inactivity</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <div id="confirm-delete-modal" class="fixed inset-0 z-[90] hidden items-center justify-center bg-slate-900/50 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-xl ring-1 ring-slate-200">
            <h3 id="confirm-delete-title" class="text-lg font-semibold text-slate-900">Set account to inactive?</h3>
            <p id="confirm-delete-message" class="mt-2 text-sm text-slate-600">The user will no longer be able to login until reactivated by admin.</p>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" id="confirm-delete-cancel"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Cancel
                </button>
                <button type="button" id="confirm-delete-submit"
                    class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">
                    Confirm Inactive
                </button>
            </div>
        </div>
    </div>
    @if ($editUser)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const roleSelect = document.getElementById('edit-role-select');
                if (!roleSelect) return;
                const editSection = document.getElementById('admin-edit-user');

                const allRoleFields = document.querySelectorAll('[data-role-field]');
                const syncRoleFields = () => {
                    const role = roleSelect.value;
                    allRoleFields.forEach((field) => {
                        const targetRole = field.getAttribute('data-role-field');
                        const isVisible = targetRole === role;
                        field.classList.toggle('hidden', !isVisible);
                        field.disabled = !isVisible;
                    });
                };

                roleSelect.addEventListener('change', syncRoleFields);
                syncRoleFields();

                // Keep edit form immediately visible after selecting Edit.
                if (editSection) {
                    editSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        </script>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('confirm-delete-modal');
            const titleEl = document.getElementById('confirm-delete-title');
            const messageEl = document.getElementById('confirm-delete-message');
            const cancelBtn = document.getElementById('confirm-delete-cancel');
            const submitBtn = document.getElementById('confirm-delete-submit');
            let activeForm = null;

            if (!modal || !titleEl || !messageEl || !cancelBtn || !submitBtn) return;

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                activeForm = null;
            };

            document.querySelectorAll('form[data-confirm-delete]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    activeForm = form;
                    titleEl.textContent = form.getAttribute('data-confirm-title') || 'Set account to inactive?';
                    messageEl.textContent = form.getAttribute('data-confirm-message') || 'The user will no longer be able to login until reactivated by admin.';
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                });
            });

            cancelBtn.addEventListener('click', closeModal);
            submitBtn.addEventListener('click', () => {
                if (!activeForm) return;
                activeForm.submit();
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal) closeModal();
            });
        });
    </script>
</x-app-layout>
