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
            background:#06b6d4;
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
            color:#06b6d4;
        }

        .seating-section{
            background:white;
            padding:20px;
            border-radius:10px;
            margin-bottom:20px;
            box-shadow:0 2px 5px rgba(0,0,0,0.1);
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
            align-items:center;
            justify-content:center;
            cursor:pointer;
            font-weight:bold;
            font-size:12px;
            transition:all 0.3s ease;
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
            background:#06b6d4;
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
            background:#0891b2;
        }
    </style>
</head>

<body>

<div class="header">
    <a href="{{ route('staff.dashboard') }}" class="back">← Back to Dashboard</a>
    <h1>Seat Reservation</h1>
</div>

<div class="container">

    <div class="stats">
        <div class="stat-card">
            <div class="stat-label">Available</div>
            <div class="stat-value">8</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Occupied</div>
            <div class="stat-value">17</div>
        </div>
    </div>

    <div class="seating-section">
        <h3>Seating Map</h3>

        <div class="seats-grid">
            <div class="seat available">A1</div>
            <div class="seat available">A2</div>
            <div class="seat occupied">A3</div>
            <div class="seat occupied">A4</div>
            <div class="seat available">A5</div>

            <div class="seat occupied">B1</div>
            <div class="seat available">B2</div>
            <div class="seat reserved">B3</div>
            <div class="seat occupied">B4</div>
            <div class="seat occupied">B5</div>

            <div class="seat available">C1</div>
            <div class="seat occupied">C2</div>
            <div class="seat occupied">C3</div>
            <div class="seat available">C4</div>
            <div class="seat available">C5</div>

            <div class="seat occupied">D1</div>
            <div class="seat occupied">D2</div>
            <div class="seat available">D3</div>
            <div class="seat occupied">D4</div>
            <div class="seat available">D5</div>

            <div class="seat available">E1</div>
            <div class="seat available">E2</div>
            <div class="seat occupied">E3</div>
            <div class="seat occupied">E4</div>
            <div class="seat occupied">E5</div>
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
            <div class="legend-item">
                <div class="legend-box" style="background:#fff3cd; border-color:#ffc107;"></div>
                <span>Reserved</span>
            </div>
        </div>
    </div>

    <button class="reserve-btn">Reserve Seat</button>

</div>

</body>
</html>
