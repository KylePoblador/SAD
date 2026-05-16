@extends('layouts.app')

@section('title', 'Reserve a seat')

@section('content')
    <div class="mb-4">
        @include('partials.app-back-link', [
            'href' => route('student.canteen', ['college' => $college]),
            'variant' => 'student',
        ])
    </div>

    @php
        $totalSeats   = $totalSeats ?? 25;
        $occupiedCount= $occupiedCount ?? count($seatCounts ?? []);
        $availableCount = $availableCount ?? max($totalSeats - $occupiedCount, 0);
        $myReservation  = $myReservation ?? null;
    @endphp

    @if (session('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif
    @if (session('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- ── MY CURRENT RESERVATION (share code card) ── --}}
    @if ($myReservation)
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-widest text-green-700 mb-1">Your reserved seat</p>
            <p class="text-4xl font-extrabold text-green-900">Seat {{ $myReservation->seat_number }}</p>
            <div class="mt-4 rounded-xl border border-green-300 bg-white px-5 py-4 text-center">
                <p class="mb-1 text-[10px] font-bold uppercase tracking-widest text-gray-400">Share code</p>
                <p class="font-mono text-3xl font-extrabold tracking-[0.3em] text-gray-900 select-all">
                    {{ $myReservation->share_code }}
                </p>
                <p class="mt-2 text-xs text-gray-500">
                    Give this code to your friends. They enter it on this page to join your seat (if there's still room).
                </p>
            </div>
        </div>
    @endif

    {{-- ── JOIN BY CODE ── --}}
    <div class="mb-6 rounded-2xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
        <p class="text-sm font-bold text-indigo-900 mb-1">Join a friend's seat</p>
        <p class="text-xs text-indigo-700 mb-3">Enter their 6-character share code to reserve the same seat (if capacity allows).</p>
        <form action="{{ route('student.confirm-seat') }}" method="POST" class="flex gap-2">
            @csrf
            <input type="hidden" name="college" value="{{ $college }}">
            <input type="hidden" name="seat" value="">
            <input type="text" name="share_code" maxlength="6"
                class="flex-1 rounded-xl border border-indigo-300 px-4 py-2.5 text-center font-mono text-lg font-bold uppercase tracking-widest focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                placeholder="ABC123"
                value="{{ old('share_code') }}">
            <button type="submit"
                class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                Join
            </button>
        </form>
    </div>

    {{-- ── SEAT MAP ── --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <h1 class="text-base font-bold text-gray-900 mb-1">Or pick a seat from the map</h1>
        <div class="mb-4 flex flex-wrap gap-2">
            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">Available: {{ $availableCount }}</span>
            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">Reserved spots: {{ $occupiedCount }}</span>
            <span class="rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-700">Total capacity: {{ $totalSeats }}</span>
        </div>

        <div class="mb-4 flex flex-wrap gap-2">
            <button type="button" class="filter-btn rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50" data-filter="all">All</button>
            <button type="button" class="filter-btn rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50" data-filter="available">Available</button>
            <button type="button" class="filter-btn rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50" data-filter="occupied">Occupied</button>
        </div>

        <p class="mb-4 text-xs text-gray-500">Tap a seat to select it. Full seats (red) cannot be picked.</p>

        @php
            $seatCapacities = $seatCapacities ?? collect();
            $seatCounts     = $seatCounts ?? collect();
        @endphp
        <div class="flex flex-wrap justify-center gap-2">
            @for ($i = 1; $i <= 25; $i++)
                @php
                    $capacity = $seatCapacities[$i] ?? 1;
                    $current  = $seatCounts[$i] ?? 0;
                    $isFull   = $current >= $capacity;
                    $seatClasses = $isFull
                        ? 'cursor-not-allowed border-red-300 bg-red-100 text-red-900'
                        : ($current > 0
                            ? 'border-amber-300 bg-amber-100 text-amber-900 cursor-pointer'
                            : 'border-gray-200 bg-gray-100 text-gray-800 cursor-pointer');
                @endphp
                <div class="seat m-1 flex h-16 w-16 items-center justify-center rounded-xl border-2 text-sm font-bold transition {{ $seatClasses }}"
                    data-seat="{{ $i }}" data-full="{{ $isFull ? '1' : '0' }}"
                    data-current="{{ $current }}" data-capacity="{{ $capacity }}"
                    title="Seat {{ $i }} — {{ $current }}/{{ $capacity }}">
                    <div class="flex flex-col items-center justify-center">
                        <span>{{ $i }}</span>
                        <span class="text-[10px] font-normal">{{ $current }}/{{ $capacity }}</span>
                    </div>
                </div>
            @endfor
        </div>

        <form action="{{ route('student.confirm-seat') }}" method="POST" class="mt-6 text-center" id="seatForm">
            @csrf
            <input type="hidden" name="college" value="{{ $college }}">
            <input type="hidden" name="seat" id="seatInput">
            <p id="seatSelectedLabel" class="mb-3 text-sm text-gray-500 hidden">
                Selected: <strong id="seatSelectedNum" class="text-green-700"></strong>
            </p>
            <button type="submit" id="btnConfirmSeat"
                class="rounded-xl bg-green-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 disabled:opacity-50"
                disabled>
                Confirm seat
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let selected = null;

            document.querySelectorAll('.seat').forEach(seat => {
                seat.addEventListener('click', () => {
                    if (seat.dataset.full === '1') return;

                    document.querySelectorAll('.seat').forEach(s => {
                        if (s.dataset.full !== '1') {
                            s.classList.remove('border-green-600', 'bg-green-600', 'text-white', 'ring-2', 'ring-green-400');
                        }
                    });

                    seat.classList.add('border-green-600', 'bg-green-600', 'text-white', 'ring-2', 'ring-green-400');
                    selected = seat.dataset.seat;
                    document.getElementById('seatInput').value = selected;

                    const lbl = document.getElementById('seatSelectedLabel');
                    const num = document.getElementById('seatSelectedNum');
                    if (lbl && num) { lbl.classList.remove('hidden'); num.textContent = 'Seat ' + selected; }

                    const btn = document.getElementById('btnConfirmSeat');
                    if (btn) btn.disabled = false;
                });
            });

            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const filter = btn.dataset.filter;
                    document.querySelectorAll('.seat').forEach(seat => {
                        seat.style.display = 'flex';
                        if (filter === 'available' && seat.dataset.full === '1') seat.style.display = 'none';
                        if (filter === 'occupied'  && seat.dataset.full !== '1') seat.style.display = 'none';
                    });
                });
            });
        });
    </script>
@endsection
