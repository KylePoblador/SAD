<!DOCTYPE html>
<html>
<head>
    <title>Menu Management</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            background:#f2f2f2;
            margin:0;
        }

        .header{
            background:#f59e0b;
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

        .menu-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:15px;
            margin-bottom:20px;
        }

        .menu-card{
            background:white;
            padding:15px;
            border-radius:10px;
            border:1px solid #ddd;
            box-shadow:0 2px 5px rgba(0,0,0,0.1);
        }

        .menu-card:hover{
            box-shadow:0 4px 12px rgba(0,0,0,0.15);
            transform:translateY(-2px);
        }

        .menu-name{
            font-weight:bold;
            font-size:14px;
            margin-bottom:8px;
            color:#333;
        }

        .menu-price{
            color:#f59e0b;
            font-weight:bold;
            font-size:16px;
            margin-bottom:8px;
        }

        .menu-status{
            font-size:12px;
            padding:4px 8px;
            border-radius:4px;
            display:inline-block;
        }

        .status-available{
            background:#d4edda;
            color:#155724;
        }

        .status-unavailable{
            background:#f8d7da;
            color:#721c24;
        }

        .add-btn{
            background:#f59e0b;
            color:white;
            border:none;
            padding:12px 20px;
            border-radius:8px;
            cursor:pointer;
            font-weight:bold;
            margin-top:15px;
            width:100%;
            transition:all 0.3s ease;
        }

        .add-btn:hover{
            background:#d97706;
        }
    </style>
</head>

<body>

<div class="header">
    <a href="{{ route('staff.dashboard') }}" class="back">← Back to Dashboard</a>
    <h1>Menu Management</h1>
</div>

<div class="container">

    <p style="color:#666; margin:0 0 20px 0;">Manage your canteen menu items</p>

    <div class="menu-grid">
        <div class="menu-card">
            <div class="menu-name">Chicken Adobo Meal</div>
            <div class="menu-price">₱65.00</div>
            <span class="menu-status status-available">Available</span>
        </div>

        <div class="menu-card">
            <div class="menu-name">Pork Fried Rice</div>
            <div class="menu-price">₱55.00</div>
            <span class="menu-status status-available">Available</span>
        </div>

        <div class="menu-card">
            <div class="menu-name">Iced Coffee</div>
            <div class="menu-price">₱35.00</div>
            <span class="menu-status status-available">Available</span>
        </div>

        <div class="menu-card">
            <div class="menu-name">Samosa</div>
            <div class="menu-price">₱25.00</div>
            <span class="menu-status status-unavailable">Unavailable</span>
        </div>

        <div class="menu-card">
            <div class="menu-name">Lumpia</div>
            <div class="menu-price">₱30.00</div>
            <span class="menu-status status-available">Available</span>
        </div>

        <div class="menu-card">
            <div class="menu-name">Halo-Halo</div>
            <div class="menu-price">₱45.00</div>
            <span class="menu-status status-available">Available</span>
        </div>
    </div>

    <button class="add-btn">+ Add New Menu Item</button>

</div>

</body>
</html>
