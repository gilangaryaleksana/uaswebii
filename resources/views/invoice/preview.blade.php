<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $order->invoice_number }}</title>

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

        .total-box {
            margin-top: 10px;
            width: 100%;
        }

        .total-box td {
            padding: 6px;
        }

        .status {
            font-weight: bold;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 40px;
            font-size: 11px;
            text-align: center;
            color: #777;
        }
    </style>
    <style>
        @media print {
            body {
                background: white;
            }

            .print\:hidden {
                display: none !important;
            }

            @page {
                size: A4;
                margin: 20mm;
            }
        }
    </style>
</head>

<body>
    {{-- HEADER --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <div class="company">INVOICE</div>
                    <table class="no-border" style="margin-top:6px;">
                        <tr>
                            <td width="120">Invoice Number</td>
                            <td>: <strong>{{ $order->invoice_number }}</strong></td>
                        </tr>
                        <tr>
                            <td>Date</td>
                            <td>: {{ $order->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td class="status">
                                : {{ strtoupper(str_replace('_', ' ', $order->status)) }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="text-right">
                    {{-- Opsional --}}
                    <img src="https://preview.thenewsmarket.com/Previews/ADID/StillAssets/1920x1440/689347.jpg"
                        height="50">
                </td>
            </tr>
        </table>
    </div>

    {{-- CUSTOMER INFO --}}
    <div class="section">
        <div class="section-title">Customer</div>
        <table class="no-border">
            <tr>
                <td width="120">Name</td>
                <td>: {{ trim(($order->user->first_name ?? '') . ' ' . ($order->user->last_name ?? '')) ?: '-' }}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>: {{ $order->user->email ?? '-' }}</td>
            </tr>
            <tr>
                <td>Address</td>
                <td>: {{ $order->user->address ?? '-' }}</td>
            </tr>
        </table>
    </div>

    {{-- PAYMENT & SHIPPING --}}
    <div class="section">
        <div class="section-title">Invoice Information</div>
        <table class="no-border">
            <tr>
                <td width="150">Payment Method</td>
                <td>: {{ucfirst($order->payment->payment_method ?? '-' )}}</td>
            </tr>

            @if($order->shipping_service)
                <tr>
                    <td>Shipping Service</td>
                    <td>: {{ $order->shipping_service }}</td>
                </tr>
            @endif

            @if($order->tracking_number)
                <tr>
                    <td>Tracking Number</td>
                    <td>: {{ $order->tracking_number }}</td>
                </tr>
            @endif
        </table>
    </div>

    {{-- ORDER ITEMS --}}
    <div class="section">
        <div class="section-title">Order Items</div>
    
        <table style="width:100%; border-collapse:collapse; border:1px solid #999;">
            <thead>
                <tr style="background-color:#f2f2f2;">
                    <th style="border:1px solid #999; padding:6px; text-align:left;">
                        Product
                    </th>
                    <th style="border:1px solid #999; padding:6px; text-align:center;">
                        Size
                    </th>
                    <th style="border:1px solid #999; padding:6px; text-align:center;">
                        Qty
                    </th>
                    <th style="border:1px solid #999; padding:6px; text-align:right;">
                        Price
                    </th>
                    <th style="border:1px solid #999; padding:6px; text-align:right;">
                        Subtotal
                    </th>
                </tr>
            </thead>
    
            <tbody>
                @php $total = 0; @endphp
                @foreach($order->items as $item)
                    @php
    $price = $item->price;
    $subtotal = $price * $item->quantity;
    $total += $subtotal;
                    @endphp
                    <tr>
                        <td style="border:1px solid #999; padding:6px;">
                            {{ $item->product->name ?? '-' }}
                        </td>
                        <td style="border:1px solid #999; padding:6px; text-align:center;">
                            {{ $item->size->code ?? '-' }}
                        </td>
                        <td style="border:1px solid #999; padding:6px; text-align:center;">
                            {{ $item->quantity }}
                        </td>
                        <td style="border:1px solid #999; padding:6px; text-align:right;">
                            Rp{{ number_format($price) }}
                        </td>
                        <td style="border:1px solid #999; padding:6px; text-align:right;">
                            Rp{{ number_format($subtotal) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    
        {{-- TOTAL --}}
        <table style="width:100%; border-collapse:collapse; margin-top:6px;">
            <tr style="border-bottom:1px solid #000;">
                <td style="text-align:right; padding:6px; font-weight:bold;" width="80%">
                    Total
                </td>
                <td style="text-align:right; padding:6px; font-weight:bold;">
                    ${{ number_format($order->total ?? $total) }}
                </td>
            </tr>
        </table>
    </div>

    {{-- NOTES --}}
    @if($order->notes)
        <div class="section">
            <div class="section-title">Notes</div>
            <div>{{ $order->notes }}</div>
        </div>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        Thank you for your purchase.<br>
        This invoice is generated automatically.
    </div>

    <div class="print:hidden"
        style="position: fixed; bottom: 10px; right: 10px; display:flex; justify-content: end; gap: 1rem; align-items: center;">
        <a href="{{ url()->previous() }}" style="text-decoration: none; color: black;">
            Back
        </a>

        <button onclick="window.print()" style="padding: 12px; cursor: pointer;">
            Print Invoice
        </button>
    </div>
</body>

</html>