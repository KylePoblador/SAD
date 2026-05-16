<x-layouts.student title="Friends & Connections" active="wallet">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Friends</h1>
        <a href="{{ route('student.wallet') }}"
            class="rounded-xl bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-200">
            Back to Wallet
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-900 shadow-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-900 shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        <!-- Add Friend Section -->
        <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
            <h2 class="mb-1 text-lg font-bold text-indigo-900">Add a Friend</h2>
            <p class="mb-4 text-xs text-indigo-900/80">Search students to add them to your contacts.</p>
            
            <div class="space-y-3">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <input id="friend-search" type="text" placeholder="Search by name, email, or student ID..."
                        class="w-full rounded-xl border border-indigo-200 pl-9 pr-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" autocomplete="off">
                    <div id="search-spinner" class="absolute right-3 top-3 hidden">
                        <svg class="h-5 w-5 animate-spin text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
                
                <div id="search-results" class="hidden max-h-60 overflow-y-auto rounded-xl border border-indigo-100 bg-white shadow-sm">
                    <!-- Results will be injected here -->
                </div>
            </div>
        </div>

        <!-- Pending Requests -->
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <h2 class="mb-1 text-lg font-bold text-amber-900">Friend Requests</h2>
            <p class="mb-4 text-xs text-amber-900/80">Accept requests to send and receive coins.</p>
            
            @if($pendingReceived->isEmpty())
                <div class="rounded-xl border border-amber-100 bg-white/60 p-6 text-center">
                    <p class="text-sm text-amber-800/60">No pending requests.</p>
                </div>
            @endif

            <div class="space-y-3">
                @foreach($pendingReceived as $req)
                    <div class="flex items-center justify-between rounded-xl border border-amber-100 bg-white p-3 shadow-sm">
                        <div class="flex items-center gap-3">
                            <img src="{{ $req->user->avatarPublicUrl() ?? 'https://ui-avatars.com/api/?name='.urlencode($req->user->name) }}" class="h-10 w-10 rounded-full object-cover shadow-sm">
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ $req->user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $req->user->email }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <form method="post" action="{{ route('student.friends.accept', $req) }}">
                                @csrf
                                <button type="submit" class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-green-700">Accept</button>
                            </form>
                            <form method="post" action="{{ route('student.friends.reject', $req) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-bold text-gray-700 transition hover:bg-gray-50">Decline</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- Sent Requests -->
    @if($pendingSent->isNotEmpty())
        <div class="mt-6 rounded-2xl border border-gray-200 bg-gray-50 p-5 shadow-sm">
            <h2 class="mb-4 text-sm font-bold text-gray-700">Sent Requests</h2>
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
                @foreach($pendingSent as $req)
                    <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <img src="{{ $req->friend->avatarPublicUrl() ?? 'https://ui-avatars.com/api/?name='.urlencode($req->friend->name) }}" class="h-8 w-8 rounded-full object-cover shadow-sm grayscale-[30%]">
                            <div class="overflow-hidden">
                                <p class="truncate text-sm font-bold text-gray-900">{{ $req->friend->name }}</p>
                                <p class="truncate text-xs text-amber-600">Pending</p>
                            </div>
                        </div>
                        <form method="post" action="{{ route('student.friends.reject', $req) }}" class="ml-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg bg-gray-100 px-2 py-1 text-xs font-bold text-gray-600 transition hover:bg-gray-200">Cancel</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Friends List -->
    <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-lg font-bold text-gray-900">My Friends ({{ $friends->count() }})</h2>
        
        @if($friends->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="mb-3 h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <p class="text-sm font-medium text-gray-900">No friends yet</p>
                <p class="mt-1 text-xs text-gray-500">Add friends to easily share coins.</p>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
                @foreach($friends as $friend)
                    <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50 p-3 shadow-sm group">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <img src="{{ $friend->avatarPublicUrl() ?? 'https://ui-avatars.com/api/?name='.urlencode($friend->name) }}" class="h-10 w-10 rounded-full object-cover shadow-sm ring-2 ring-white">
                            <div class="overflow-hidden">
                                <p class="truncate text-sm font-bold text-gray-900">{{ $friend->name }}</p>
                                <p class="truncate text-xs text-gray-500">{{ $friend->student_id ?: $friend->email }}</p>
                            </div>
                        </div>
                        <form method="post" action="{{ route('student.friends.remove', $friend) }}" class="ml-2">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="rounded-lg bg-red-50 px-2 py-1.5 text-xs font-bold text-red-600 transition hover:bg-red-100" onclick="confirmUnfriend(this.form, '{{ addslashes($friend->name) }}')">
                                Unfriend
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            async function confirmUnfriend(form, name) {
                const ok = await CoinmealDialog.confirm({
                    title: 'Unfriend student?',
                    message: `Are you sure you want to remove ${name} from your friends list?`,
                    variant: 'danger',
                    confirmLabel: 'Unfriend',
                    cancelLabel: 'Cancel',
                });
                if (ok) {
                    form.submit();
                }
            }

            (function() {
                const searchInput = document.getElementById('friend-search');
                const resultsContainer = document.getElementById('search-results');
                const spinner = document.getElementById('search-spinner');
                const endpoint = @json(route('student.friends.search'));
                const addEndpoint = @json(route('student.friends.add'));
                const csrfToken = '{{ csrf_token() }}';
                
                if (!searchInput || !resultsContainer || !endpoint) return;
                
                function clearResults() {
                    resultsContainer.innerHTML = '';
                    resultsContainer.classList.add('hidden');
                }

                let timer = null;
                searchInput.addEventListener('input', function() {
                    clearTimeout(timer);
                    const q = searchInput.value.trim();
                    
                    if (q.length < 2) {
                        spinner.classList.add('hidden');
                        clearResults();
                        return;
                    }
                    
                    spinner.classList.remove('hidden');
                    
                    timer = setTimeout(async function() {
                        try {
                            const res = await fetch(endpoint + '?q=' + encodeURIComponent(q), {
                                headers: {
                                    Accept: 'application/json'
                                }
                            });
                            const data = await res.json();
                            
                            spinner.classList.add('hidden');
                            clearResults();
                            resultsContainer.classList.remove('hidden');
                            
                            if (data.items.length === 0) {
                                resultsContainer.innerHTML = '<div class="p-4 text-center text-sm text-gray-500">No students found</div>';
                                return;
                            }
                            
                            data.items.forEach(function(u) {
                                const item = document.createElement('div');
                                item.className = 'flex items-center justify-between p-3 border-b border-indigo-50 last:border-0 hover:bg-indigo-50/50 transition';
                                
                                const info = document.createElement('div');
                                info.className = 'overflow-hidden';
                                info.innerHTML = `
                                    <p class="truncate text-sm font-bold text-gray-900">${u.name}</p>
                                    <p class="truncate text-xs text-gray-500">${u.email}</p>
                                `;
                                
                                const form = document.createElement('form');
                                form.method = 'post';
                                form.action = addEndpoint;
                                form.innerHTML = `
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <input type="hidden" name="friend_id" value="${u.id}">
                                    <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-indigo-700 active:scale-[0.99]">
                                        Add
                                    </button>
                                `;
                                
                                item.appendChild(info);
                                item.appendChild(form);
                                resultsContainer.appendChild(item);
                            });
                        } catch (e) {
                            spinner.classList.add('hidden');
                            clearResults();
                        }
                    }, 400);
                });
            })();
        </script>
    @endpush
</x-layouts.student>
