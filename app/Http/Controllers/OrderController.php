<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use App\Mail\PaymentLinkMail;
use Illuminate\Support\Facades\Mail;
use App\Jobs\CancelOrderJob;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('index2.order', compact('orders'));
    }
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        $order->update([
            'status' => $request->status
        ]);

        if ($request->status === 'shipped') {
            \App\Models\ShippingCalendar::create([
                'order_id'          => $order->id,
                'user_id'           => $order->user_id,
                'title'             => 'Order #' . $order->id,
                'shipped_at'        => now(),
                'estimated_arrival' => $order->estimated_arrival,
                'delivered_at'      => null,
                'status'            => 'shipped',
            ]);
        }

        return back()->with('success', 'Status order berhasil diperbarui');
    }
    public function createFromCart()
    {
        $user = auth()->user();

        $cartItems = Cart::with('product')
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Keranjang masih kosong!');
        }

        DB::transaction(function () use ($user, $cartItems) {

            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'payment_expired_at' => now()->addSeconds(30),
                'total' => null,
            ]);

            foreach ($cartItems as $item) {

                $order->items()->create([
                    'product_id' => $item->product_id,
                    'size_id' => $item->size_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);

                if ($item->size_id) {
                    DB::table('product_sizes')
                        ->where('product_id', $item->product_id)
                        ->where('size_id', $item->size_id)
                        ->decrement('stock', $item->quantity);
                } else {
                    DB::table('products')
                        ->where('id', $item->product_id)
                        ->decrement('stock', $item->quantity);
                }
            }

            Cart::where('user_id', $user->id)->delete();
        });

        return redirect()->route('user.order')
            ->with('success', 'Order berhasil dibuat, silakan lakukan pembayaran.');
    }
    public function cancel(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return back()->with('error', 'Order tidak bisa dibatalkan');
        }

        DB::transaction(function () use ($order) {
            $order->load('items');

            foreach ($order->items as $item) {
                if ($item->size_id) {
                    DB::table('product_sizes')
                        ->where('product_id', $item->product_id)
                        ->where('size_id', $item->size_id)
                        ->increment('stock', $item->quantity);
                } else {
                    DB::table('products')
                        ->where('id', $item->product_id)
                        ->increment('stock', $item->quantity);
                }
            }

            $order->update(['status' => 'cancelled']);
        });

        return back()->with('success', 'Order dibatalkan & stok dikembalikan');
    }
    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items.product');

        return view('v_user.v_order.detail', compact('order'));
    }
    public function sendPaymentEmail($orderId)
    {
        $order = Order::with('user')->findOrFail($orderId);

        if ($order->status !== 'pending') {
            return back()->with('error', 'Order sudah diproses.');
        }

        $paymentLink = route('payment.confirm', $order->id);

        Mail::to($order->user->email)
            ->send(new PaymentLinkMail($order, $paymentLink));

        if (!$order->payment_expired_at) {
            $order->update([
                'status' => 'pending',
                'payment_expired_at' => now()->addMinutes(15),
            ]);

            CancelOrderJob::dispatch($order->id)
                ->delay($order->payment_expired_at);
        }

        return back()->with('success', 'Instruksi pembayaran berhasil dikirim ke email.');
    }

    public function markReceived($id)
    {
        $order = Order::where('id', $id)
                    ->where('user_id', auth()->id())
                    ->firstOrFail();

        if ($order->status !== 'shipped') {
            return back()->with('error', 'Order belum dikirim.');
        }

        $order->update(['status' => 'delivered']);

        return back()->with('success', 'Pesanan dikonfirmasi diterima!');
    }
}
