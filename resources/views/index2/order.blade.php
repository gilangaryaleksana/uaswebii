@extends('v_user.v_order.app')

@section('content-order')
    <div class="md:pt-[14rem] md:w-5xl pt-4 px-5 w-full flex-1 overflow-y-auto">

        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($orders->isEmpty())
            <p>You have no orders yet.</p>
        @else
            <h1 class="text-2xl font-bold mb-6">Your Orders</h1>
            <div class="overflow-x-auto">
                {{-- Desktop Table --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-black text-white text-xs">
                                <th class="p-2">Order ID</th>
                                <th class="p-2">Total Price</th>
                                <th class="p-2">Status</th>
                                <th class="p-2">Date</th>
                                <th class="p-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr class="text-center border-b-gray-200 border-b-1 text-xs">
                                    <td class="p-2">{{ $order->id }}</td>
                                    <td class="p-2">Rp{{ number_format($order->total, 2, ',', '.') }}</td>
                                    <td class="p-2 capitalize">{{ $order->status }}</td>
                                    <td class="p-2">{{ $order->created_at->format('d M Y H:i') }}</td>
                                    <td class="p-2 flex justify-center gap-2">
                                        <a href="{{ route('order.show', $order->id) }}"
                                            class="bg-black text-white! text-sm px-6 py-2 border-white border-1 hover:bg-white hover:text-black! hover:border-black focus:bg-white focus:text-black! focus:border-black">Detail</a>
                                        @if($order->status === 'pending')
                                            <form action="{{ route('order.cancel', $order->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="bg-black text-white text-sm px-6 py-2 border-white border-1 hover:bg-red-100 hover:text-red-500 hover:border-black cursor-pointer">Cancel</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Card --}}
                <div class="md:hidden space-y-3">
                    @foreach($orders as $order)
                        <div class="border p-4 text-sm space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Order ID</span>
                                <span class="font-medium">#{{ $order->id }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Total</span>
                                <span class="font-medium">Rp{{ number_format($order->total, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Status</span>
                                <span class="capitalize font-medium">{{ $order->status }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Date</span>
                                <span>{{ $order->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <div class="flex gap-2 pt-1">
                                <a href="{{ route('order.show', $order->id) }}"
                                    class="flex-1 text-center bg-black text-white! text-xs px-4 py-2 border border-black hover:bg-white hover:text-black!">Detail</a>
                                @if($order->status === 'pending')
                                    <form action="{{ route('order.cancel', $order->id) }}" method="POST" class="flex-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="w-full bg-black text-white text-xs px-4 py-2 border border-black hover:bg-red-100 hover:text-red-500 hover:border-black cursor-pointer">Cancel</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection