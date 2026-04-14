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
                    autocomplete="username"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                <p id="email-hint-student" class="mt-1 text-[11px] text-gray-500 {{ $isStaff ? 'hidden' : '' }}">
                    Students: must be <strong>@usm.edu.ph</strong> (official USM email).
                </p>
                <p id="email-hint-staff" class="mt-1 text-[11px] text-gray-500 {{ $isStaff ? '' : 'hidden' }}">
                    Staff: any valid email is allowed.
                </p>
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <div data-student-only class="space-y-4 {{ $isStaff ? 'hidden' : '' }}">
                <div>
                    <label for="phone" class="mb-1 block text-xs font-semibold text-gray-700">Phone number *</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}"
                        autocomplete="tel" placeholder="e.g. 09XX XXX XXXX"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                    <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                </div>
                <div>
                    <label for="student_id" class="mb-1 block text-xs font-semibold text-gray-700">Student ID *</label>
                    <input id="student_id" name="student_id" type="text" value="{{ old('student_id') }}"
                        autocomplete="off" placeholder="Your USM student ID"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                    <x-input-error :messages="$errors->get('student_id')" class="mt-1" />
                </div>
            </div>

            <div data-staff-only class="space-y-4 {{ $isStaff ? '' : 'hidden' }}">
                <div>
                    <label for="canteen_name" class="mb-1 block text-xs font-semibold text-gray-700">Canteen name *</label>
                    <input id="canteen_name" name="canteen_name" type="text" value="{{ old('canteen_name') }}"
                        autocomplete="organization" placeholder="e.g. CHEFS Main Dining"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                    <x-input-error :messages="$errors->get('canteen_name')" class="mt-1" />
                </div>
            </div>

            <div>
                <label for="college" class="mb-1 block text-xs font-semibold text-gray-700">Select college / canteen *</label>
                <select id="college" name="college" required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500">
                    <option value="">Choose your college</option>
                    <option value="ceit" {{ old('college') === 'ceit' ? 'selected' : '' }}>CEIT - College of Engineering and Information Technology</option>
                    <option value="cass" {{ old('college') === 'cass' ? 'selected' : '' }}>CASS - College of Arts and Social Sciences</option>
                    <option value="chefs" {{ old('college') === 'chefs' ? 'selected' : '' }}>CHEFS - College of Human Ecology and Food Sciences</option>
                    <option value="cti" {{ old('college') === 'cti' ? 'selected' : '' }}>CTI - College of Trade and Industry</option>
                    <option value="cbdem" {{ old('college') === 'cbdem' ? 'selected' : '' }}>CBDEM - College of Business, Development, Economics, and Management</option>
                    <option value="ced" {{ old('college') === 'ced' ? 'selected' : '' }}>CED - College of Education</option>
                    <option value="chk" {{ old('college') === 'chk' ? 'selected' : '' }}>CHK - College of Human Kinetic</option>
                    <option value="imeas" {{ old('college') === 'imeas' ? 'selected' : '' }}>IMEAS - Institute of Middle East and Asian Studies</option>
                    <option value="ca" {{ old('college') === 'ca' ? 'selected' : '' }}>CA - College of Agriculture</option>
                    <option value="csm" {{ old('college') === 'csm' ? 'selected' : '' }}>CSM - College of Science and Mathematics</option>
                    <option value="chs" {{ old('college') === 'chs' ? 'selected' : '' }}>CHS - College of Health Sciences</option>
                    <option value="cvm" {{ old('college') === 'cvm' ? 'selected' : '' }}>CVM - College of Veterinary Medicine</option>
                </select>
                <x-input-error :messages="$errors->get('college')" class="mt-1" />
            </div>

            <p class="text-xs text-gray-500">Students: choose your college. Canteen staff: choose the college your canteen serves.</p>
            <p data-staff-only class="text-xs font-medium text-amber-800 {{ $isStaff ? '' : 'hidden' }}">Only one canteen staff account is allowed per college. If your canteen already has staff, contact the administrator.</p>

            <div>
                <label for="password" class="mb-1 block text-xs font-semibold text-gray-700">Password *</label>
                <div class="relative">
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                        placeholder="Minimum 8 chars, 1 uppercase, 1 lowercase, 1 number"
                        class="w-full rounded-md border border-gray-300 py-2 pl-3 pr-11 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                    <button type="button" data-password-toggle="password"
                        class="absolute right-1 top-1/2 -translate-y-1/2 rounded-md p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                        aria-label="Show password">
                        <svg class="pw-eye h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg class="pw-eye-off hidden h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-xs font-semibold text-gray-700">Confirm
                    Password *</label>
                <div class="relative">
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                        autocomplete="new-password" placeholder="Re-enter your password"
                        class="w-full rounded-md border border-gray-300 py-2 pl-3 pr-11 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                    <button type="button" data-password-toggle="password_confirmation"
                        class="absolute right-1 top-1/2 -translate-y-1/2 rounded-md p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                        aria-label="Show password">
                        <svg class="pw-eye h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg class="pw-eye-off hidden h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
            </div>

            <div>
                <label class="flex cursor-pointer items-start gap-2 text-xs text-gray-600">
                    <input type="checkbox" name="terms_accepted" value="1" id="terms_accepted" required
                        @checked(old('terms_accepted'))
                        class="mt-0.5 rounded border-gray-300 text-green-600 focus:ring-green-500" />
                    <span>
                        I have read and agree to the
                        <button type="button" id="btn-open-terms-modal"
                            class="font-semibold text-green-700 underline decoration-green-600/60 underline-offset-2 hover:text-green-800">
                            Terms &amp; Conditions
                        </button>
                        and
                        <button type="button" id="btn-open-terms-modal-privacy"
                            class="font-semibold text-green-700 underline decoration-green-600/60 underline-offset-2 hover:text-green-800">
                            Privacy Policy
                        </button>
                        <span class="text-red-600">*</span>
                    </span>
                </label>
                <x-input-error :messages="$errors->get('terms_accepted')" class="mt-1" />
            </div>

            <button type="submit" id="submitBtn"
                class="w-full rounded-md bg-green-600 px-4 py-3 text-sm font-semibold text-white shadow hover:bg-green-700">
                Register as {{ $isStaff ? 'Canteen Staff' : 'Student' }}
            </button>

            <p class="pt-2 text-center text-sm text-gray-600">
                Already have an account?
                <a href="{{ url('/') }}" class="font-semibold text-green-700 hover:underline">Back to Home</a>
            </p>
        </form>
    </div>

    {{-- Terms & Privacy: slide-in panel from the right --}}
    <div id="terms-modal" role="dialog" aria-modal="true" aria-labelledby="terms-modal-title"
        class="fixed inset-0 z-[70] opacity-0 pointer-events-none transition-opacity duration-200">
        <div id="terms-backdrop" class="absolute inset-0 bg-black/45 backdrop-blur-[1px]"></div>
        <div id="terms-panel"
            class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col bg-white shadow-2xl transition-transform duration-300 ease-out translate-x-full">
            <div class="flex shrink-0 items-center justify-between border-b border-gray-200 bg-green-700 px-4 py-3 text-white">
                <h2 id="terms-modal-title" class="text-base font-bold">Terms &amp; Privacy</h2>
                <button type="button" id="btn-close-terms-modal"
                    class="rounded-lg p-2 text-white/90 transition hover:bg-white/10 hover:text-white"
                    aria-label="Close">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4 text-sm leading-relaxed text-gray-700">
                <section id="terms-section" class="scroll-mt-4">
                    <h3 class="mb-2 text-xs font-bold uppercase tracking-wide text-green-800">Terms &amp; Conditions
                    </h3>
                    <p class="mb-3 text-xs text-gray-600">Last updated: {{ now()->format('F j, Y') }}. CoinMeal / USM
                        Canteen System — educational project use.</p>
                    <ol class="list-decimal space-y-2 pl-4 text-xs">
                        <li><strong>Account.</strong> You must provide accurate information. You are responsible for
                            keeping your login credentials secure.</li>
                        <li><strong>Use of service.</strong> The platform is for ordering food, wallet top-ups as
                            configured by your institution, and related canteen features. Misuse, fraud, or harassment
                            may result in suspension.</li>
                        <li><strong>Orders &amp; payments.</strong> Order totals and availability follow what the
                            canteen staff publish. Refunds or disputes follow campus / canteen policy.</li>
                        <li><strong>Content.</strong> You may not upload unlawful, offensive, or misleading content
                            (including profile or menu images).</li>
                        <li><strong>Changes.</strong> We may update these terms; continued use after changes means you
                            accept the updated terms.</li>
                    </ol>
                </section>
                <hr class="my-5 border-gray-200">
                <section id="privacy-section" class="scroll-mt-4">
                    <h3 class="mb-2 text-xs font-bold uppercase tracking-wide text-green-800">Privacy Policy</h3>
                    <ol class="list-decimal space-y-2 pl-4 text-xs">
                        <li><strong>Data we collect.</strong> Name, email, college/canteen affiliation, account and
                            order-related data needed to run the service.</li>
                        <li><strong>How we use it.</strong> To authenticate you, process orders, show balances, and
                            operate canteen workflows (staff dashboards, seating, etc.).</li>
                        <li><strong>Storage.</strong> Data is stored according to your deployment (e.g. school server).
                            Protect your password and log out on shared devices.</li>
                        <li><strong>Sharing.</strong> We do not sell your data. Limited data may be visible to canteen
                            staff as needed to fulfill orders.</li>
                        <li><strong>Your rights.</strong> You may request corrections or account closure through your
                            institution’s administrator where applicable.</li>
                    </ol>
                </section>
            </div>
            <div class="shrink-0 border-t border-gray-100 p-4">
                <button type="button" id="btn-close-terms-modal-bottom"
                    class="w-full rounded-lg bg-green-600 py-2.5 text-sm font-semibold text-white shadow hover:bg-green-700">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        const studentTab = document.getElementById('studentTab');
        const staffTab = document.getElementById('staffTab');
        const roleInput = document.getElementById('roleInput');
        const submitBtn = document.getElementById('submitBtn');

        function syncRoleFields(role) {
            var staff = role === 'staff';
            var hintStu = document.getElementById('email-hint-student');
            var hintStaff = document.getElementById('email-hint-staff');
            var emailInp = document.getElementById('email');
            if (hintStu) hintStu.classList.toggle('hidden', staff);
            if (hintStaff) hintStaff.classList.toggle('hidden', !staff);
            if (emailInp) {
                emailInp.placeholder = staff ? 'you@example.com' : 'name.lastname@usm.edu.ph';
            }
            document.querySelectorAll('[data-student-only]').forEach(function(wrap) {
                wrap.classList.toggle('hidden', staff);
                wrap.querySelectorAll('input').forEach(function(inp) {
                    inp.disabled = staff;
                    if (!staff) {
                        inp.setAttribute('required', 'required');
                    } else {
                        inp.removeAttribute('required');
                    }
                });
            });
            document.querySelectorAll('[data-staff-only]').forEach(function(wrap) {
                wrap.classList.toggle('hidden', !staff);
                wrap.querySelectorAll('input').forEach(function(inp) {
                    inp.disabled = !staff;
                    if (staff) {
                        inp.setAttribute('required', 'required');
                    } else {
                        inp.removeAttribute('required');
                    }
                });
            });
        }

        function setRole(role) {
            const studentActive = role === 'student';
            roleInput.value = role;
            studentTab.className = studentActive ?
                'rounded-md bg-orange-500 px-3 py-2 text-sm font-semibold text-white transition' :
                'rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-600 transition';
            staffTab.className = !studentActive ?
                'rounded-md bg-orange-500 px-3 py-2 text-sm font-semibold text-white transition' :
                'rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-600 transition';
            submitBtn.textContent = studentActive ? 'Register as Student' : 'Register as Canteen Staff';
            syncRoleFields(role);
        }

        studentTab.addEventListener('click', () => setRole('student'));
        staffTab.addEventListener('click', () => setRole('staff'));
        setRole(roleInput.value === 'staff' ? 'staff' : 'student');

        document.querySelectorAll('[data-password-toggle]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = btn.getAttribute('data-password-toggle');
                var input = document.getElementById(id);
                if (!input) return;
                var eye = btn.querySelector('.pw-eye');
                var eyeOff = btn.querySelector('.pw-eye-off');
                if (input.type === 'password') {
                    input.type = 'text';
                    eye && eye.classList.add('hidden');
                    eyeOff && eyeOff.classList.remove('hidden');
                    btn.setAttribute('aria-label', 'Hide password');
                } else {
                    input.type = 'password';
                    eye && eye.classList.remove('hidden');
                    eyeOff && eyeOff.classList.add('hidden');
                    btn.setAttribute('aria-label', 'Show password');
                }
            });
        });

        (function() {
            var modal = document.getElementById('terms-modal');
            var panel = document.getElementById('terms-panel');
            var backdrop = document.getElementById('terms-backdrop');
            var openTerms = document.getElementById('btn-open-terms-modal');
            var openPrivacy = document.getElementById('btn-open-terms-modal-privacy');
            var closeTop = document.getElementById('btn-close-terms-modal');
            var closeBottom = document.getElementById('btn-close-terms-modal-bottom');
            if (!modal || !panel) return;

            function openModal(scrollToId) {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                modal.classList.add('opacity-100', 'pointer-events-auto');
                panel.classList.remove('translate-x-full');
                panel.classList.add('translate-x-0');
                document.documentElement.classList.add('overflow-hidden');
                if (scrollToId) {
                    setTimeout(function() {
                        var el = document.getElementById(scrollToId);
                        if (el) el.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }, 250);
                }
            }

            function closeModal() {
                modal.classList.add('opacity-0', 'pointer-events-none');
                modal.classList.remove('opacity-100', 'pointer-events-auto');
                panel.classList.add('translate-x-full');
                panel.classList.remove('translate-x-0');
                document.documentElement.classList.remove('overflow-hidden');
            }

            openTerms && openTerms.addEventListener('click', function() {
                openModal('terms-section');
            });
            openPrivacy && openPrivacy.addEventListener('click', function() {
                openModal('privacy-section');
            });
            closeTop && closeTop.addEventListener('click', closeModal);
            closeBottom && closeBottom.addEventListener('click', closeModal);
            backdrop && backdrop.addEventListener('click', closeModal);
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('pointer-events-auto')) closeModal();
            });
        })();
    </script>
</body>

</html>
