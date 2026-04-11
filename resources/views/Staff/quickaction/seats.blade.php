<!DOCTYPE html>
<html>
<head>
    <title>Seat Reservation</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            background:#f2f2f2;
            margin:0;
        }

        .header{
            background:#16a34a;
            color:white;
            padding:20px;
        }

        .header h1{ margin:0; }

        .back{
            font-size:14px;
            margin-bottom:10px;
            display:inline-block;
            color:white;
            text-decoration:none;
        }

        .container{
            padding:20px;
            max-width:600px;
            margin:0 auto;
        }

        .stats{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:15px;
            margin-bottom:20px;
        }

        .stat-card{
            background:white;
            padding:20px;
            border-radius:10px;
            text-align:center;
            box-shadow:0 2px 5px rgba(0,0,0,0.1);
        }

        .stat-label{
            color:#666;
            font-size:14px;
            margin-bottom:10px;
        }

        .stat-value{
            font-size:32px;
            font-weight:bold;
            color:#16a34a;
        }

        .seating-section{
            background:white;
            padding:20px;
            border-radius:10px;
            margin-bottom:20px;
            box-shadow:0 2px 5px rgba(0,0,0,0.08);
        }

        .seating-section h3{
            margin:0 0 20px 0;
            color:#333;
        }

        .seats-grid{
            display:grid;
            grid-template-columns:repeat(5, 1fr);
            gap:10px;
            margin-bottom:20px;
        }

        .seat{
            aspect-ratio:1;
            border:2px solid #ddd;
            border-radius:8px;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            cursor:pointer;
            font-weight:bold;
            font-size:12px;
            transition:all 0.3s ease;
            padding:8px;
            text-align:center;
        }

        .seat:hover{
            transform:scale(1.05);
        }

        .seat.available{
            background:#d4edda;
            border-color:#22c55e;
            color:#155724;
        }

        .seat.occupied{
            background:#f8d7da;
            border-color:#dc3545;
            color:#721c24;
        }

        .seat.reserved{
            background:#fff3cd;
            border-color:#ffc107;
            color:#856404;
        }

        .seat-number{
            font-size:14px;
            margin-bottom:4px;
        }

        .seat-student{
            font-size:10px;
            line-height:1.2;
            opacity:0.85;
        }

        .legend{
            display:flex;
            gap:20px;
            margin-top:20px;
            font-size:12px;
        }

        .legend-item{
            display:flex;
            align-items:center;
            gap:8px;
        }

        .legend-box{
            width:20px;
            height:20px;
            border-radius:4px;
            border:2px solid;
        }

        .reserve-btn{
            background:#16a34a;
            color:white;
            border:none;
            padding:12px 20px;
            border-radius:8px;
            cursor:pointer;
            font-weight:bold;
            width:100%;
            transition:all 0.3s ease;
        }

        .reserve-btn:hover{
            background:#15803d;
        }

        .alert{
            background:#d1fae5;
            border:1px solid #10b981;
            color:#065f46;
            padding:14px 18px;
            border-radius:10px;
            margin-bottom:20px;
        }

        .action-panel{
            background:white;
            padding:20px;
            border-radius:10px;
            box-shadow:0 2px 5px rgba(0,0,0,0.08);
            margin-top:20px;
        }

        .note{
            font-size:13px;
            color:#14532d;
            margin-top:10px;
        }
    </style>
</head>

<body>

<div class="header">
    <a href="{{ route('staff.dashboard') }}" class="back">← Back to Dashboard</a>
    <h1>Seat Reservation</h1>
</div>

<div class="container">

    @if(session('status') === 'seat-released')
        <div class="alert">Seat has been released and is now available.</div>
    @endif

    <div class="stats">
        <div class="stat-card">
            <div class="stat-label">Available</div>
            <div class="stat-value">{{ $availableSeats }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Occupied</div>
            <div class="stat-value">{{ $occupiedSeats }}</div>
        </div>
    </div>

    <div class="seating-section">
        <h3>Seating Map</h3>

        <div class="seats-grid">
            @foreach($seats as $seat)
                <div class="seat {{ $seat['status'] }}"
                     data-seat="{{ $seat['number'] }}"
                     data-status="{{ $seat['status'] }}"
                     data-student="{{ $seat['student'] ?? '' }}">
                    <div class="seat-number">{{ $seat['number'] }}</div>
                    @if($seat['status'] === 'occupied' && $seat['student'])
                        <div class="seat-student">{{ $seat['student'] }}</div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="legend">
            <div class="legend-item">
                <div class="legend-box" style="background:#d4edda; border-color:#22c55e;"></div>
                <span>Available</span>
            </div>
            <div class="legend-item">
                <div class="legend-box" style="background:#f8d7da; border-color:#dc3545;"></div>
                <span>Occupied</span>
            </div>
        </div>

        <div class="note">Tap an occupied seat to release it back to available status.</div>
    </div>

    <div class="action-panel">
        <form id="releaseForm" action="{{ route('staff.seats.release') }}" method="POST">
            @csrf
            <input type="hidden" name="seat_number" id="seat_number" value="">
            <button id="releaseButton" type="submit" disabled>Release Selected Seat</button>
        </form>
        <p id="selectedInfo" class="note">No seat selected.</p>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const seats = document.querySelectorAll('.seat');
        const releaseButton = document.getElementById('releaseButton');
        const seatInput = document.getElementById('seat_number');
        const selectedInfo = document.getElementById('selectedInfo');

        seats.forEach(seat => {
            seat.addEventListener('click', function () {
                seats.forEach(s => s.classList.remove('selected'));
                this.classList.add('selected');
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

</body>
</html>
