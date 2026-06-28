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
        <div class="bg-white rounded-xl w-full">
            <h2 class="text-sm font-bold mb-4 p-5">Orders</h2>

            {{-- Filter Status --}}
            <div class="px-5 mb-4 flex gap-2">
                <a href="{{ route('admin.dashboard.pendingOrder') }}"
                    class="text-xs px-3 py-1 rounded border {{ empty(request('status')) ? 'bg-gray-800 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    All ({{ $totalOrders }})
                </a>
                <a href="{{ route('admin.dashboard.pendingOrder', ['status' => 'pending']) }}"
                    class="text-xs px-3 py-1 rounded border {{ request('status') == 'pending' ? 'bg-yellow-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    Pending ({{ $pendingOrders }})
                </a>
                <a href="{{ route('admin.dashboard.pendingOrder', ['status' => 'paid']) }}"
                    class="text-xs px-3 py-1 rounded border {{ request('status') == 'paid' ? 'bg-orange-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    Paid ({{ $paidOrders }})
                </a>
                <a href="{{ route('admin.dashboard.pendingOrder', ['status' => 'shipped']) }}"
                    class="text-xs px-3 py-1 rounded border {{ request('status') == 'shipped' ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    Shipped ({{ $shippedOrders }})
                </a>
                <a href="{{ route('admin.dashboard.pendingOrder', ['status' => 'delivered']) }}"
                    class="text-xs px-3 py-1 rounded border {{ request('status') == 'delivered' ? 'bg-green-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    Delivered ({{ $deliveredOrders }})
                </a>
                <a href="{{ route('admin.dashboard.pendingOrder', ['status' => 'cancelled']) }}"
                    class="text-xs px-3 py-1 rounded border {{ request('status') == 'cancelled' ? 'bg-red-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    Cancelled ({{ $cancelledOrders }})
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-gray-500 border-b text-left">
                        <tr>
                            <th class="p-3">Order ID</th>
                            <th class="p-3">Customer ID</th>
                            <th class="p-3">Total</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Payment Method</th>
                            <th class="p-3">Date</th>
                            <th class="p-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr class="border-b">
                                <td class="p-3">#{{ $order->id }}</td>
                                <td class="p-3">{{ $order->user_id }}</td>
                                <td class="p-3">Rp {{ number_format($order->total, 2, ',', '.') }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-1 rounded text-xs
                                                        @if($order->status === 'pending') bg-yellow-100 text-yellow-600
                                                        @elseif($order->status === 'paid') bg-orange-100 text-orange-600
                                                        @elseif($order->status === 'shipped') bg-blue-100 text-blue-600
                                                        @elseif($order->status === 'delivered') bg-green-100 text-green-600
                                                        @else bg-red-100 text-red-600 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </td>
                                <td class="p-3 text-xs">
                                    {{ strtoupper($order->last_payment_method ?? '-') }}
                                </td>
                                <td class="p-3">{{ $order->created_at->format('d M Y') }}</td>
                                <td class="p-3 text-center">
                                    {{-- Icon Mata (semua status) --}}
                                    <button data-id="{{ $order->id }}" class="detailOpen text-gray-500 hover:text-black inline-block mb-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>

                                    @if ($order->status === 'cancelled')
                                        <span class="text-xs text-red-600 font-semibold block">Cancelled</span>

                                    @elseif ($order->status === 'delivered')
                                        <span class="text-xs text-green-600 font-semibold block">Delivered</span>

                                    @elseif ($order->status === 'pending')
                                        <form action="{{ route('admin.order.updateStatus', $order->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" onclick="return confirm('Yakin cancel order #{{ $order->id }}?')"
                                                class="bg-red-600 text-white text-xs px-3 py-2 rounded w-full mt-1">
                                                Cancel
                                            </button>
                                        </form>

                                    @elseif ($order->status === 'paid')
                                        <button data-id="{{ $order->id }}"
                                            class="verifyOpen bg-orange-600 text-white text-xs px-3 py-2 rounded w-full mt-1">
                                            Check Payment
                                        </button>

                                    @elseif ($order->status === 'shipped')
                                        <span class="text-xs text-blue-600 font-semibold block">On Delivery</span>

                                    @else
                                        <span class="text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-4 text-center text-gray-500">No orders yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Verify Modal --}}
                @foreach ($orders as $order)
                    <div id="verifyShow-{{ $order->id }}"
                        class="hidden bg-white rounded-xl border border-gray-200 p-6 w-[520px] font-sans"
                        style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); z-index:50;">

                        <div class="relative">

                            <button
                                class="verifyClose absolute top-4 right-0 bg-red-100 text-red-600 border border-red-200 rounded-lg px-3 py-1 text-xs"
                                data-id="{{ $order->id }}">✕</button>

                            <p class="text-sm font-semibold mb-5 text-black">Payment Verification</p>

                            <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs mb-5 border border-gray-200 p-4">
                                <span>Order ID</span><span>#{{ $order->id }}</span>
                                <span>Invoice</span><span>{{ $order->invoice_number ?? '-' }}</span>
                                <span>User</span><span>{{ $order->user->username }}</span>
                                <span>Name</span><span>{{ $order->first_name }}
                                    {{ $order->last_name }}</span>
                                <span>Email</span><span>{{ $order->email ?? '-' }}</span>
                                <span>Phone</span><span>{{ $order->phone ?? '-' }}</span>
                                <span>Address</span><span>{{ $order->address ?? '-' }}</span>
                                <span>Total</span><span class="font-medium">Rp
                                    {{ number_format($order->total, 0, ',', '.') }}</span>
                                <span>Status</span>
                                <span><span
                                        class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded">{{ ucfirst($order->status) }}</span></span>
                                <span>Payment method</span><span>{{ strtoupper($order->last_payment_method ?? '-') }}</span>
                                <span>Shipping courier</span><span>{{ $order->shipping_courier ?? '-' }}</span>
                                <span>Shipping
                                    cost</span><span>{{ $order->shipping_cost ? 'Rp ' . number_format($order->shipping_cost, 0, ',', '.') : '-' }}</span>
                                <span>Order date</span><span>{{ $order->created_at->format('d M Y, H:i') }}</span>
                                <span>Estimated Arrival</span>
                                <span>{{ $order->estimated_arrival ? \Carbon\Carbon::parse($order->estimated_arrival)->format('d M Y') : '-' }}</span>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4 text-xs mb-5 border">
                                <p class="text-black tracking-wider text-[11px] font-semibold mb-3">Midtrans
                                    payment
                                    info</p>
                                @if ($order->payment)
                                    <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                                        <span>Transaction ID</span>
                                        <span class="font-mono text-[11px]">{{ $order->payment->transaction_id ?? '-' }}</span>
                                        <span>Method</span>
                                        <span>{{ strtoupper($order->payment->payment_method ?? '-') }}</span>
                                        <span>Amount</span>
                                        <span>Rp
                                            {{ number_format($order->payment->payment_amount ?? $order->total, 0, ',', '.') }}</span>
                                        <span>Status</span>
                                        <span
                                            class="text-green-600 font-medium">{{ ucfirst($order->payment->payment_status ?? '-') }}</span>
                                        <span>Paid at</span>
                                        <span>{{ $order->payment->updated_at?->format('d M Y, H:i') ?? '-' }}</span>
                                    </div>
                                @else
                                    <p>Belum ada data pembayaran</p>
                                @endif
                            </div>

                            {{-- Order Items --}}
                            <div class="bg-gray-50 rounded-lg p-4 text-xs mb-5 border">
                                <p class="text-black tracking-wider text-[11px] font-semibold mb-3">Items Ordered</p>
                                @if ($order->orderItems->count())
                                    <table class="w-full">
                                        <thead>
                                            <tr class="text-gray-500 border-b">
                                                <th class="text-left py-1">Product</th>
                                                <th class="text-center py-1">Qty</th>
                                                <th class="text-right py-1">Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($order->orderItems as $item)
                                                <tr class="border-b last:border-0">
                                                    <td class="py-1">{{ $item->product->name ?? $item->product_name ?? '-' }}</td>
                                                    <td class="text-center py-1">{{ $item->quantity }}</td>
                                                    <td class="text-right py-1">Rp {{ number_format($item->price, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-gray-400">Tidak ada item</p>
                                @endif
                            </div>

                            <form action="{{ route('admin.order.updateStatus', $order->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="shipped">
                                <button type="submit"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white text-xs font-medium py-3 rounded-lg transition">
                                    ✔ Paid — Mark as Shipped
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach

                {{-- Detail Modal --}}
                @foreach ($orders as $order)
                    <div id="detailShow-{{ $order->id }}"
                        class="hidden bg-white rounded-xl border border-gray-200 p-6 w-[520px] font-sans overflow-y-auto max-h-[80vh]"
                        style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); z-index:50;">

                        <div class="relative">
                            <button
                                class="detailClose absolute top-0 right-0 bg-red-100 text-red-600 border border-red-200 rounded-lg px-3 py-1 text-xs"
                                data-id="{{ $order->id }}">✕</button>

                            <p class="text-sm font-semibold mb-5 text-black">Order Detail #{{ $order->id }}</p>

                            {{-- Order Info --}}
                            <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs mb-5 border border-gray-200 p-4">
                                <span>Order ID</span><span>#{{ $order->id }}</span>
                                <span>Invoice</span><span>{{ $order->invoice_number ?? '-' }}</span>
                                <span>Customer</span><span>{{ $order->first_name }} {{ $order->last_name }}</span>
                                <span>Email</span><span>{{ $order->email ?? '-' }}</span>
                                <span>Phone</span><span>{{ $order->phone ?? '-' }}</span>
                                <span>Address</span><span>{{ $order->address ?? '-' }}</span>
                                <span>Status</span>
                                <span>
                                    <span class="px-2 py-0.5 rounded text-xs
                                            @if($order->status === 'pending') bg-yellow-100 text-yellow-600
                                            @elseif($order->status === 'paid') bg-orange-100 text-orange-600
                                            @elseif($order->status === 'shipped') bg-blue-100 text-blue-600
                                            @elseif($order->status === 'delivered') bg-green-100 text-green-600
                                            @else bg-red-100 text-red-600 @endif">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </span>
                                <span>Payment Method</span><span>{{ strtoupper($order->last_payment_method ?? '-') }}</span>
                                <span>Shipping Courier</span><span>{{ $order->shipping_courier ?? '-' }}</span>
                                <span>Shipping
                                    Cost</span><span>{{ $order->shipping_cost ? 'Rp ' . number_format($order->shipping_cost, 0, ',', '.') : '-' }}</span>
                                <span>Estimated Arrival</span>
                                <span>{{ $order->estimated_arrival ? \Carbon\Carbon::parse($order->estimated_arrival)->format('d M Y') : '-' }}</span>
                                <span>Order Date</span><span>{{ $order->created_at->format('d M Y, H:i') }}</span>
                                <span class="font-semibold">Total</span>
                                <span class="font-semibold">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                            </div>

                            {{-- Order Items --}}
                            <div class="bg-gray-50 rounded-lg p-4 text-xs border">
                                <p class="text-black tracking-wider text-[11px] font-semibold mb-3">Items Ordered</p>
                                @if ($order->orderItems->count())
                                    <table class="w-full">
                                        <thead>
                                            <tr class="text-gray-500 border-b">
                                                <th class="text-left py-1">Product</th>
                                                <th class="text-center py-1">Size</th>
                                                <th class="text-center py-1">Qty</th>
                                                <th class="text-right py-1">Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($order->orderItems as $item)
                                                <tr class="border-b last:border-0">
                                                    <td class="py-1">{{ $item->product->name ?? '-' }}</td>
                                                    <td class="text-center py-1">{{ $item->size->code ?? '-' }}</td>
                                                    <td class="text-center py-1">{{ $item->quantity }}</td>
                                                    <td class="text-right py-1">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-gray-400">Tidak ada item</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                <script>
                    document.querySelectorAll('.verifyOpen').forEach(btn => {
                        btn.addEventListener('click', () => {
                            document.getElementById('verifyShow-' + btn.dataset.id).classList.remove('hidden');
                        });
                    });

                    document.querySelectorAll('.verifyClose').forEach(btn => {
                        btn.addEventListener('click', () => {
                            document.getElementById('verifyShow-' + btn.dataset.id).classList.add('hidden');
                        });
                    });

                    document.querySelectorAll('.detailOpen').forEach(btn => {
                            btn.addEventListener('click', () => {
                                document.getElementById('detailShow-' + btn.dataset.id).classList.remove('hidden');
                            });
                        });

                        document.querySelectorAll('.detailClose').forEach(btn => {
                            btn.addEventListener('click', () => {
                                document.getElementById('detailShow-' + btn.dataset.id).classList.add('hidden');
                            });
                        });
                </script>
            </div>
        </div>
    </div>
</body>