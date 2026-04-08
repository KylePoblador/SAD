<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>USM Canteen System - Register</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>

<body class="min-h-screen bg-lime-50 font-sans text-gray-800 py-6 px-3">
    @php
        $selectedRole = old('role', request('role', 'student'));
        $isStaff = $selectedRole === 'staff';
    @endphp

    <div class="mx-auto w-full max-w-md overflow-hidden rounded-xl border border-gray-200 bg-white shadow-md">
        <div class="relative bg-green-700 px-6 py-5 text-center text-white">
            <h1 class="text-3xl font-bold leading-tight">USM Canteen System</h1>
            <p class="mt-1 text-sm text-green-100">Registration Portal</p>
        </div>

        <div class="bg-stone-100 px-6 py-3">
            <div class="mx-auto grid w-full max-w-xs grid-cols-2 gap-2 rounded-md bg-gray-200 p-1">
                <button type="button" id="studentTab"
                    class="rounded-md px-3 py-2 text-sm font-semibold transition">Student</button>
                <button type="button" id="staffTab"
                    class="rounded-md px-3 py-2 text-sm font-semibold transition">Canteen Staff</button>
            </div>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4 px-6 py-5" id="registerForm">
            @csrf
            <input type="hidden" name="role" id="roleInput" value="{{ $selectedRole }}">

            <div>
                <label for="name" class="mb-1 block text-xs font-semibold text-gray-700">Full Name *</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                    autocomplete="name" placeholder="Enter your full name"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>

            <div>
                <label for="email" class="mb-1 block text-xs font-semibold text-gray-700">Email Address *</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                    autocomplete="username" placeholder="your.email@example.com"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <div>
                <label for="phone" class="mb-1 block text-xs font-semibold text-gray-700">Phone Number *</label>
                <input id="phone" type="text" placeholder="+63 912 345 6789"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
            </div>

            <div>
                <label for="college" class="mb-1 block text-xs font-semibold text-gray-700">Select College *</label>
                <select id="college"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-600 outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500">
                    <option>Choose your college</option>
                    <option>College of Engineering</option>
                    <option>College of Business</option>
                    <option>College of Arts and Sciences</option>
                </select>
            </div>

            <div id="studentFields" class="space-y-4">
                <div>
                    <label for="student_id" class="mb-1 block text-xs font-semibold text-gray-700">Student ID *</label>
                    <input id="student_id" type="text" placeholder="e.g. 23-05647"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                </div>
            </div>

            <div id="staffFields" class="hidden space-y-4">
                <div>
                    <label for="canteen_name" class="mb-1 block text-xs font-semibold text-gray-700">Canteen Name
                        *</label>
                    <input id="canteen_name" type="text" placeholder="e.g. Main Canteen"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                </div>
                <div>
                    <label for="canteen_location" class="mb-1 block text-xs font-semibold text-gray-700">Canteen
                        Location *</label>
                    <input id="canteen_location" type="text" placeholder="e.g. Near CECM Building"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                </div>
                <div>
                    <label for="permit_no" class="mb-1 block text-xs font-semibold text-gray-700">Business License
                        Number *</label>
                    <input id="permit_no" type="text" placeholder="Enter license number"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                </div>
            </div>

            <div>
                <label for="password" class="mb-1 block text-xs font-semibold text-gray-700">Password *</label>
                <input id="password" name="password" type="password" required autocomplete="new-password"
                    placeholder="Minimum 8 chars, 1 uppercase, 1 lowercase, 1 number"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-xs font-semibold text-gray-700">Confirm
                    Password *</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                    autocomplete="new-password" placeholder="Re-enter your password"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
            </div>

            <label class="flex items-start gap-2 text-xs text-gray-600">
                <input type="checkbox" class="mt-0.5 rounded border-gray-300 text-green-600 focus:ring-green-500" />
                <span>I agree to the Terms and Conditions and Privacy Policy</span>
            </label>

            <button type="submit" id="submitBtn"
                class="w-full rounded-md bg-green-600 px-4 py-3 text-sm font-semibold text-white shadow hover:bg-green-700">
                Register as {{ $isStaff ? 'Canteen Staff' : 'Student' }}
            </button>

            <p class="pt-2 text-center text-sm text-gray-600">
                Already have an account?
                <a href="{{ route('welcome') }}" class="font-semibold text-green-700 hover:underline">Back to Home</a>
            </p>
        </form>
    </div>

    <script>
        const studentTab = document.getElementById('studentTab');
        const staffTab = document.getElementById('staffTab');
        const studentFields = document.getElementById('studentFields');
        const staffFields = document.getElementById('staffFields');
        const roleInput = document.getElementById('roleInput');
        const submitBtn = document.getElementById('submitBtn');

        function setRole(role) {
            const studentActive = role === 'student';
            roleInput.value = role;
            studentFields.classList.toggle('hidden', !studentActive);
            staffFields.classList.toggle('hidden', studentActive);
            studentTab.className = studentActive ?
                'rounded-md bg-orange-500 px-3 py-2 text-sm font-semibold text-white transition' :
                'rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-600 transition';
            staffTab.className = !studentActive ?
                'rounded-md bg-orange-500 px-3 py-2 text-sm font-semibold text-white transition' :
                'rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-600 transition';
            submitBtn.textContent = studentActive ? 'Register as Student' : 'Register as Canteen Staff';
        }

        studentTab.addEventListener('click', () => setRole('student'));
        staffTab.addEventListener('click', () => setRole('staff'));
        setRole(roleInput.value === 'staff' ? 'staff' : 'student');
    </script>
</body>

</html>
