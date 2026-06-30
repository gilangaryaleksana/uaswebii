<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }

        h1,
        h2,
        h3 {
            margin: 0;
        }

        .header {
            margin-bottom: 20px;
        }

        .header-table {
            width: 100%;
        }

        .header-table td {
            vertical-align: top;
        }

        .company {
            font-size: 14px;
            font-weight: bold;
        }

        .section {
            margin-bottom: 18px;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f2f2f2;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .no-border td {
            border: none;
            padding: 2px 0;
        }

        .footer {
            margin-top: 40px;
            font-size: 11px;
            text-align: center;
            color: #777;
        }

        @page {
            size: A4;
            margin: 20mm;
        }
    </style>
</head>

<body>
    {{-- HEADER --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <div class="company">Sales Report</div>
                    <table class="no-border" style="margin-top:6px;">
                        <tr>
                            <td width="120">Period</td>
                            <td>: <strong>{{ $periodLabel }}</strong></td>
                        </tr>
                        <tr>
                            <td>Printed</td>
                            <td>: {{ now()->format('d M Y H:i') }}</td>
                        </tr>
                    </table>
                </td>
                <td class="text-right">
                    <img src="{{ public_path('images/logo.jpg') }}" width="100" alt="Logo">
                </td>
            </tr>
        </table>
    </div>

    {{-- ORDER TABLE --}}
    <div class="section">
        <div class="section-title">Detail Transactions</div>

        <table style="width:100%; border-collapse:collapse; border:1px solid #999;">
            <thead>
                <tr style="background-color:#f2f2f2;">
                    <th style="border:1px solid #999; padding:6px; text-align:center;">No</th>
                    <th style="border:1px solid #999; padding:6px; text-align:left;">Invoice</th>
                    <th style="border:1px solid #999; padding:6px; text-align:center;">Date</th>
                    <th style="border:1px solid #999; padding:6px; text-align:left;">Product</th>
                    <th style="border:1px solid #999; padding:6px; text-align:center;">Quantity</th>
                    <th style="border:1px solid #999; padding:6px; text-align:right;">Total</th>
                    <th style="border:1px solid #999; padding:6px; text-align:center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td style="border:1px solid #999; padding:6px; text-align:center;">
                            {{ $loop->iteration }}
                        </td>
                        <td style="border:1px solid #999; padding:6px;">
                            {{ $order->invoice_number ?? $order->id }}
                        </td>
                        <td style="border:1px solid #999; padding:6px; text-align:center;">
                            {{ $order->created_at->format('d-m-Y') }}
                        </td>
                        <td style="border:1px solid #999; padding:6px;">
                            @foreach($order->items as $item)
                                {{ $item->product->name ?? '-' }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </td>
                        <td style="border:1px solid #999; padding:6px; text-align:center;">
                            {{ $order->items->sum('quantity') }}
                        </td>
                        <td style="border:1px solid #999; padding:6px; text-align:right;">
                            Rp{{ number_format($order->total ?? 0, 0, ',', '.') }}
                        </td>
                        <td style="border:1px solid #999; padding:6px; text-align:center;">
                            {{ strtoupper(str_replace('_', ' ', $order->status)) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="border:1px solid #999; padding:10px; text-align:center;">
                            No sales data available for this period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- TOTAL --}}
        <table style="width:100%; border-collapse:collapse; margin-top:6px;">
            <tr style="border-bottom:1px solid #000;">
                <td style="text-align:right; padding:6px; font-weight:bold;" width="80%">
                    Total Revenue
                </td>
                <td style="text-align:right; padding:6px; font-weight:bold;">
                    Rp{{ number_format($totalRevenue ?? 0, 0, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        This report is generated automatically by the system.
    </div>
</body>

</html>