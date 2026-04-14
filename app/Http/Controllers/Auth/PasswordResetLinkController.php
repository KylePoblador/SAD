<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $email = strtolower(trim((string) $request->input('email')));
        User::query()->where('email', $email)->firstOrFail();

        $request->session()->put('password_reset_verified_email', $email);

        return redirect()
            ->route('password.reset', ['email' => $email])
            ->with('status', 'Email verified. You can now change your password.');
    }
}
