@extends('admin.layout')

@section('title', 'Users')
@section('heading', 'Users')

@section('content')
    <div class="mb-4 rounded-xl bg-white p-4 shadow-sm">
        <form method="get" class="flex flex-wrap items-end gap-3">
            <div class="min-w-[200px] flex-1">
                <label class="mb-1 block text-xs font-semibold text-gray-600">Search</label>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name, email, student ID, phone"
                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-600">Role</label>
                <select name="role" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="student" @selected(($filters['role'] ?? '') === 'student')>Student</option>
                    <option value="staff" @selected(($filters['role'] ?? '') === 'staff')>Staff</option>
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Search</button>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">College</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                        <td class="px-4 py-3"><span class="rounded bg-gray-100 px-2 py-0.5 text-xs uppercase">{{ $user->role }}</span></td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->college ? strtoupper($user->college) : '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.users.show', $user) }}" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">View details</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-gray-100 p-4">{{ $users->links() }}</div>
    </div>
@endsection
