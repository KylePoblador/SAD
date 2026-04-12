<x-layouts.student title="Profile" active="profile">
    <h2 class="text-base font-bold text-gray-800">Profile</h2>

    <div class="flex flex-col items-center rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        @php
            $nameParts = explode(' ', Auth::user()->name);
            $initials =
                strtoupper(substr($nameParts[0], 0, 1)) .
                (isset($nameParts[1]) ? strtoupper(substr($nameParts[1], 0, 1)) : '');
        @endphp
        @if (Auth::user()->avatarPublicUrl())
            <img src="{{ Auth::user()->avatarPublicUrl() }}" alt=""
                class="mb-3 h-16 w-16 rounded-full object-cover ring-2 ring-green-100" />
        @else
            <div
                class="mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-green-600 text-xl font-bold text-white">
                {{ $initials }}
            </div>
        @endif
        <p class="text-base font-bold text-gray-800">{{ Auth::user()->name }}</p>
        <p class="text-xs text-gray-400">{{ Auth::user()->email }}</p>
        @if (Auth::user()->college && isset($canteenCatalog[Auth::user()->college]))
            <p class="mt-2 text-xs text-gray-500">{{ $canteenCatalog[Auth::user()->college]['label'] }}</p>
        @endif
    </div>

    <div class="space-y-3 rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Account</p>

        @if (session('status') === 'profile-updated')
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-2">
                <p class="text-xs font-semibold text-green-700">Profile updated successfully.</p>
            </div>
        @endif

        <form method="POST" action="{{ route('student.profile.update') }}" enctype="multipart/form-data"
            class="space-y-3">
            @csrf
            @method('PATCH')

            <div>
                <label class="mb-1 block text-xs text-gray-500">Profile photo</label>
                <input type="file" name="avatar" accept="image/*"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-green-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-green-700" />
                @error('avatar')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-[11px] text-gray-400">Select Photo</p>
            </div>

            <div>
                <label class="mb-1 block text-xs text-gray-500">Full name</label>
                <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" required
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
            </div>

            <div>
                <label class="mb-1 block text-xs text-gray-500">Email</label>
                <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" required
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
            </div>

            <div>
                <label class="mb-1 block text-xs text-gray-500">College (optional)</label>
                <select name="college"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500">
                    <option value="">— Not set —</option>
                    @foreach ($canteenCatalog as $code => $info)
                        <option value="{{ $code }}" @selected(old('college', Auth::user()->college) === $code)>
                            {{ $info['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                class="w-full rounded-xl bg-green-600 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                Save changes
            </button>
        </form>

    </div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
            class="w-full rounded-xl bg-red-50 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-100">
            Log out
        </button>
    </form>

    @push('scripts')
        <script>
            const unreadBadge = document.getElementById('unread-badge');
            const unreadCountEndpoint = @json(route('student.unread-count'));
            async function updateUnreadCount() {
                if (!unreadBadge) return;
                try {
                    const r = await fetch(unreadCountEndpoint, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const d = await r.json();
                    const c = d.unread_count || 0;
                    if (c > 0) {
                        unreadBadge.textContent = c > 99 ? '99+' : String(c);
                        unreadBadge.style.display = 'flex';
                    } else {
                        unreadBadge.style.display = 'none';
                    }
                } catch (e) {}
            }
            updateUnreadCount();
        </script>
    @endpush
</x-layouts.student>
