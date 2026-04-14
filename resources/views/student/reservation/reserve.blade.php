@extends('layouts.app')

@section('title', 'Reserve a seat')

@section('content')
    <div class="mb-4">
        @include('partials.app-back-link', ['href' => route('student.canteen', ['college' => $college]), 'variant' => 'student'])
    </div>
    <div class="text-center">
        <h1 class="text-lg font-bold text-gray-900">Select your seat</h1>
        @php
            $totalSeats = 25;
            $occupiedCount = count($occupied ?? []);
            $availableCount = $totalSeats - $occupiedCount;
        @endphp

        <div class="mt-4 flex flex-wrap justify-center gap-2">
            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">Available:
                {{ $availableCount }}</span>
            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">Occupied:
                {{ $occupiedCount }}</span>
            <span class="rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-700">Total:
                {{ $totalSeats }}</span>
        </div>

        @if (session('error'))
            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <div class="mt-6 flex flex-wrap justify-center gap-2">
            <button type="button"
                class="filter-btn rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                data-filter="all">All</button>
            <button type="button"
                class="filter-btn rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                data-filter="available">Available</button>
            <button type="button"
                class="filter-btn rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                data-filter="occupied">Occupied</button>
            <button type="button"
                class="filter-btn rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                data-filter="selected">Selected</button>
        </div>

        <div class="mt-6 flex flex-wrap justify-center gap-2">
            @php
                $occupied = $occupied ?? [];
            @endphp
            @for ($i = 1; $i <= 25; $i++)
                <div class="seat m-1 flex h-14 w-14 cursor-pointer items-center justify-center rounded-xl border-2 text-sm font-bold transition {{ in_array($i, $occupied) ? 'cursor-not-allowed border-red-300 bg-red-100 text-red-900' : 'border-gray-200 bg-gray-100 text-gray-800' }}"
                    data-seat="{{ $i }}">
                    {{ $i }}
                </div>
            @endfor
        </div>

        <form action="{{ route('student.confirm.seat') }}" method="POST" class="mt-8">
            @csrf
            <input type="hidden" name="college" value="{{ $college }}">
            <input type="hidden" name="seat" id="seatInput">

            <button type="submit"
                class="rounded-xl bg-green-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700">
                Confirm seat
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let selected = null;

            document.querySelectorAll('.seat').forEach(seat => {
                seat.addEventListener('click', () => {
                    if (seat.classList.contains('cursor-not-allowed')) return;

                    document.querySelectorAll('.seat').forEach(s => {
                        if (!s.classList.contains('cursor-not-allowed')) {
                            s.classList.remove('border-green-600', 'bg-green-600', 'text-white', 'seat-picked');
                            s.classList.add('border-gray-200', 'bg-gray-100', 'text-gray-800');
                        }
                    });

                    seat.classList.remove('border-gray-200', 'bg-gray-100', 'text-gray-800');
                    seat.classList.add('border-green-600', 'bg-green-600', 'text-white', 'seat-picked');
                    selected = seat.dataset.seat;
                    document.getElementById('seatInput').value = selected;
                });
            });

            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const filter = btn.dataset.filter;
                    document.querySelectorAll('.seat').forEach(seat => {
                        seat.style.display = 'flex';
                        if (filter === 'available' && seat.classList.contains('cursor-not-allowed')) {
                            seat.style.display = 'none';
                        }
                        if (filter === 'occupied' && !seat.classList.contains('cursor-not-allowed')) {
                            seat.style.display = 'none';
                        }
                        if (filter === 'selected' && !seat.classList.contains('seat-picked')) {
                            seat.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
@endsection
