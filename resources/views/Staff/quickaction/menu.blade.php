@php
    $menuFormHasErrors = $errors->has('name') || $errors->has('price') || $errors->has('photo') || $errors->has('category');
@endphp

<x-layouts.staff-subpage title="Menu management" subtitle="Items you add appear on the student canteen page">
    @if ($errors->any() && !$menuFormHasErrors)
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">Could not save changes</p>
            <ul class="mt-2 list-inside list-disc text-xs">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status') === 'menu-added')
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">Menu item added.
        </div>
    @endif
    @if (session('status') === 'menu-removed')
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">Menu item removed.
        </div>
    @endif
    @if (session('status') === 'menu-updated')
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">Availability updated.
        </div>
    @endif
    @if (session('status') === 'menu-edited')
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">Menu item updated.
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-base font-bold text-gray-900">Your menu <span
                class="text-sm font-semibold text-gray-500">({{ strtoupper($collegeCode) }})</span></p>
        <button type="button" id="btn-open-menu-add"
            class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add menu item
        </button>
    </div>

    @forelse ($menuItems as $item)
        <div
            class="flex flex-col gap-3 rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 flex-1 gap-3 sm:items-center">
                @if ($item->imagePublicUrl())
                    <img src="{{ $item->imagePublicUrl() }}" alt=""
                        class="h-14 w-14 shrink-0 rounded-lg object-cover ring-1 ring-gray-100" />
                @else
                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400">
                        —
                    </div>
                @endif
                <div class="min-w-0">
                    <p class="text-sm font-bold text-gray-900">{{ $item->name }}</p>
                    <p class="text-base font-bold text-green-600">₱{{ number_format($item->price, 2) }}</p>
                    <p class="text-xs text-gray-500">{{ $item->category }}</p>
                    <span
                        class="mt-1 inline-block rounded-md px-2 py-0.5 text-xs font-semibold {{ $item->is_available ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }}">
                        {{ $item->is_available ? 'Available' : 'Unavailable' }}
                    </span>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button"
                    class="btn-menu-edit rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-100"
                    data-id="{{ $item->id }}" data-name="{{ e($item->name) }}" data-price="{{ $item->price }}"
                    data-category="{{ e($item->category) }}" data-url="{{ route('staff.menu.update', $item) }}">
                    Edit
                </button>
                <form method="POST" action="{{ route('staff.menu.toggle', $item) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                        {{ $item->is_available ? 'Mark unavailable' : 'Mark available' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('staff.menu.destroy', $item) }}"
                    onsubmit="return confirm('Remove this item?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div
            class="rounded-2xl border border-dashed border-emerald-200 bg-emerald-50/40 px-6 py-12 text-center text-sm text-emerald-900/80">
            <p class="font-medium text-emerald-900">No products yet</p>
            <p class="mt-1 text-emerald-800/70">Tap <strong>Add menu item</strong> to create your first dish — it will
                show on the student canteen page.</p>
        </div>
    @endforelse

    {{-- Add item modal --}}
    <div id="menu-add-modal" role="dialog" aria-modal="true" aria-labelledby="menu-add-title"
        class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4 opacity-0 pointer-events-none transition-opacity duration-200 ease-out"
        data-open="{{ $menuFormHasErrors ? '1' : '0' }}">
        <div id="menu-add-backdrop" class="absolute inset-0 bg-black/50 backdrop-blur-[2px] transition-opacity"></div>
        <div id="menu-add-panel"
            class="relative z-10 flex max-h-[min(90vh,640px)] w-full max-w-lg flex-col rounded-t-2xl bg-white shadow-2xl transition-transform duration-300 ease-out sm:rounded-2xl translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0">
            <div class="flex shrink-0 items-center justify-between border-b border-gray-100 px-4 py-3 sm:px-5">
                <h2 id="menu-add-title" class="text-base font-bold text-gray-900">Add menu item</h2>
                <button type="button" id="btn-close-menu-add"
                    class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-800"
                    aria-label="Close">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4 sm:px-5 sm:py-5">
                <form id="staff-menu-item-form" method="POST" action="{{ route('staff.menu.store') }}"
                    enctype="multipart/form-data" class="space-y-3" novalidate>
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Price (₱)</label>
                        <input type="number" id="staff-menu-price-stepper" value="{{ old('price') }}" step="5"
                            min="0" inputmode="decimal" autocomplete="off"
                            aria-describedby="staff-menu-price-hint"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                        <input type="hidden" name="price" id="staff-menu-price-value" value="{{ old('price') }}" />
                        @error('price')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p id="staff-menu-price-hint" class="mt-1 text-[11px] text-gray-500">Arrow buttons change by
                            ₱5; you can still type any amount.</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Category</label>
                        <input type="text" name="category" value="{{ old('category', 'Meals') }}"
                            placeholder="e.g. Meals, Snacks, Beverages"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Product photo <span
                                class="font-normal text-red-600">*</span></label>
                        <input type="file" id="staff-menu-photo" name="photo" accept="image/*" required
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-green-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-green-700" />
                        @error('photo')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[11px] text-gray-500">Required. Max 3&nbsp;MB. Shown on the student menu.</p>
                    </div>
                    <div class="flex gap-2 pt-1">
                        <button type="button" id="btn-cancel-menu-add"
                            class="flex-1 rounded-xl border border-gray-200 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 rounded-xl bg-green-600 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700">
                            Add item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit item modal --}}
    <div id="menu-edit-modal" role="dialog" aria-modal="true" aria-labelledby="menu-edit-title"
        class="fixed inset-0 z-50 flex items-end justify-center opacity-0 pointer-events-none transition-opacity duration-200 ease-out sm:items-center sm:p-4">
        <div id="menu-edit-backdrop" class="absolute inset-0 bg-black/50 backdrop-blur-[2px] transition-opacity"></div>
        <div id="menu-edit-panel"
            class="relative z-10 flex max-h-[min(90vh,640px)] w-full max-w-lg flex-col rounded-t-2xl bg-white shadow-2xl transition-transform duration-300 ease-out sm:rounded-2xl translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0">
            <div class="flex shrink-0 items-center justify-between border-b border-gray-100 px-4 py-3 sm:px-5">
                <h2 id="menu-edit-title" class="text-base font-bold text-gray-900">Edit menu item</h2>
                <button type="button" id="btn-close-menu-edit"
                    class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-800"
                    aria-label="Close">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4 sm:px-5 sm:py-5">
                <form id="staff-menu-edit-form" method="POST" action="" enctype="multipart/form-data" class="space-y-3"
                    novalidate>
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Name</label>
                        <input type="text" name="name" id="staff-menu-edit-name" required
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Price (₱)</label>
                        <input type="number" id="staff-menu-edit-price-stepper" step="0.01" min="0" inputmode="decimal"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                        <input type="hidden" name="price" id="staff-menu-edit-price-value" />
                        @error('price')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Category</label>
                        <input type="text" name="category" id="staff-menu-edit-category"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Replace photo <span
                                class="font-normal text-gray-500">(optional)</span></label>
                        <input type="file" id="staff-menu-edit-photo" name="photo" accept="image/*"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-green-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-green-700" />
                        @error('photo')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex gap-2 pt-1">
                        <button type="button" id="btn-cancel-menu-edit"
                            class="flex-1 rounded-xl border border-gray-200 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 rounded-xl bg-green-600 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700">
                            Save changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var modal = document.getElementById('menu-add-modal');
            var panel = document.getElementById('menu-add-panel');
            var backdrop = document.getElementById('menu-add-backdrop');
            var openBtn = document.getElementById('btn-open-menu-add');
            var closeBtn = document.getElementById('btn-close-menu-add');
            var cancelBtn = document.getElementById('btn-cancel-menu-add');
            var form = document.getElementById('staff-menu-item-form');
            var ui = document.getElementById('staff-menu-price-stepper');
            var real = document.getElementById('staff-menu-price-value');
            if (!modal || !panel) return;

            function openModal() {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                modal.classList.add('opacity-100', 'pointer-events-auto');
                panel.classList.remove('translate-y-full', 'sm:scale-95', 'sm:opacity-0');
                panel.classList.add('translate-y-0', 'sm:scale-100', 'sm:opacity-100');
                document.documentElement.classList.add('overflow-hidden');
                setTimeout(function() {
                    var first = form && form.querySelector('input[name="name"]');
                    if (first) first.focus();
                }, 200);
            }

            function closeModal() {
                modal.classList.add('opacity-0', 'pointer-events-none');
                modal.classList.remove('opacity-100', 'pointer-events-auto');
                panel.classList.add('translate-y-full', 'sm:scale-95', 'sm:opacity-0');
                panel.classList.remove('translate-y-0', 'sm:scale-100', 'sm:opacity-100');
                document.documentElement.classList.remove('overflow-hidden');
            }

            if (modal.getAttribute('data-open') === '1') {
                requestAnimationFrame(function() {
                    openModal();
                });
            }

            openBtn && openBtn.addEventListener('click', openModal);
            closeBtn && closeBtn.addEventListener('click', closeModal);
            cancelBtn && cancelBtn.addEventListener('click', closeModal);
            backdrop && backdrop.addEventListener('click', closeModal);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('pointer-events-auto')) {
                    closeModal();
                }
            });

            if (!form || !ui || !real) return;

            function syncPrice() {
                real.value = ui.value.trim() === '' ? '' : ui.value;
            }
            ui.addEventListener('input', syncPrice);
            ui.addEventListener('change', syncPrice);
            syncPrice();

            form.addEventListener('submit', function(e) {
                syncPrice();
                var nameInput = form.querySelector('input[name="name"]');
                var photoInput = document.getElementById('staff-menu-photo');
                var v = real.value.trim();
                if (!nameInput || !String(nameInput.value).trim()) {
                    e.preventDefault();
                    if (nameInput) nameInput.reportValidity();
                    return;
                }
                if (v === '' || isNaN(Number(v)) || Number(v) < 0) {
                    e.preventDefault();
                    ui.setCustomValidity('Enter a valid price (₱0 or more).');
                    ui.reportValidity();
                    ui.setCustomValidity('');
                    return;
                }
                if (photoInput && (!photoInput.files || !photoInput.files.length)) {
                    e.preventDefault();
                    photoInput.setCustomValidity('Please choose a product photo.');
                    photoInput.reportValidity();
                    photoInput.setCustomValidity('');
                }
            });
        })();
    </script>
    <script>
        (function() {
            var modal = document.getElementById('menu-edit-modal');
            var panel = document.getElementById('menu-edit-panel');
            var backdrop = document.getElementById('menu-edit-backdrop');
            var form = document.getElementById('staff-menu-edit-form');
            var closeBtn = document.getElementById('btn-close-menu-edit');
            var cancelBtn = document.getElementById('btn-cancel-menu-edit');
            var ui = document.getElementById('staff-menu-edit-price-stepper');
            var real = document.getElementById('staff-menu-edit-price-value');
            if (!modal || !panel || !form) return;

            function openEdit() {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                modal.classList.add('opacity-100', 'pointer-events-auto');
                panel.classList.remove('translate-y-full', 'sm:scale-95', 'sm:opacity-0');
                panel.classList.add('translate-y-0', 'sm:scale-100', 'sm:opacity-100');
                document.documentElement.classList.add('overflow-hidden');
            }

            function closeEdit() {
                modal.classList.add('opacity-0', 'pointer-events-none');
                modal.classList.remove('opacity-100', 'pointer-events-auto');
                panel.classList.add('translate-y-full', 'sm:scale-95', 'sm:opacity-0');
                panel.classList.remove('translate-y-0', 'sm:scale-100', 'sm:opacity-100');
                document.documentElement.classList.remove('overflow-hidden');
            }

            document.querySelectorAll('.btn-menu-edit').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    form.action = btn.getAttribute('data-url') || '';
                    document.getElementById('staff-menu-edit-name').value = btn.getAttribute('data-name') || '';
                    document.getElementById('staff-menu-edit-category').value = btn.getAttribute('data-category') ||
                        'Meals';
                    var p = btn.getAttribute('data-price') || '';
                    if (ui) ui.value = p;
                    if (real) real.value = p;
                    var ph = document.getElementById('staff-menu-edit-photo');
                    if (ph) ph.value = '';
                    openEdit();
                    setTimeout(function() {
                        var n = document.getElementById('staff-menu-edit-name');
                        if (n) n.focus();
                    }, 200);
                });
            });

            closeBtn && closeBtn.addEventListener('click', closeEdit);
            cancelBtn && cancelBtn.addEventListener('click', closeEdit);
            backdrop && backdrop.addEventListener('click', closeEdit);
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('pointer-events-auto')) {
                    closeEdit();
                }
            });

            if (ui && real) {
                function sync() {
                    real.value = ui.value.trim() === '' ? '' : ui.value;
                }
                ui.addEventListener('input', sync);
                ui.addEventListener('change', sync);
            }

            form.addEventListener('submit', function(e) {
                if (ui && real) real.value = ui.value.trim() === '' ? '' : ui.value;
                var nameInput = document.getElementById('staff-menu-edit-name');
                var v = real ? String(real.value).trim() : '';
                if (!nameInput || !String(nameInput.value).trim()) {
                    e.preventDefault();
                    if (nameInput) nameInput.reportValidity();
                    return;
                }
                if (v === '' || isNaN(Number(v)) || Number(v) < 0) {
                    e.preventDefault();
                    if (ui) {
                        ui.setCustomValidity('Enter a valid price (₱0 or more).');
                        ui.reportValidity();
                        ui.setCustomValidity('');
                    }
                }
            });
        })();
    </script>
</x-layouts.staff-subpage>
