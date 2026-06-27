<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Notification;
use Midtrans\Snap;

class CheckoutController extends Controller
{
    public function __construct()
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    public function index()
    {
        $user = Auth::user();

        $cartItems = $this->getCheckoutCartItems();

        if ($cartItems->isEmpty()) {
            return redirect()
                ->route('beranda')
                ->with('error', 'Keranjang kosong, silakan pilih produk terlebih dahulu.');
        }

        foreach ($cartItems as $item) {
            $item->additional_price = 0;

            // ✅ Ganti product_id → product->id (support stdClass dari getBuyNowItem)
            $productId = $item->product->id ?? null;
            $sizeId    = $item->size_id ?? null;

            if ($productId && $sizeId) {
                $pivot = DB::table('product_sizes')
                    ->where('product_id', $productId)
                    ->where('size_id', $sizeId)
                    ->first();

                if ($pivot) {
                    $item->additional_price = $pivot->additional_price;
                }
            }
        }

        $source = session('order_source') === 'product' ? 'product' : 'cart';
        return view('v_user.v_checkout.app', compact('cartItems', 'source'));
    }

    public function selectItems(Request $request)
    {
        $request->validate([
            'selected_items'   => 'required|array|min:1',
            'selected_items.*' => 'exists:carts,id',
        ]);

        // Pastikan semua cart ID milik user yang login
        $validIds = Cart::where('user_id', auth()->id())
            ->whereIn('id', $request->selected_items)
            ->pluck('id')
            ->toArray();

        if (empty($validIds)) {
            return back()->with('error', 'Pilih minimal 1 produk untuk checkout.');
        }

        session(['selected_cart_ids' => $validIds]);

        return redirect()->route('checkout');
    }

