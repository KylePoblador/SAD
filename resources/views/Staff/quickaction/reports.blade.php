<!DOCTYPE html>
<html>
<head>
    <title>Reports & Analytics</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            background:#f2f2f2;
            margin:0;
        }

        .header{
            background:#22c55e;
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

        .card{
            background:white;
            padding:20px;
            border-radius:10px;
            margin-bottom:20px;
            box-shadow:0 2px 5px rgba(0,0,0,0.1);
        }

        .title{
            font-size:16px;
            font-weight:bold;
            margin-bottom:10px;
        }

        .revenue{
            font-size:30px;
            font-weight:bold;
            color:#22c55e;
        }

        .sub{
            font-size:12px;
            color:#888;
        }

        .item{
            display:flex;
            justify-content:space-between;
            padding:10px 0;
            border-bottom:1px solid #eee;
        }

        .item:last-child{
            border-bottom:none;
        }

        .name{
            font-weight:bold;
        }

        .sold{
            font-size:12px;
            color:#777;
        }

        .price{
            color:#22c55e;
            font-weight:bold;
        }

        .total{
            display:flex;
            justify-content:space-between;
            margin-top:15px;
            font-weight:bold;
        }
    </style>
</head>

<body>

<div class="header">
    <a href="{{ route('staff.dashboard') }}" class="back">← back</a>
    <h1>Reports & Analytics</h1>
    <small>Today's Summary</small>
</div>

<div class="container">

    <!-- Revenue -->
    <div class="card">
        <div class="title">Today's Revenue</div>
        <div class="revenue">₱ {{ number_format($todayRevenue ?? 0, 2) }}</div>
        <div class="sub">From {{ $totalOrders ?? 0 }} orders</div>
    </div>

    <!-- Top Items -->
    <div class="card">
        <div class="title">Top Selling Items</div>

        @if(isset($topItems) && count($topItems) > 0)
            @foreach($topItems as $item)
                <div class="item">
                    <div>
                        <div class="name">{{ $item->name ?? 'Item' }}</div>
                        <div class="sold">{{ $item->sold ?? 0 }} sold</div>
                    </div>
                    <div class="price">
                        ₱ {{ number_format($item->total ?? 0, 2) }}
                    </div>
                </div>
            @endforeach
        @else
            <p style="color:#777;">No sales today.</p>
        @endif

        <div class="total">
            <span>TOTAL :</span>
            <span class="price">
                ₱ {{ number_format($totalSales ?? 0, 2) }}
            </span>
        </div>
    </div>

</div>

</body>
</html>
