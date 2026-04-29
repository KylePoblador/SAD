<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): View
    {
        $role = $request->query('role');

        if ($role !== null) {
            $request->validate([
                'role' => ['string', Rule::in(['student', 'staff', 'admin'])],
            ]);
        }

        return view('auth.login', [
            'selectedRole' => $role,
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $request->session()->put('selected_role', Auth::user()->role);
        if (Schema::hasColumn('users', 'last_login_at')) {
            Auth::user()->forceFill([
                'last_login_at' => now(),
            ])->save();
        }

        $role = Auth::user()->role;

        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($role === 'staff') {
            return redirect()->route('dashboard');
        } else {
            return redirect()->route('student.dashboard');
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}