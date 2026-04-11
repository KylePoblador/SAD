<x-layouts.staff-subpage title="Seat reservation" subtitle="View and release seats">
    @if (session('status') === 'seat-released')
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
            Seat has been released and is now available.
        </div>
    @endif

    <div class="grid grid-cols-2 gap-3">
        <div class="rounded-xl border border-gray-100 bg-white p-4 text-center shadow-sm">
            <p class="text-xs font-medium text-gray-500">Available</p>
            <p class="mt-1 text-3xl font-bold text-green-600">{{ $availableSeats }}</p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-4 text-center shadow-sm">
            <p class="text-xs font-medium text-gray-500">Occupied</p>
            <p class="mt-1 text-3xl font-bold text-red-600">{{ $occupiedSeats }}</p>
        </div>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <h3 class="mb-4 text-sm font-bold text-gray-900">Seating map</h3>

        <div class="grid grid-cols-5 gap-2">
            @foreach ($seats as $seat)
                @php
                    $base =
                        'seat flex aspect-square cursor-pointer flex-col items-center justify-center rounded-lg border-2 p-1 text-center text-xs font-bold transition';
                    $state = match ($seat['status']) {
                        'available' => 'border-green-500 bg-green-50 text-green-900',
                        'occupied' => 'border-red-400 bg-red-50 text-red-900',
                        'reserved' => 'border-amber-400 bg-amber-50 text-amber-900',
                        default => 'border-gray-300 bg-gray-50 text-gray-700',
                    };
                @endphp
                <div class="{{ $base }} {{ $state }}" data-seat="{{ $seat['number'] }}"
                    data-status="{{ $seat['status'] }}" data-student="{{ $seat['student'] ?? '' }}">
                    <span class="text-sm">{{ $seat['number'] }}</span>
                    @if ($seat['status'] === 'occupied' && !empty($seat['student']))
                        <span class="mt-0.5 line-clamp-2 text-[10px] font-normal leading-tight opacity-90">
                            {{ $seat['student'] }}
                        </span>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-4 flex flex-wrap gap-4 text-xs text-gray-600">
            <span class="inline-flex items-center gap-2">
                <span class="h-4 w-4 rounded border-2 border-green-500 bg-green-50"></span> Available
            </span>
            <span class="inline-flex items-center gap-2">
                <span class="h-4 w-4 rounded border-2 border-red-400 bg-red-50"></span> Occupied
            </span>
        </div>

        <p class="mt-3 text-xs text-gray-500">Tap an occupied seat to release it back to available status.</p>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form id="releaseForm" action="{{ route('staff.seats.release') }}" method="POST">
            @csrf
            <input type="hidden" name="seat_number" id="seat_number" value="">
            <button id="releaseButton" type="submit" disabled
                class="w-full rounded-xl bg-green-600 py-3 text-sm font-semibold text-white shadow-sm transition enabled:hover:bg-green-700 disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-500">
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
                        selectedInfo.textContent = `Seat ${seatNumber} is occupied${student ? ` by ${student}` : ''}. Click release to make it available.`;
                    } else {
                        releaseButton.disabled = true;
                        selectedInfo.textContent = `Seat ${seatNumber} is already available.`;
                    }
                });
            });
        });
    </script>
</x-layouts.staff-subpage>
