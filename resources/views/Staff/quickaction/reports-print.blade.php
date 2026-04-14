<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printable Report - {{ $canteenName }}</title>
    @include('partials.coinmeal-assets')
    <style>
        body {
            background: #fff;
            color: #111827;
        }

        .container {
            max-width: 980px;
            margin: 0 auto;
            padding: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background: #f9fafb;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            margin: 12px 0;
        }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px 10px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="no-print" style="margin-bottom: 12px;">
            <button type="button" onclick="window.print()"
                style="background:#111827;color:#fff;border:none;border-radius:6px;padding:8px 12px;font-weight:600;cursor:pointer;">
                Print now
            </button>
        </div>

        <h1 style="margin:0;font-size:20px;">Reports & Analytics</h1>
        <p style="margin:4px 0 0 0;font-size:13px;">{{ $canteenName }} ({{ $collegeCode }})</p>
        <p style="margin:4px 0 0 0;font-size:12px;color:#6b7280;">
            Period:
            {{ $from ? \Illuminate\Support\Carbon::parse($from)->format('M d, Y') : 'Start' }}
            -
            {{ $to ? \Illuminate\Support\Carbon::parse($to)->format('M d, Y') : 'Present' }}
        </p>

        <div class="summary">
            <div class="card">
                <div style="font-size:11px;color:#6b7280;">Income (successful)</div>
                <div style="font-weight:700;font-size:18px;">₱{{ number_format($totalIncome, 2) }}</div>
            </div>
            <div class="card">
                <div style="font-size:11px;color:#6b7280;">Successful orders</div>
                <div style="font-weight:700;font-size:18px;">{{ $successfulOrdersCount }}</div>
            </div>
            <div class="card">
                <div style="font-size:11px;color:#6b7280;">Cancelled orders</div>
                <div style="font-weight:700;font-size:18px;">{{ $cancelledOrdersCount }}</div>
            </div>
            <div class="card">
                <div style="font-size:11px;color:#6b7280;">Cancelled amount</div>
                <div style="font-weight:700;font-size:18px;">₱{{ number_format($cancelledAmount, 2) }}</div>
            </div>
        </div>

        <h2 style="font-size:14px;margin:16px 0 8px 0;">Top selling items (successful)</h2>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Sold qty</th>
                    <th>Total sales</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($topItems as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ (int) $item->sold }}</td>
                        <td>₱{{ number_format((float) $item->total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No successful sales in selected period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <h2 style="font-size:14px;margin:16px 0 8px 0;">Successful order list</h2>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($successfulOrders as $order)
                    <tr>
                        <td>{{ $order->order_number ?? 'ORD-'.$order->id }}</td>
                        <td>{{ $order->user->name ?? 'Student' }}</td>
                        <td>{{ $order->created_at?->format('M d, Y H:i') }}</td>
                        <td>{{ (int) $order->items->sum('qty') }}</td>
                        <td>₱{{ number_format((float) $order->total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No successful orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="page-break"></div>
        <h2 style="font-size:14px;margin:0 0 8px 0;">Cancelled order list</h2>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cancelledOrders as $order)
                    <tr>
                        <td>{{ $order->order_number ?? 'ORD-'.$order->id }}</td>
                        <td>{{ $order->user->name ?? 'Student' }}</td>
                        <td>{{ $order->created_at?->format('M d, Y H:i') }}</td>
                        <td>{{ (int) $order->items->sum('qty') }}</td>
                        <td>₱{{ number_format((float) $order->total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No cancelled orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>

</html>
