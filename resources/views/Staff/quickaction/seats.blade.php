@php
    $seatRows = $seatRows ?? collect($seats ?? [])->chunk(5);
@endphp

<x-layouts.staff-subpage title="Seat management" :subtitle="$canteenName ?? 'Canteen'">
    @if (session('status') === 'seat-released')
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
            Seat has been released and is now available.
        </div>
    @endif

    @if (session('status') === 'all-seats-released')
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
            @if (session('bulk_scope') === 'all_reserved')
                All reservations have been cleared. Every seat is available again.
            @else
                All occupied seats have been cleared. Every seat is available again.
            @endif
        </div>
    @endif

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-100 bg-white p-4 text-center shadow-sm">
            <p class="text-xs font-medium text-gray-500">Total seats</p>
            <p class="mt-1 text-2xl font-bold text-gray-800">{{ $totalSeats }}</p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-4 text-center shadow-sm">
            <p class="text-xs font-medium text-gray-500">Available</p>
            <p class="mt-1 text-2xl font-bold text-green-600">{{ $availableSeats }}</p>
        </div>
        <div class="col-span-2 rounded-xl border border-gray-100 bg-white p-4 text-center shadow-sm sm:col-span-1">
            <p class="text-xs font-medium text-gray-500">Occupied / reserved</p>
            <p class="mt-1 text-2xl font-bold text-red-600">{{ $occupiedSeats }}</p>
        </div>
    </div>

    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-relaxed text-amber-950">
        <strong>Overview:</strong> In CoinMeal, a seat shows as <strong>occupied</strong> when a student has an active
        reservation. “Reserved” uses the same data — bulk actions below both clear every reservation for
        <strong>{{ strtoupper($collegeCode ?? '') }}</strong>.
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <form method="post" action="{{ route('staff.seats.release-all') }}"
            onsubmit="return confirm('Release ALL occupied seats? Every student will lose their seat assignment for this canteen.');">
            @csrf
            <input type="hidden" name="scope" value="all_occupied">
            <button type="submit"
                class="w-full min-h-[44px] touch-manipulation rounded-xl border-2 border-red-300 bg-white px-4 py-3 text-sm font-semibold text-red-800 shadow-sm transition hover:bg-red-50">
                Clear all occupied
            </button>
        </form>
        <form method="post" action="{{ route('staff.seats.release-all') }}"
            onsubmit="return confirm('Cancel ALL seat reservations? Same as clearing occupied — all students lose their reserved seat here.');">
            @csrf
            <input type="hidden" name="scope" value="all_reserved">
            <button type="submit"
                class="w-full min-h-[44px] touch-manipulation rounded-xl border-2 border-amber-300 bg-white px-4 py-3 text-sm font-semibold text-amber-900 shadow-sm transition hover:bg-amber-50">
                Clear all reserved
            </button>
        </form>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <h3 class="mb-1 text-sm font-bold text-gray-900">Seat layout</h3>
        <p class="mb-4 text-xs text-gray-500">25 seats in 5 rows (1–25). Front of canteen at the top.</p>

        <p class="mb-2 text-center text-[10px] font-bold uppercase tracking-wider text-gray-400">Front / counter</p>

        <div class="mx-auto max-w-md">
            <div class="grid gap-1.5 sm:gap-2" style="grid-template-columns: 1.75rem repeat(5, minmax(0, 1fr));">
                <div></div>
                @foreach (range(1, 5) as $col)
                    <div class="flex items-end justify-center pb-0.5 text-[10px] font-semibold text-gray-400">{{ $col }}
                    </div>
                @endforeach

                @foreach ($seatRows as $rowIndex => $row)
                    <div class="flex items-center justify-end pr-1 text-[10px] font-semibold text-gray-500">
                        R{{ $rowIndex + 1 }}</div>
                    @foreach ($row as $seat)
                        @php
                            $base =
                                'seat flex aspect-square cursor-pointer flex-col items-center justify-center rounded-lg border-2 p-0.5 text-center text-[10px] font-bold transition sm:p-1 sm:text-xs';
                            $state = match ($seat['status']) {
                                'available' => 'border-green-500 bg-green-50 text-green-900',
                                'occupied' => 'border-red-400 bg-red-50 text-red-900',
                                'reserved' => 'border-amber-400 bg-amber-50 text-amber-900',
                                default => 'border-gray-300 bg-gray-50 text-gray-700',
                            };
                        @endphp
                        <div class="{{ $base }} {{ $state }}" data-seat="{{ $seat['number'] }}"
                            data-status="{{ $seat['status'] }}" data-student="{{ $seat['student'] ?? '' }}">
                            <span>{{ $seat['number'] }}</span>
                            @if ($seat['status'] === 'occupied' && !empty($seat['student']))
                                <span class="mt-0.5 line-clamp-2 text-[8px] font-normal leading-tight opacity-90 sm:text-[9px]">
                                    {{ $seat['student'] }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-4 text-xs text-gray-600">
            <span class="inline-flex items-center gap-2">
                <span class="h-4 w-4 rounded border-2 border-green-500 bg-green-50"></span> Available
            </span>
            <span class="inline-flex items-center gap-2">
                <span class="h-4 w-4 rounded border-2 border-red-400 bg-red-50"></span> Occupied (reserved)
            </span>
        </div>

        <p class="mt-3 text-xs text-gray-500">Tap an occupied seat, then use <strong>Release selected seat</strong> below.
        </p>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form id="releaseForm" action="{{ route('staff.seats.release') }}" method="POST">
            @csrf
            <input type="hidden" name="seat_number" id="seat_number" value="">
            <button id="releaseButton" type="submit" disabled
                class="w-full min-h-[44px] touch-manipulation rounded-xl bg-green-600 py-3 text-sm font-semibold text-white shadow-sm transition enabled:hover:bg-green-700 disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-500">
                Release selected seat
            </button>
        </form>
        <p id="selectedInfo" class="mt-3 text-xs text-gray-600">No seat selected.</p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const seats = document.querySelectorAll('.seat');
            const releaseButton = document.getElementById('releaseButton');
            const seatInput = document.getElementById('seat_number');
            const selectedInfo = document.getElementById('selectedInfo');

            seats.forEach(seat => {
                seat.addEventListener('click', function() {
                    seats.forEach(s => s.classList.remove('ring-2', 'ring-green-600', 'ring-offset-2'));
                    this.classList.add('ring-2', 'ring-green-600', 'ring-offset-2');
                    const seatNumber = this.dataset.seat;
                    const status = this.dataset.status;
                    const student = this.dataset.student;

                    seatInput.value = seatNumber;

                    if (status === 'occupied') {
                        releaseButton.disabled = false;
                        selectedInfo.textContent = `Seat ${seatNumber} is occupied${student ? ` by ${student}` : ''}. Tap the button below to release it.`;
                    } else {
                        releaseButton.disabled = true;
                        selectedInfo.textContent = `Seat ${seatNumber} is available.`;
                    }
                });
            });
        });
    </script>
</x-layouts.staff-subpage>
