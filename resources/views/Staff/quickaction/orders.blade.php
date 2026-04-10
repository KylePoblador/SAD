<!DOCTYPE html>
<html>
<head>
    <title>Order Management</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            background:#f2f2f2;
            margin:0;
        }

        .header{
            background:#16c43b;
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

        .container{ padding:20px; }

        .tabs{
            display:flex;
            gap:10px;
            margin-bottom:20px;
        }

        .tab{
            padding:8px 18px;
            border-radius:8px;
            background:#d9b98c;
            border:none;
            cursor:pointer;
            transition: all 0.3s ease;
        }

        .tab:hover{
            opacity: 0.8;
        }

        .tab.active{
            background:#22c55e;
            color:white;
        }

        .order-card{
            background:#ded9d9;
            padding:20px;
            border-radius:10px;
            width:420px;
            border:1px solid #aaa;
            margin-bottom:20px;
        }

        .top-row{
            display:flex;
            justify-content:space-between;
            align-items:center;
        }

        .order-header{
            font-weight:bold;
            font-size:18px;
        }

        .badge{
            background:#8be28b;
            padding:4px 10px;
            border-radius:12px;
            font-size:12px;
            font-weight:bold;
        }

        .time{
            font-size:12px;
            text-align:right;
        }

        .order-id{
            font-size:13px;
            margin-bottom:15px;
        }

        .item{
            display:flex;
            justify-content:space-between;
            margin:6px 0;
        }

        .divider{
            border-top:1px solid gray;
            margin:10px 0;
        }

        .total{
            display:flex;
            justify-content:space-between;
            font-weight:bold;
        }

        .action-btn{
            margin-top:15px;
            background:#ff8a00;
            border:none;
            padding:10px;
            width:220px;
            border-radius:6px;
            font-weight:bold;
            cursor:pointer;
        }
    </style>
</head>

<body>

@php
    $status = $status ?? 'pending';
@endphp

<div class="header">
    <a href="{{ route('staff.dashboard') }}" class="back">← Back to Dashboard</a>
    <h1>Order Management</h1>
    <p style="margin:8px 0 0 0; font-size:13px; opacity:0.9;">{{ $canteenName ?? 'Canteen' }}</p>
</div>

<div class="container">

    <!-- Tabs -->
    <div class="tabs">
        <a href="{{ route('staff.orders', ['status'=>'pending']) }}" style="text-decoration: none;">
            <button class="tab {{ $status=='pending' ? 'active':'' }}">Pending</button>
        </a>

        <a href="{{ route('staff.orders', ['status'=>'preparing']) }}" style="text-decoration: none;">
            <button class="tab {{ $status=='preparing' ? 'active':'' }}">Preparing</button>
        </a>

        <a href="{{ route('staff.orders', ['status'=>'ready']) }}" style="text-decoration: none;">
            <button class="tab {{ $status=='ready' ? 'active':'' }}">Ready</button>
        </a>

        <a href="{{ route('staff.orders', ['status'=>'completed']) }}" style="text-decoration: none;">
            <button class="tab {{ $status=='completed' ? 'active':'' }}">Completed</button>
        </a>
    </div>

    <!-- ✅ FIXED: LOOP ORDERS HERE -->
    @forelse($orders as $order)

        <div class="order-card">

            <div class="top-row">
                <div class="order-header">
                    {{ $order->name }}
                </div>

                @if($status == 'completed')
                    <div>
                        <div class="badge">PAID</div>
                        <div class="time">
                            {{ $order->created_at->format('h:i A') }}
                        </div>
                    </div>
                @endif
            </div>

            <div class="order-id">
                {{ $order->student_id }} <br>
                Order ID: {{ $order->order_number }}
            </div>

            <!-- Items (replace later with real relation if you have it) -->
            @if(isset($order->items) && $order->items->count() > 0)
                @foreach($order->items as $item)
                    <div class="item">
                        <span>{{ $item->name }} x{{ $item->qty }}</span>
                        <span>₱{{ number_format($item->price, 2) }}</span>
                    </div>
                @endforeach
            @else
                <div class="item">
                    <span>Chicken Adobo Meal x1</span>
                    <span>₱65.00</span>
                </div>
                <div class="item">
                    <span>Iced Coffee x1</span>
                    <span>₱35.00</span>
                </div>
            @endif

            <div class="divider"></div>

            <div class="total">
                <span>Total</span>
                <span>₱{{ number_format($order->total ?? 100, 2) }}</span>
            </div>

            <!-- Buttons -->
            @if($status == 'pending')
                <button class="action-btn">Start Preparing</button>
            @elseif($status == 'preparing')
                <button class="action-btn">Mark as Ready</button>
            @elseif($status == 'ready')
                <button class="action-btn">Mark as Complete</button>
            @endif

        </div>

    @empty
        <p>No orders found.</p>
    @endforelse

</div>

</body>
</html>
