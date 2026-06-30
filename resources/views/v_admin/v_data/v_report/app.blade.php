@include('base.start')
@include('base.navbar')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.css">
    @stack('styles')
</head>

<body>
    <div class="flex h-screen relative">
        @include('base.sidebar')
        <div class="p-6 w-full">
            <h1 class="text-xl font-semibold mb-4">Sales report</h1>

            <form method="GET" action="{{ route('admin.dashboard.salesReport') }}"
                class="flex gap-3 items-end mb-6 flex-wrap">
                <div>
                    <label class="block text-xs mb-1">Period Type</label>
                    <select name="type" id="type" class="border rounded p-2 text-sm">
                        <option value="daily" {{ $type == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="monthly" {{ $type == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="yearly" {{ $type == 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>
                </div>

                <div id="dailyInput" class="{{ $type != 'daily' ? 'hidden' : '' }}">
                    <label class="block text-xs mb-1">Date</label>
                    <input type="date" name="date" value="{{ $date }}" class="border rounded p-2 text-sm">
                </div>

                <div id="monthlyInput" class="{{ $type != 'monthly' ? 'hidden' : '' }}">
                    <label class="block text-xs mb-1">Month</label>
                    <input type="month" name="month" value="{{ $month }}" class="border rounded p-2 text-sm">
                </div>

                <div id="yearlyInput" class="{{ $type != 'yearly' ? 'hidden' : '' }}">
                    <label class="block text-xs mb-1">Year</label>
                    <input type="number" name="year" value="{{ $year }}" min="2020" max="2100"
                        class="border rounded p-2 text-sm w-24">
                </div>

                <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded text-sm">Show</button>

                <a href="{{ route('admin.dashboard.salesReport.export', request()->query()) }}"
                    class="bg-gray-800 text-white px-4 py-2 rounded text-sm">
                    Export PDF
                </a>
            </form>

            <div class="bg-white rounded shadow p-4 mb-4">
                <p class="text-sm text-gray-600">Total Revenue</p>
                <p class="text-2xl font-bold text-teal-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            </div>

            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-left">
                        <th class="p-2 border text-center">No</th>
                        <th class="p-2 border text-center">Invoice</th>
                        <th class="p-2 border text-center">Date</th>
                        <th class="p-2 border text-center">Product</th>
                        <th class="p-2 border text-center">Quantity</th>
                        <th class="p-2 border text-center">Total</th>
                        <th class="p-2 border text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="p-2 border text-center">{{ $loop->iteration }}</td>
                            <td class="p-2 border">{{ $order->invoice_number ?? $order->id }}</td>
                            <td class="p-2 border text-center">{{ $order->created_at->format('d-m-Y') }}</td>
                            <td class="p-2 border">
                                @foreach($order->items as $item)
                                    {{ $item->product->name ?? '-' }}{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </td>
                            <td class="p-2 border text-center">{{ $order->items->sum('quantity') }}</td>
                            <td class="p-2 border text-right">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td class="p-2 border text-center capitalize">{{ $order->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">No sales data available for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <script>
            document.getElementById('type').addEventListener('change', function () {
                document.getElementById('dailyInput').classList.add('hidden');
                document.getElementById('monthlyInput').classList.add('hidden');
                document.getElementById('yearlyInput').classList.add('hidden');
                document.getElementById(this.value + 'Input').classList.remove('hidden');
            });
        </script>
    </div>
</body>