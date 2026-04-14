<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        $email = strtolower(trim((string) $request->query('email', '')));
        if (session('password_reset_verified_email') !== $email) {
            return view('auth.forgot-password')->withErrors([
                'email' => 'Verify your email first using Forgot Password.',
            ]);
        }

        return view('auth.reset-password', ['email' => $email]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = strtolower(trim((string) $request->input('email')));
        $user = User::query()->where('email', $email)->first();
        if (! $user || session('password_reset_verified_email') !== $email) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Verify your email first using Forgot Password.']);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        $request->session()->forget('password_reset_verified_email');
        event(new PasswordReset($user));

        return redirect()->route('login')->with('status', 'Password reset successful. You can now log in.');
    }
}
