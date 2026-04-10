@extends('layouts.app')

@section('content')

<div class="container text-center mt-3">

    <h4>Select your seat</h4>
    @php
        $totalSeats = 25;
        $occupiedCount = count($occupied ?? []);
        $availableCount = $totalSeats - $occupiedCount;
    @endphp

    <div class="d-flex justify-content-center gap-2 mb-3">
        <span class="badge bg-success">Available: {{ $availableCount }}</span>
        <span class="badge bg-danger">Occupied: {{ $occupiedCount }}</span>
        <span class="badge bg-secondary">Total: {{ $totalSeats }}</span>
    </div>

    @if (session('error'))
        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
    @endif

    {{-- FILTER --}}
    <div class="mb-3">
        <button class="btn btn-secondary filter-btn" data-filter="all">All</button>
        <button class="btn btn-outline-secondary filter-btn" data-filter="available">Available</button>
        <button class="btn btn-outline-danger filter-btn" data-filter="occupied">Occupied</button>
        <button class="btn btn-outline-success filter-btn" data-filter="selected">Selected</button>
    </div>

    {{-- SEATS --}}
    <div class="d-flex flex-wrap justify-content-center">

        @php
            $occupied = $occupied ?? [];
        @endphp

        @for ($i = 1; $i <= 25; $i++)
            <div
                class="seat m-2 {{ in_array($i,$occupied) ? 'occupied' : 'available' }}"
                data-seat="{{ $i }}"
            >
                {{ $i }}
            </div>
        @endfor

    </div>

    {{-- FORM --}}
    <form action="{{ route('student.confirm.seat') }}" method="POST">
        @csrf
        <input type="hidden" name="college" value="{{ $college }}">
        <input type="hidden" name="seat" id="seatInput">

        <button class="btn btn-success mt-4 px-5">
            Confirm Seat
        </button>
    </form>

</div>

<style>
.seat{
    width:60px;
    height:60px;
    line-height:60px;
    border-radius:10px;
    cursor:pointer;
    font-weight:bold;
}

.available{ background:#ddd; }
.occupied{ background:#e57373; cursor:not-allowed; }
.selected{ background:#22c55e; color:#fff; }
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {

    let selected = null;

    document.querySelectorAll('.seat').forEach(seat => {
        seat.onclick = () => {

            if(seat.classList.contains('occupied')) return;

            document.querySelectorAll('.seat').forEach(s => s.classList.remove('selected'));

            seat.classList.add('selected');
            selected = seat.dataset.seat;

            document.getElementById('seatInput').value = selected;
        }
    });

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.onclick = () => {

            let filter = btn.dataset.filter;

            document.querySelectorAll('.seat').forEach(seat => {
                seat.style.display = 'block';

                if(filter === 'available' && !seat.classList.contains('available')){
                    seat.style.display = 'none';
                }

                if(filter === 'occupied' && !seat.classList.contains('occupied')){
                    seat.style.display = 'none';
                }

                if(filter === 'selected' && !seat.classList.contains('selected')){
                    seat.style.display = 'none';
                }
            });
        }
    });

});
</script>

@endsection
