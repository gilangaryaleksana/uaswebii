<div class="md:pt-[10rem] pt-4 px-5 container space-y-5">
    <a href="{{ route('user.order') }}"
        class="group inline-flex items-center gap-2 hover:text-black text-gray-600! px-6 py-2 border-b-white border-b-1 hover:border-b hover:border-gray-600 duration-200">
        <svg class="w-5 h-5 transform transition-all duration-300 -translate-x-2 group-hover:translate-x-0" fill="none"
            stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Orders
    </a>
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- <!-- User Details -->
        <div class="bg-gray-100 p-4 rounded-lg">
            <h2 class="text-xl font-semibold mb-4">User Details</h2>
            <div class="grid grid-cols-2 gap-4 select-none">
                <div>
                    <label class="block mb-1 font-semibold">First Name</label>
                    <input type="text" value="{{ auth()->user()->first_name }}" class="px-3 py-2 w-full outline-none "
                        readonly>
                </div>
                <div>
                    <label class="block mb-1 font-semibold">Last Name</label>
                    <input type="text" value="{{ auth()->user()->last_name }}" class="px-3 py-2 w-full outline-none "
                        readonly>
                </div>
            </div>
            <div class="mt-4 space-y-4">
                <div>
                    <label class="block mb-1 font-semibold">Email</label>
                    <input type="email" value="{{ auth()->user()->email }}" class="px-3 py-2 w-full outline-none "
                        readonly>
                </div>
                <div>
                    <label class="block mb-1 font-semibold">Phone</label>
                    <input type="text" value="{{ auth()->user()->phone }}" class="px-3 py-2 w-full outline-none "
                        readonly>
                </div>
                <div>
                    <label class="block mb-1 font-semibold">Address</label>
                    <textarea rows="3" class="px-3 py-2 w-full outline-none "
                        readonly>{{ auth()->user()->address }}</textarea>
                </div>
            </div>
        </div> --}}

        <!-- Order & Payment Details -->
        <div class="bg-gray-100 p-4">
            <h2 class="text-xl font-semibold mb-4">Order #{{ $order->id }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                <div class="grid grid-cols-[140px_1fr] gap-2">
                    <span class="font-semibold">Status</span>
                    <span class="capitalize">: {{ $order->status }}</span>
                </div>

                <div class="grid grid-cols-[140px_1fr] gap-2">
                    <span class="font-semibold">Order Date</span>
                    <span>: {{ $order->created_at->format('d M Y') }}</span>
                </div>

                <div class="grid grid-cols-[140px_1fr] gap-2">
                    <span class="font-semibold">Total Price</span>
                    <span>: Rp{{ number_format($order->total, 2, ',', '.') }}</span>
                </div>

                @if(!empty($order->payment))
                    <div class="grid grid-cols-[140px_1fr] gap-2">
                        <span class="font-semibold">Payment</span>
                        <span>
                            : {{ ucfirst($order->payment->payment_method) }}

                            @if (
        $order->status !== 'cancelled' &&
        $order->payment->payment_method === 'ewallet'
    )
                                <a href="{{ route('payment.qrcode', $order->id) }}" class="hover:text-gray-600! ml-1">
                                    (Pay)
                                </a>
                            @endif
                        </span>
                    </div>
                @endif

                <div class="grid grid-cols-[140px_1fr] gap-2">
                    <span class="font-semibold">Shipped At</span>
                    <span>: {{ $order->shippingCalendar?->shipped_at?->format('d M Y') ?? '-' }}</span>
                </div>

                <div class="grid grid-cols-[140px_1fr] gap-2">
                    <span class="font-semibold">Estimated Arrival</span>
                    <span>:
                        {{ $order->estimated_arrival ? \Carbon\Carbon::parse($order->estimated_arrival)->format('d M Y') : '-' }}</span>
                </div>
            </div>

        </div>

        <!-- Order Items -->
        <div class="bg-white p-4 space-y-6">

            {{-- Tabel produk tetap pakai scroll di mobile --}}
            <div class="overflow-x-auto">
                <table class="w-full min-w-[500px] border-collapse">
                    <thead class="bg-black text-white text-sm">
                        <tr>
                            <th class="p-2 text-left">Product</th>
                            <th class="p-2 text-left">Price</th>
                            <th class="p-2 text-left">Quantity</th>
                            <th class="p-2 text-left">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @foreach($order->items as $item)
                            <tr class="border-b border-gray-200">
                                <td class="p-2 flex items-center gap-2">
                                    @if($item->product && $item->product->image)
                                        <img src="{{ $item->product->image }}" alt="{{ $item->product->name }}"
                                            class="w-12 h-12 object-cover">
                                    @endif
                                    {{ $item->product->name }}
                                    <span class="text-xs text-gray-500">({{ $item->size->code ?? '-' }})</span>
                                </td>
                                <td class="p-2">Rp{{ number_format($item->price, 2, ',', '.') }}</td>
                                <td class="p-2">{{ $item->quantity }}</td>
                                <td class="p-2">Rp{{ number_format($item->price * $item->quantity, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Customer Info — card di mobile, tabel di desktop --}}
            <div>
                <h3 class="text-sm font-semibold mb-3">Customer Information</h3>

                {{-- Desktop --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full min-w-[500px] border-collapse">
                        <thead class="bg-black text-white text-sm">
                            <tr>
                                <th class="p-2 text-left">First Name</th>
                                <th class="p-2 text-left">Last Name</th>
                                <th class="p-2 text-left">Email</th>
                                <th class="p-2 text-left">Phone</th>
                                <th class="p-2 text-left">Address</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <tr class="border-b border-gray-200 text-xs">
                                <td class="p-2"><input type="text" value="{{ auth()->user()->first_name }}"
                                        class="px-3 py-2 w-full outline-none" readonly></td>
                                <td class="p-2"><input type="text" value="{{ auth()->user()->last_name }}"
                                        class="px-3 py-2 w-full outline-none" readonly></td>
                                <td class="p-2"><input type="email" value="{{ auth()->user()->email }}"
                                        class="px-3 py-2 w-full outline-none" readonly></td>
                                <td class="p-2"><input type="text" value="{{ auth()->user()->phone }}"
                                        class="px-3 py-2 w-full outline-none" readonly></td>
                                <td class="p-2">
                                    <div class="px-3 py-2 w-full h-20 overflow-auto flex items-center">
                                        {{ auth()->user()->province_name }}, {{ auth()->user()->city_name }},
                                        {{ auth()->user()->address }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Mobile --}}
                <div class="md:hidden border p-4 text-sm space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-500">First Name</span>
                        <span class="font-medium">{{ auth()->user()->first_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Last Name</span>
                        <span class="font-medium">{{ auth()->user()->last_name }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500 shrink-0">Email</span>
                        <span class="font-medium text-right break-all">{{ auth()->user()->email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Phone</span>
                        <span class="font-medium">{{ auth()->user()->phone }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-gray-500">Address</span>
                        <span class="font-medium">{{ auth()->user()->province_name }}, {{ auth()->user()->city_name }},
                            {{ auth()->user()->address }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col md:flex-row gap-2 py-5 justify-end">
            @if($order->status === 'pending')
                {{-- Tombol Bayar --}}
                <a href="{{ route('checkout.repay', $order->id) }}"
                    class="bg-black text-white! text-sm px-6 py-2 border border-black hover:bg-white hover:text-black! text-center cursor-pointer">
                    Bayar Sekarang
                </a>

                {{-- Tombol Cancel --}}
                <form action="{{ route('order.cancel', $order->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="bg-black text-white text-sm w-full md:px-6 py-2 border-white border-1 hover:bg-red-100 hover:text-red-500 hover:border-black cursor-pointer">
                        Cancel Order
                    </button>
                </form>
            @endif

            @if ($order->status === 'shipped')
                <div class="flex flex-col justify-center items-end gap-2">
                    <p class="text-xs text-gray-500 text-right">
                        Have you received your order? Click the button below to confirm.
                    </p>
                    <form action="{{ route('orders.received', $order->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" onclick="return confirm('Order confirmation received?')"
                            class="bg-black text-white! text-sm px-6 py-2 border border-black hover:bg-white hover:text-black! text-center cursor-pointer">
                            Receive goods
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>