<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserCanteenBalance;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $collegeKeys = array_keys(config('canteens', []));

        $base = [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['required', 'in:student,staff'],
            'college' => ['required', 'string', Rule::in($collegeKeys)],
            'terms_accepted' => ['accepted'],
        ];

        $role = $request->input('role');

        if ($role === 'staff') {
            $request->validate(array_merge($base, [
                'college' => [
                    'required',
                    'string',
                    Rule::in($collegeKeys),
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        if ($this->staffCollegeIsTaken((string) $value)) {
                            $fail(__('This college / canteen already has a registered staff account. Only one canteen staff may register per college.'));
                        }
                    },
                ],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'canteen_name' => ['required', 'string', 'max:255'],
                'phone' => ['prohibited'],
                'student_id' => ['prohibited'],
            ]));
        } else {
            $request->validate(
                array_merge($base, [
                    'email' => [
                        'required',
                        'string',
                        'lowercase',
                        'email',
                        'max:255',
                        'unique:'.User::class,
                        'regex:/^[^@\s]+@usm\.edu\.ph$/',
                    ],
                    'phone' => ['required', 'string', 'max:32'],
                    'student_id' => ['required', 'string', 'max:64', 'unique:users,student_id'],
                    'canteen_name' => ['prohibited'],
                ]),
                [
                    'email.regex' => 'Use your official USM email address ending with @usm.edu.ph.',
                ]
            );
        }

        return DB::transaction(function () use ($request, $role) {
            if ($role === 'staff' && $this->staffCollegeIsTaken((string) $request->input('college'), lockForUpdate: true)) {
                throw ValidationException::withMessages([
                    'college' => __('This college / canteen already has a registered staff account. Only one canteen staff may register per college.'),
                ]);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $role,
                'college' => $request->input('college'),
                'wallet_balance' => 0,
                'phone' => $role === 'student' ? $request->input('phone') : null,
                'student_id' => $role === 'student' ? $request->input('student_id') : null,
                'canteen_name' => $role === 'staff' ? $request->input('canteen_name') : null,
            ]);

            event(new Registered($user));

            return redirect('/')
                ->with('status', 'Registration successful. Please log in to continue.');
        });
    }

    protected function staffCollegeIsTaken(string $college, bool $lockForUpdate = false): bool
    {
        $norm = UserCanteenBalance::normalizedCollege($college);

        $query = User::query()
            ->where('role', 'staff')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$norm]);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->exists();
    }
}