    public function processCheckout(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|email',
            'phone'      => 'required|string',
            'address'    => 'required|string',
        ]);

        $user = Auth::user();

        $cartItems = $this->getCheckoutCartItems();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Cart kosong');
        }

        DB::beginTransaction();

        try {
            $total = 0;
            foreach ($cartItems as $item) {
                $price = $this->getItemPrice($item);
                $total += $price * $item->quantity;
            }

            $shippingCost = (int) ($request->shipping_cost ?? 0);
            $total += $shippingCost;

            $order = Order::create([
                'user_id'             => $user->id,
                'total'               => $total,
                'status'              => 'pending',
                'first_name'          => $request->first_name,
                'last_name'           => $request->last_name,
                'email'               => $request->email,
                'phone'               => $request->phone,
                'address'             => $request->address,
                'payment_method'      => 'midtrans',
                'shipping_courier'    => $request->shipping_courier,
                'shipping_service'    => $request->shipping_service,
                'shipping_cost'       => $shippingCost,
                'estimated_arrival'   => $request->estimated_arrival,
                'last_payment_method' => $request->payment_method,
            ]);

        foreach ($cartItems as $item) {
            $price = $this->getItemPrice($item);
            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $item->product->id,
                'size_id'    => $item->size_id,
                'quantity'   => $item->quantity,
                'price'      => $price,
            ]);
        }

        Payment::create([
            'order_id'       => $order->id,
            'payment_method' => 'midtrans',
            'payment_amount' => $total,
            'payment_status' => 'pending',
        ]);

            // Build item details untuk Midtrans
            $itemDetails = [];
            foreach ($cartItems as $item) {
                $price = $this->getItemPrice($item);
                $itemDetails[] = [
                    'id'       => $item->product->id,
                    'price'    => (int) $price,
                    'quantity' => $item->quantity,
                    'name'     => substr($item->product->name, 0, 50),
                ];
            }

            if ($shippingCost > 0) {
                $itemDetails[] = [
                    'id'       => 'SHIPPING',
                    'price'    => $shippingCost,
                    'quantity' => 1,
                    'name'     => 'Ongkos Kirim ' . ($request->shipping_courier ?? ''),
                ];
            }

            $params = [
                'transaction_details' => [
                    'order_id'     => $order->id . '-' . time(),
                    'gross_amount' => (int) $total,
                ],
                'customer_details' => [
                    'first_name' => $request->first_name,
                    'last_name'  => $request->last_name,
                    'email'      => $request->email,
                    'phone'      => $request->phone,
                ],
                'item_details' => $itemDetails,
            ];

            $snapToken = Snap::getSnapToken($params);

            DB::commit();

            $this->clearCheckedOutCart($user);
            Session::forget(['order_source', 'product_id', 'size_id', 'quantity', 'selected_cart_ids']);

            return response()->json([
                'snap_token' => $snapToken,
                'order_id'   => $order->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleNotification(Request $request)
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');

        try {
            $notification = new Notification();
        } catch (\Throwable $e) {
            Log::error('Midtrans notification invalid payload', [
                'error'   => $e->getMessage(),
                'payload' => $request->getContent(),
            ]);
            return response()->json(['message' => 'Invalid Midtrans notification payload'], 400);
        }

        $orderId           = $notification->order_id;
        $transactionStatus = $notification->transaction_status;
        $fraudStatus       = $notification->fraud_status;

        preg_match('/(\d+)-\d+$/', $orderId, $matches);
        $realOrderId = $matches[1] ?? $orderId;

        $order = Order::with('orderItems')->find($realOrderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Update payment dengan transaction_id dari Midtrans
        Payment::where('order_id', $order->id)->update([
            'transaction_id'     => $notification->transaction_id,
            'payment_type'       => $notification->payment_type,
            'transaction_status' => $notification->transaction_status,
            'transaction_time'   => $notification->transaction_time,
            'settlement_time'    => $notification->settlement_time ?? null,
            'fraud_status'       => $notification->fraud_status ?? null,
            'issuer'             => $notification->issuer ?? null,
            'acquirer'           => $notification->acquirer ?? null,
            'currency'           => $notification->currency ?? null,
            'payment_status'     => $transactionStatus,
        ]);

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                $order->update(['status' => 'paid']);
                $this->decrementStock($order);
            }
        } elseif ($transactionStatus == 'settlement') {
            $order->update(['status' => 'paid']);
            $this->decrementStock($order);
        } elseif ($transactionStatus == 'pending') {
            $order->update(['status' => 'pending']);
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $order->update(['status' => 'cancelled']);
        }

        return response()->json(['message' => 'OK']);
    }

    public function buyNow(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'size_id'    => 'nullable|exists:sizes,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        // Simpan ke session, tidak perlu manipulasi cart
        session([
            'order_source' => 'product',
            'product_id'   => $request->product_id,
            'size_id'      => $request->size_id,
            'quantity'     => $request->quantity,
            'product_url'    => url()->previous(),
        ]);

        return redirect()->route('checkout');
    }

    public function createQris(Request $request)
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $order = Order::findOrFail($request->order_id);

        $params = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id'     => 'QRIS-' . $order->id . '-' . time(),
                'gross_amount' => (int) $order->total,
            ],
            'qris' => [
                'acquirer' => 'gopay'
            ]
        ];

        $response = CoreApi::charge($params);

        $qrUrl = $response->actions[0]->url ?? null;

        return response()->json([
            'qr_url'   => $qrUrl,
            'order_id' => $order->id,
        ]);
    }

    public function createBcaVa(Request $request)
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $order = Order::findOrFail($request->order_id);

        $params = [
            'payment_type' => 'bank_transfer',
            'transaction_details' => [
                'order_id'     => 'BCA-' . $order->id . '-' . time(),
                'gross_amount' => (int) $order->total,
            ],
            'bank_transfer' => [
                'bank' => 'bca'
            ],
            'customer_details' => [
                'first_name' => $order->first_name,
                'last_name'  => $order->last_name,
                'email'      => $order->email,
                'phone'      => $order->phone,
            ],
        ];

        $response = CoreApi::charge($params);

        $vaNumber = $response->va_numbers[0]->va_number ?? null;

        return response()->json([
            'va_number' => $vaNumber,
            'bank'      => 'BCA',
            'order_id'  => $order->id,
            'total'     => $order->total,
        ]);
    }

    public function createMandiriVa(Request $request)
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $order = Order::findOrFail($request->order_id);

        $params = [
            'payment_type' => 'echannel',
            'transaction_details' => [
                'order_id'     => 'MANDIRI-' . $order->id . '-' . time(),
                'gross_amount' => (int) $order->total,
            ],
            'echannel' => [
                'bill_info1' => 'Payment for order',
                'bill_info2' => (string) $order->id,
            ],
        ];

        $response = CoreApi::charge($params);

        return response()->json([
            'bill_key'    => $response->bill_key,
            'biller_code' => $response->biller_code,
            'total'       => $order->total,
            'order_id'    => $order->id,
        ]);
    }

    public function createAlfamartVa(Request $request)
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $order = Order::findOrFail($request->order_id);

        $params = [
            'payment_type' => 'cstore',
            'transaction_details' => [
                'order_id'     => 'ALFAMART-' . $order->id . '-' . time(),
                'gross_amount' => (int) $order->total,
            ],
            'cstore' => [
                'store'   => 'alfamart',
                'message' => 'Payment for order ' . $order->id,
            ],
        ];

        $response = CoreApi::charge($params);

        return response()->json([
            'payment_code' => $response->payment_code,
            'total'        => $order->total,
            'order_id'     => $order->id,
        ]);
    }

    public function createIndomaretVa(Request $request)
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $order = Order::findOrFail($request->order_id);

        $params = [
            'payment_type' => 'cstore',
            'transaction_details' => [
                'order_id'     => 'INDOMARET-' . $order->id . '-' . time(),
                'gross_amount' => (int) $order->total,
            ],
            'cstore' => [
                'store'   => 'indomaret',
                'message' => 'Payment for order ' . $order->id,
            ],
        ];

        $response = CoreApi::charge($params);

        return response()->json([
            'payment_code' => $response->payment_code,
            'total'        => $order->total,
            'order_id'     => $order->id,
        ]);
    }

    public function cancelBuyNow()
    {
        Session::forget(['order_source', 'product_id', 'size_id', 'quantity']);
        return redirect()->back();
    }

    public function repay($orderId)
    {
        $order = Order::with('orderItems.product', 'orderItems.size')->findOrFail($orderId);

        // Pastikan order milik user yang login
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // Pastikan masih pending
        if ($order->status !== 'pending') {
            return redirect()->route('user.order')->with('error', 'Order ini sudah tidak bisa dibayar.');
        }

        // Generate snap token baru
        $params = [
            'transaction_details' => [
                'order_id'     => $order->id . '-' . time(), // hindari duplicate order_id
                'gross_amount' => (int) $order->total,
            ],
            'customer_details' => [
                'first_name' => $order->first_name,
                'last_name'  => $order->last_name,
                'email'      => $order->email,
                'phone'      => $order->phone,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        return view('v_user.v_order.repay', compact('order', 'snapToken'));
    }

    /**
     * Ambil cart items yang relevan untuk proses checkout saat ini.
     *
     * - Jika order_source == 'product' (flow "Buy Now"), ambil item buy-now dari session.
     * - Jika ada selected_cart_ids di session (user mencentang sebagian item di halaman cart),
     *   filter hanya item tersebut.
     * - Jika tidak ada selected_cart_ids (fallback / flow lama), ambil semua cart milik user.
     *
     * Dipakai bersama oleh index() dan processCheckout() agar kedua method selalu
     * konsisten dan tidak terulang bug "item yang tidak dicentang ikut ke-checkout".
     */
    private function getCheckoutCartItems()
    {
        if (session('order_source') === 'product') {
            return $this->getBuyNowItem();
        }

        $selectedIds = session('selected_cart_ids');

        $query = Cart::with(['product', 'size'])
            ->where('user_id', Auth::id());

        if ($selectedIds) {
            $query->whereIn('id', $selectedIds);
        }

        return $query->get();
    }

    /**
     * Hapus cart items yang baru saja berhasil di-checkout.
     *
     * - Flow "Buy Now" tidak punya cart row yang perlu dihapus di sini (sudah ditangani terpisah).
     * - Jika ada selected_cart_ids, hapus HANYA item yang dipilih (bukan semua cart user).
     * - Jika tidak ada selected_cart_ids (fallback / flow lama), hapus semua cart user.
     */
    private function clearCheckedOutCart($user)
    {
        if (session('order_source') === 'product') {
            return;
        }

        $selectedIds = session('selected_cart_ids');

        if ($selectedIds) {
            Cart::where('user_id', $user->id)
                ->whereIn('id', $selectedIds)
                ->delete();
        } else {
            Cart::where('user_id', $user->id)->delete();
        }

        Session::forget('selected_cart_ids');
    }

    private function getBuyNowItem()
    {
        $product  = Product::find(session('product_id'));
        $sizeId   = session('size_id');
        $quantity = session('quantity') ?? 1;

        if (!$product) {
            return collect([]);
        }

        $size = $sizeId ? Size::find($sizeId) : null;

        $additional_price = 0;
        if ($sizeId) {
            $additional_price = DB::table('product_sizes')
                ->where('product_id', $product->id)
                ->where('size_id', $sizeId)
                ->value('additional_price') ?? 0;
        }

        return collect([(object)[
            'product'          => $product,
            'size'             => $size,
            'size_id'          => $sizeId,
            'quantity'         => $quantity,
            'additional_price' => $additional_price,
        ]]);
    }

    private function getItemPrice($item)
    {
        $base = $item->product->price ?? 0;

        if (!$item->size_id) {
            return $base;
        }

        $additional_price = DB::table('product_sizes')
            ->where('product_id', $item->product->id)
            ->where('size_id', $item->size_id)
            ->value('additional_price') ?? 0;

        return $base + $additional_price;
    }

    private function decrementStock(Order $order)
    {
        // Cegah double decrement kalau notif datang 2x
        if ($order->stock_decremented) {
            return;
        }

        foreach ($order->orderItems as $item) {
            if ($item->size_id) {
                DB::table('product_sizes')
                    ->where('product_id', $item->product_id)
                    ->where('size_id', $item->size_id)
                    ->decrement('stock', $item->quantity);
            }
        }

        $order->update(['stock_decremented' => true]);
    }
}
