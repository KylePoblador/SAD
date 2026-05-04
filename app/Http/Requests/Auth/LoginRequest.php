<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'role' => ['nullable', 'string', 'in:student,staff,admin'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = [
            'email' => $this->input('email'),
            'password' => $this->input('password'),
        ];

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Invalid credentials for the selected role.',
            ]);
        }

        if ($this->filled('role') && Auth::user()->role !== $this->input('role')) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Invalid credentials for the selected role.',
            ]);
        }

        $user = Auth::user();
        $isManagedRole = in_array((string) $user->role, ['student', 'staff'], true);
        $isStale = optional($user->updated_at)->lt(now()->subMonths(6));
        if ($isManagedRole && $isStale && (string) ($user->status ?? 'active') !== 'inactive') {
            $user->forceFill(['status' => 'inactive'])->saveQuietly();
        }
        $isInactive = (string) ($user->status ?? 'active') === 'inactive' || $isStale;
        if ($isManagedRole && $isInactive) {
            Auth::logout();
            RateLimiter::clear($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Your account is inactive due to 6+ months without activity. Please contact admin to restore access.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
