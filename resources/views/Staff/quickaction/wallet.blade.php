<!DOCTYPE html>
<html>
<head>
    <title>Wallet Management</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            background:#f2f2f2;
            margin:0;
        }

        .header{
            background:#ec4899;
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
            max-width:500px;
            margin:0 auto;
        }

        .wallet-card{
            background:white;
            border-radius:15px;
            padding:30px;
            box-shadow:0 4px 15px rgba(0,0,0,0.1);
            margin-bottom:20px;
            text-align:center;
        }

        .wallet-label{
            color:#666;
            font-size:14px;
            margin-bottom:10px;
        }

        .wallet-balance{
            font-size:48px;
            font-weight:bold;
            color:#ec4899;
            margin-bottom:20px;
        }

        .balance-info{
            background:#f8f8f8;
            padding:15px;
            border-radius:8px;
            margin:15px 0;
            font-size:14px;
            color:#666;
        }

        .transaction-section{
            margin-top:30px;
        }

        .transaction-section h3{
            text-align:left;
            color:#333;
            margin:20px 0 15px 0;
        }

        .transaction-item{
            background:#f8f8f8;
            padding:12px;
            border-radius:8px;
            margin-bottom:10px;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }

        .transaction-desc{
            text-align:left;
        }

        .transaction-amount{
            font-weight:bold;
            color:#ec4899;
        }

        .load-btn{
            background:#ec4899;
            color:white;
            border:none;
            padding:15px;
            width:100%;
            border-radius:8px;
            font-weight:bold;
            font-size:16px;
            cursor:pointer;
            margin-top:20px;
            transition:all 0.3s ease;
        }

        .load-btn:hover{
            background:#be185d;
        }
    </style>
</head>

<body>

<div class="header">
    <a href="{{ route('staff.dashboard') }}" class="back">← Back to Dashboard</a>
    <h1>Load Wallet</h1>
</div>

<div class="container">

    <div class="wallet-card">
        <div class="wallet-label">Current Wallet Balance</div>
        <div class="wallet-balance">₱1,250.00</div>
        <div class="balance-info">
            Last updated: Today at 2:30 PM
        </div>
    </div>

    <div class="transaction-section">
        <h3>Recent Transactions</h3>

        <div class="transaction-item">
            <div class="transaction-desc">
                <div style="font-weight:bold;">Canteen Purchase</div>
                <div style="font-size:12px; color:#999;">Apr 10, 2:15 PM</div>
            </div>
            <div class="transaction-amount">-₱150.00</div>
        </div>

        <div class="transaction-item">
            <div class="transaction-desc">
                <div style="font-weight:bold;">Wallet Top-up</div>
                <div style="font-size:12px; color:#999;">Apr 09, 10:30 AM</div>
            </div>
            <div class="transaction-amount" style="color:#22c55e;">+₱500.00</div>
        </div>

        <div class="transaction-item">
            <div class="transaction-desc">
                <div style="font-weight:bold;">Canteen Purchase</div>
                <div style="font-size:12px; color:#999;">Apr 08, 1:45 PM</div>
            </div>
            <div class="transaction-amount">-₱85.00</div>
        </div>

        <div class="transaction-item">
            <div class="transaction-desc">
                <div style="font-weight:bold;">Canteen Purchase</div>
                <div style="font-size:12px; color:#999;">Apr 07, 12:00 PM</div>
            </div>
            <div class="transaction-amount">-₱65.00</div>
        </div>
    </div>

    <button class="load-btn">Load Wallet</button>

</div>

</body>
</html>
