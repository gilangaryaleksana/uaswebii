@include('base2.start')
<title>Tab Checkout | Adidas</title>

<style>
    #payLoadingOverlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(255, 255, 255, 0.85);
        z-index: 9999;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 16px;
    }

    #payLoadingOverlay.active {
        display: flex;
    }

    .loader {
        width: 100px;
        aspect-ratio: 1;
        padding: 10px;
        box-sizing: border-box;
        display: grid;
        background: #fff;
        filter: blur(5px) contrast(10) hue-rotate(300deg);
        mix-blend-mode: darken;
    }

    .loader:before,
    .loader:after {
        content: "";
        grid-area: 1/1;
        width: 40px;
        height: 40px;
        background: black;
        animation: l7 2s infinite;
    }

    .loader:after {
        animation-delay: -1s;
    }

    @keyframes l7 {
        0% {
            transform: translate(0, 0)
        }

        25% {
            transform: translate(100%, 0)
        }

        50% {
            transform: translate(100%, 100%)
        }

        75% {
            transform: translate(0, 100%)
        }

        100% {
            transform: translate(0, 0)
        }
    }

    /* ── Responsive cart table ── */
    .cart-table-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .cart-table {
        min-width: 480px;
        width: 100%;
        border-collapse: collapse;
    }

    /* ── Mobile stack: cart items as cards ── */
    @media (max-width: 640px) {

        /* Hide table header */
        .cart-table thead {
            display: none;
        }

        /* Each row becomes a card */
        .cart-table tbody tr {
            display: block;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 12px;
            padding: 12px;
            text-align: left;
        }

        .cart-table tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            border: none;
            font-size: 14px;
        }

        .cart-table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-right: 8px;
            white-space: nowrap;
        }

        /* Customer info grid: 2 cols → 1 col */
        .customer-grid {
            grid-template-columns: 1fr !important;
        }

        /* Payment grid: 4 cols → 2 cols */
        .payment-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }

        /* Shipping row: 3 cols → 1 col */
        .shipping-grid {
            grid-template-columns: 1fr !important;
        }

        /* Section title size */
        h1.page-title {
            font-size: 1.25rem !important;
        }

        h2.section-title {
            font-size: 1rem !important;
        }

        /* Grand total font */
        #grandTotalSection {
            font-size: 1rem !important;
        }

        .product-total {
            font-size: 1rem !important;
        }

        /* Pay button full width on mobile */
        #payBtn {
            width: 100%;
            text-align: center;
        }

        /* BCA / QRIS sections */
        #bcaVaNumber {
            font-size: 1.25rem !important;
            word-break: break-all;
        }

        #qrisImage {
            width: 200px;
            height: 200px;
        }

        /* Container padding */
        .checkout-container {
            padding: 16px !important;
        }
    }

    @media (max-width: 400px) {
        .payment-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>
</head>

<body>
    <div id="smooth-wrapper">
        <div id="smooth-content">
            @if ($source === 'product')
                <a href="{{ session('product_url', route('beranda')) }}"
                    class="inline-flex items-center gap-2 text-gray-600 hover:text-black px-6 py-4 duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Last Product
                </a>
            @else
                <a href="{{ route('user.cart') }}"
                    class="inline-flex items-center gap-2 text-gray-600 hover:text-black px-6 py-4 duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Cart
                </a>
            @endif
            <div class="checkout-container container mx-auto p-6">
                <form action="{{ route('checkout.process') }}" method="POST" class="space-y-4">
                    @csrf

                    <h1 class="page-title text-2xl font-semibold open-sans mb-6">Tab Checkout</h1>

                    {{-- Alert --}}
                    @if(session('error'))
                        <div class="bg-red-200 text-red-800 px-4 py-2 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Cart Items --}}
                    <div class="cart-table-wrapper mb-6">
                        <table class="cart-table">
                            <thead>
                                <tr class="bg-black text-white">
                                    <th class="p-2 border">Product</th>
                                    <th class="p-2 border">Size</th>
                                    <th class="p-2 border">Price</th>
                                    <th class="p-2 border">Quantity</th>
                                    <th class="p-2 border">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $total = 0; @endphp
                                @foreach($cartItems as $item)
                                    @php
    $price = $item->product->price ?? 0;
    $additional = $item->additional_price ?? 0;
    $qty = $item->quantity ?? 0;
    $subtotal = ($price + $additional) * $qty;
    $total += $subtotal;
                                    @endphp

                                    <tr class="text-center">
                                        <td class="p-2" data-label="Product">
                                            <div class="flex items-center gap-3">
                                                <img src="{{ $item->product->image }}" alt="{{ $item->product->name }}"
                                                    class="w-10 h-10 object-cover rounded shrink-0">
                                                <span>{{ $item->product->name }}</span>
                                            </div>
                                        </td>
                                        <td class="p-2" data-label="Size">{{ $item->size->code ?? '-' }}</td>
                                        <td class="p-2" data-label="Price">
                                            Rp{{ number_format($price + $additional, 2, ',', '.') }}</td>
                                        <td class="p-2" data-label="Qty">{{ $qty }}</td>
                                        <td class="p-2" data-label="Subtotal">Rp{{ number_format($subtotal, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="product-total text-right text-xl font-semibold mb-6">
                        Total: Rp{{ number_format($total, 2, ',', '.') }}
                    </div>

                    {{-- Customer & Payment Form --}}
                    <h2 class="section-title text-xl font-bold mt-8 mb-4">Customer Information</h2>

                    <div class="customer-grid grid grid-cols-2 gap-4 select-none">
                        <div>
                            <label class="block mb-1 font-semibold text-sm">First Name</label>
                            <input type="text" name="first_name" value="{{ auth()->user()->first_name }}"
                                class="px-3 py-2 w-full outline-none border-b-black border-b-1" required readonly>
                        </div>

                        <div>
                            <label class="block mb-1 font-semibold text-sm">Last Name</label>
                            <input type="text" name="last_name" value="{{ auth()->user()->last_name }}"
                                class="px-3 py-2 w-full outline-none border-b-black border-b-1" required readonly>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 font-semibold text-sm">Email</label>
                        <input type="email" name="email" value="{{ auth()->user()->email }}"
                            class="px-3 py-2 w-full outline-none border-b-black border-b-1" required readonly>
                    </div>

                    <div>
                        <label class="block mb-1 font-semibold text-sm">Phone</label>
                        <input type="text" name="phone" value="{{ auth()->user()->phone }}"
                            class="px-3 py-2 w-full outline-none border-b-black border-b-1" required readonly>
                    </div>

                    <div>
                        <label class="block mb-1 font-semibold text-sm">Address</label>
                        <textarea name="address" rows="3"
                            class="px-3 py-2 w-full outline-none border-b-black border-b-1" required
                            readonly>{{ auth()->user()->province_name }}, {{ auth()->user()->city_name }}, {{ auth()->user()->address }}</textarea>
                    </div>

                    {{-- Shipping Options --}}
                    <h2 class="section-title text-xl font-bold mt-8 mb-4">Select Shipping</h2>

                    <div class="space-y-3">
                        <div class="shipping-grid grid grid-cols-3 gap-3">
                            <div>
                                <label class="block mb-1 font-semibold text-sm">Kurir</label>
                                <select id="courierSelect" class="border px-3 py-2 rounded w-full outline-none text-sm">
                                    <option value="jne">JNE</option>
                                    <option value="jnt">J&T</option>
                                    <option value="pos">POS Indonesia</option>
                                    <option value="sicepat">SiCepat</option>
                                    <option value="tiki">TIKI</option>
                                </select>
                            </div>
                            <div>
                                <label class="block mb-1 font-semibold text-sm">Berat (gram)</label>
                                <input type="number" id="weightInput" value="500" min="1"
                                    class="border px-3 py-2 rounded w-full outline-none text-sm" disabled>
                            </div>
                            <div class="flex items-end">
                                <button type="button" id="checkOngkirBtn"
                                    class="bg-black text-white px-4 py-2 rounded text-sm hover:bg-white hover:border-black hover:text-black focus:text-black focus:bg-white border border-black w-full cursor-pointer">
                                    Cek Ongkir
                                </button>
                            </div>
                        </div>

                        <div id="ongkirResult" class="hidden space-y-2"></div>

                        <input type="hidden" name="shipping_service" id="selectedService">
                        <input type="hidden" name="shipping_cost" id="selectedCost">
                        <input type="hidden" name="shipping_courier" id="selectedCourier">
                        <input type="hidden" name="estimated_arrival" id="selectedEtd">

                        <div id="grandTotalSection" class="hidden text-right text-xl font-semibold mt-4 pt-4">
                            Total Pembayaran: <span id="grandTotal">Rp0</span>
                        </div>
                    </div>

                    {{-- Metode Pembayaran --}}
                    <h2 class="section-title text-xl font-bold mt-8 mb-4">Metode Pembayaran</h2>
                    <div class="payment-grid grid grid-cols-2 gap-3 sm:grid-cols-4">

                        <button type="button" onclick="selectPayment('qris', event)"
                            class="payment-option border-2 rounded px-4 py-3 flex flex-col items-center justify-center gap-2 hover:border-black transition cursor-pointer">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/a/a2/Logo_QRIS.svg"
                                class="h-8 object-contain" alt="QRIS">
                        </button>

                        <button type="button" onclick="selectPayment('mandiri', event)"
                            class="payment-option border-2 rounded px-4 py-3 flex flex-col items-center justify-center gap-2 hover:border-black transition cursor-pointer">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg"
                                class="h-8 object-contain" alt="Mandiri">
                        </button>

                        <button type="button" onclick="selectPayment('bca', event)"
                            class="payment-option border-2 rounded px-4 py-3 flex flex-col items-center justify-center gap-2 hover:border-black transition cursor-pointer">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg"
                                class="h-8 object-contain" alt="BCA">
                        </button>

                        <button type="button" onclick="selectPayment('alfamart', event)"
                            class="payment-option border-2 rounded px-4 py-3 flex flex-col items-center justify-center gap-2 hover:border-black transition cursor-pointer">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9e/ALFAMART_LOGO_BARU.png/960px-ALFAMART_LOGO_BARU.png"
                                class="h-8 object-contain" alt="Alfamart">
                        </button>

                        <button type="button" onclick="selectPayment('indomaret', event)"
                            class="payment-option border-2 rounded px-4 py-3 flex flex-col items-center justify-center gap-2 hover:border-black transition cursor-pointer">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9d/Logo_Indomaret.png/960px-Logo_Indomaret.png"
                                class="h-8 object-contain" alt="Indomaret">
                        </button>

                    </div>

                    <p id="paymentError" class="hidden text-red-500 text-sm mt-2">Pilih metode pembayaran terlebih
                        dahulu.</p>

                    <input type="hidden" name="payment_method" id="selectedPayment">

                    <button type="button" id="payBtn" disabled
                        class="inline-block border bg-black text-white px-6 py-2 rounded hover:bg-white focus:bg-white focus:text-black focus:border-black hover:border-black hover:text-black cursor-pointer text-sm">
                        Confirm & Pay
                    </button>

                    {{-- QRIS Section --}}
                    <div id="qrisSection" class="hidden mt-6 flex flex-col items-center text-center">
                        <h2 class="section-title text-xl font-bold mb-2">Bayar dengan QRIS</h2>
                        <p class="text-sm text-gray-500 mb-4">Scan QR code berikut dengan GoPay, OVO, Dana, atau
                            aplikasi apapun yang support QRIS</p>
                        <img id="qrisImage" src="" alt="QR Code" class="w-48 h-48 sm:w-64 sm:h-64 border p-2 rounded">
                        <p class="mt-4 text-sm text-gray-500">Selesaikan pembayaran dalam <span id="qrisTimer"
                                class="font-bold text-black">15:00</span></p>
                        <div class="flex flex-wrap gap-3 mt-4 justify-center">
                            <a id="qrisDownload" href="#" download="qris.png"
                                class="border px-4 py-2 text-sm rounded hover:bg-gray-100">
                                Download QR
                            </a>
                            <button type="button" onclick="checkQrisStatus()"
                                class="bg-black text-white px-4 py-2 text-sm rounded hover:bg-gray-800 cursor-pointer">
                                Cek Status Pembayaran
                            </button>
                        </div>
                    </div>

                    {{-- BCA VA Section --}}
                    <div id="bcaSection" class="hidden mt-6 border rounded p-4 sm:p-6">
                        <h2 class="section-title text-xl font-bold mb-2">Transfer BCA Virtual Account</h2>
                        <p class="text-sm text-gray-500 mb-4">Selesaikan pembayaran sebelum expired</p>
                        <div class="bg-gray-50 rounded p-4 mb-4">
                            <p class="text-sm text-gray-500 mb-1">Nomor Virtual Account</p>
                            <p class="text-2xl font-bold tracking-widest break-all" id="bcaVaNumber">-</p>
                            <button type="button" onclick="copyText('bcaVaNumber')"
                                class="mt-2 text-xs border px-3 py-1 rounded hover:bg-gray-100 cursor-pointer">
                                Salin Nomor
                            </button>
                        </div>
                        <div class="text-sm text-gray-500 space-y-1">
                            <p>Total Pembayaran: <span id="bcaTotal" class="font-semibold text-black"></span></p>
                            <p>Bank: <span class="font-semibold text-black">BCA</span></p>
                        </div>
                        <div class="flex gap-3 mt-4">
                            <button type="button" onclick="checkBcaStatus()"
                                class="bg-black text-white px-4 py-2 text-sm rounded hover:bg-gray-800 cursor-pointer w-full sm:w-auto">
                                Cek Status Pembayaran
                            </button>
                        </div>
                    </div>

                    {{-- Mandiri Section --}}
                    <div id="mandiriSection" class="hidden mt-6 border rounded p-4 sm:p-6">
                        <h2 class="section-title text-xl font-bold mb-2">Mandiri Bill Payment</h2>
                        <p class="text-sm text-gray-500 mb-4">Bayar melalui ATM, Mobile Banking, atau Internet Banking
                            Mandiri</p>
                        <div class="bg-gray-50 rounded p-4 mb-4 space-y-3">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Biller Code</p>
                                <p class="text-2xl font-bold tracking-widest" id="mandiriBillerCode">-</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Bill Key</p>
                                <p class="text-2xl font-bold tracking-widest" id="mandiriBillKey">-</p>
                                <button type="button" onclick="copyText('mandiriBillKey')"
                                    class="mt-2 text-xs border px-3 py-1 rounded hover:bg-gray-100 cursor-pointer">
                                    Salin Bill Key
                                </button>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500 space-y-1">
                            <p>Total Pembayaran: <span id="mandiriTotal" class="font-semibold text-black"></span></p>
                            <p>Bank: <span class="font-semibold text-black">Mandiri</span></p>
                        </div>
                        <div class="flex gap-3 mt-4">
                            <button type="button" onclick="checkMandiriStatus()"
                                class="bg-black text-white px-4 py-2 text-sm rounded hover:bg-gray-800 cursor-pointer w-full sm:w-auto">
                                Cek Status Pembayaran
                            </button>
                        </div>
                    </div>

                    {{-- Alfamart Section --}}
                    <div id="alfamartSection" class="hidden mt-6 border rounded p-4 sm:p-6">
                        <h2 class="section-title text-xl font-bold mb-2">Bayar di Alfamart</h2>
                        <p class="text-sm text-gray-500 mb-4">Tunjukkan kode berikut ke kasir Alfamart / Alfamidi
                            terdekat</p>
                        <div class="bg-gray-50 rounded p-4 mb-4">
                            <p class="text-sm text-gray-500 mb-1">Kode Pembayaran</p>
                            <p class="text-2xl font-bold tracking-widest" id="alfamartCode">-</p>
                            <button type="button" onclick="copyText('alfamartCode')"
                                class="mt-2 text-xs border px-3 py-1 rounded hover:bg-gray-100 cursor-pointer">
                                Salin Kode
                            </button>
                        </div>
                        <div class="text-sm text-gray-500 space-y-1">
                            <p>Total Pembayaran: <span id="alfamartTotal" class="font-semibold text-black"></span></p>
                            <p>Tersedia di: <span class="font-semibold text-black">Alfamart & Alfamidi</span></p>
                        </div>
                        <div class="flex gap-3 mt-4">
                            <button type="button" onclick="checkAlfamartStatus()"
                                class="bg-black text-white px-4 py-2 text-sm rounded hover:bg-gray-800 cursor-pointer w-full sm:w-auto">
                                Cek Status Pembayaran
                            </button>
                        </div>
                    </div>

                    {{-- Indomaret Section --}}
                    <div id="indomaretSection" class="hidden mt-6 border rounded p-4 sm:p-6">
                        <h2 class="section-title text-xl font-bold mb-2">Bayar di Indomaret</h2>
                        <p class="text-sm text-gray-500 mb-4">Tunjukkan kode berikut ke kasir Indomaret terdekat</p>
                        <div class="bg-gray-50 rounded p-4 mb-4">
                            <p class="text-sm text-gray-500 mb-1">Kode Pembayaran</p>
                            <p class="text-2xl font-bold tracking-widest" id="indomaretCode">-</p>
                            <button type="button" onclick="copyText('indomaretCode')"
                                class="mt-2 text-xs border px-3 py-1 rounded hover:bg-gray-100 cursor-pointer">
                                Salin Kode
                            </button>
                        </div>
                        <div class="text-sm text-gray-500 space-y-1">
                            <p>Total Pembayaran: <span id="indomaretTotal" class="font-semibold text-black"></span></p>
                            <p>Tersedia di: <span class="font-semibold text-black">Indomaret</span></p>
                        </div>
                        <div class="flex gap-3 mt-4">
                            <button type="button" onclick="checkIndomaretStatus()"
                                class="bg-black text-white px-4 py-2 text-sm rounded hover:bg-gray-800 cursor-pointer w-full sm:w-auto">
                                Cek Status Pembayaran
                            </button>
                        </div>
                    </div>

                    <div id="snap-container" class="mt-6"></div>
                </form>
            </div>
        </div>
    </div>

    <div id="toastContainer" class="fixed bottom-6 right-6 z-50 space-y-2 max-w-xs"></div>
    <div id="payLoadingOverlay">
        <div class="loader"></div>
        <p style="font-size: 14px; color: #333; margin: 0;">Memproses pembayaran...</p>
    </div>


    <script>
        const cityId = "{{ auth()->user()->city_id }}";
        const productTotal = {{ $total }};

        // ✅ 1. Cek Ongkir
        document.getElementById('checkOngkirBtn').addEventListener('click', function () {
            const courier = document.getElementById('courierSelect').value;
            const weight = document.getElementById('weightInput').value;
            const resultDiv = document.getElementById('ongkirResult');

            resultDiv.innerHTML = '<p class="text-sm text-gray-500">Mengecek ongkir...</p>';
            resultDiv.classList.remove('hidden');

            fetch('{{ url("/api/cost") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    origin: '501',
                    destination: cityId,
                    weight: weight,
                    courier: courier
                })
            })
                .then(res => res.json())
                .then(data => {
                    console.log(JSON.stringify(data));
                    const services = data?.data ?? [];

                    if (services.length === 0) {
                        resultDiv.innerHTML = '<p class="text-sm text-red-500">Tidak ada layanan tersedia.</p>';
                        return;
                    }

                    resultDiv.innerHTML = '<p class="font-semibold text-sm mb-2">Pilih Layanan:</p>';

                    services.forEach(service => {
                        const cost = service.cost;
                        const etd = service.etd;

                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'w-full text-left border px-4 py-2 rounded text-sm hover:border-black transition service-option';
                        btn.innerHTML = `
                        <div class="flex flex-wrap justify-between items-start gap-1">
                            <div>
                                <span class="font-semibold">${service.code.toUpperCase()} - ${service.service}</span>
                                <span class="text-gray-500 text-xs ml-1">${service.description}</span>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="font-semibold block">Rp${cost.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                                <span class="text-xs text-gray-500">ETD: ${etd}</span>
                            </div>
                        </div>
                    `;

                        btn.addEventListener('click', function () {
                            document.querySelectorAll('.service-option').forEach(b => {
                                b.classList.remove('border-black', 'bg-gray-50');
                            });
                            this.classList.add('border-black', 'bg-gray-50');

                            document.getElementById('selectedService').value = service.service;
                            document.getElementById('selectedCost').value = cost;
                            document.getElementById('selectedCourier').value = service.code.toUpperCase();

                            // Hitung estimated_arrival dari ETD
                            const etdDays = parseInt(etd.split('-')[1] ?? etd.split('-')[0]);
                            const arrival = new Date();
                            arrival.setDate(arrival.getDate() + etdDays);
                            const formatted = arrival.toISOString().split('T')[0]; // YYYY-MM-DD

                            document.getElementById('selectedEtd').value = formatted;

                            const grandTotal = productTotal + cost;
                            document.getElementById('grandTotal').textContent = 'Rp' + grandTotal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            document.getElementById('grandTotalSection').classList.remove('hidden');

                            const payBtn = document.getElementById('payBtn');
                            payBtn.disabled = false;
                            payBtn.className = 'inline-block border bg-black text-white px-6 py-2 rounded hover:bg-white focus:bg-white focus:text-black focus:border-black hover:border-black hover:text-black cursor-pointer text-sm';
                            showToast('Ongkir dipilih: Rp' + cost.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }), 'success');
                        });

                        resultDiv.appendChild(btn);
                    });
                })
                .catch(() => {
                    resultDiv.innerHTML = '<p class="text-sm text-red-500">Gagal mengambil data ongkir.</p>';
                });
        });

        // ✅ 2. Pilih Metode Pembayaran — terima event sebagai parameter
        function selectPayment(method, e) {
            document.querySelectorAll('.payment-option').forEach(btn => {
                btn.classList.remove('border-black', 'bg-gray-50');
                btn.classList.add('border-gray-200');
            });

            e.currentTarget.classList.add('border-black', 'bg-gray-50');
            e.currentTarget.classList.remove('border-gray-200');

            document.getElementById('selectedPayment').value = method;
            document.getElementById('paymentError').classList.add('hidden');
        }

        // ✅ 3. Confirm & Pay
        document.getElementById('payBtn').addEventListener('click', function () {
            if (!document.getElementById('selectedPayment').value) {
                document.getElementById('paymentError').classList.remove('hidden');
                document.getElementById('paymentError').scrollIntoView({ behavior: 'smooth' });
                return;
            }

            document.getElementById('payLoadingOverlay').classList.add('active');

            const form = document.querySelector('form');
            const formData = new FormData(form);

            fetch('{{ route("checkout.process") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    const paymentMethod = document.getElementById('selectedPayment').value;

                    if (data.snap_token) {
                        if (paymentMethod === 'qris') {
                            fetch('{{ route("checkout.qris") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ order_id: data.order_id })
                            })
                                .then(res => res.json())
                                .then(qris => {
                                    document.getElementById('payLoadingOverlay').classList.remove('active');
                                    if (qris.qr_url) {
                                        document.getElementById('qrisImage').src = qris.qr_url;
                                        document.getElementById('qrisDownload').href = qris.qr_url;
                                        document.getElementById('qrisSection').classList.remove('hidden');
                                        showToast('QR Code siap, silakan scan!', 'success');
                                        document.getElementById('qrisSection').scrollIntoView({ behavior: 'smooth' });

                                        window._qrisOrderId = qris.order_id;

                                        let timeLeft = 15 * 60;
                                        const timer = setInterval(() => {
                                            const m = Math.floor(timeLeft / 60).toString().padStart(2, '0');
                                            const s = (timeLeft % 60).toString().padStart(2, '0');
                                            document.getElementById('qrisTimer').textContent = m + ':' + s;
                                            if (timeLeft <= 0) {
                                                clearInterval(timer);
                                                document.getElementById('qrisTimer').textContent = 'Expired';
                                            }
                                            timeLeft--;
                                        }, 1000);

                                        window._qrisTimer = timer;
                                    } else {
                                        showToast(qris.error ?? 'Gagal mendapatkan QR code.', 'error');
                                    }
                                })
                                .catch(() => {
                                    document.getElementById('payLoadingOverlay').classList.remove('active');
                                    showToast('Gagal membuat QRIS.', 'error');
                                });

                        } else if (paymentMethod === 'bca') {
                            fetch('{{ route("checkout.bca") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ order_id: data.order_id })
                            })
                                .then(res => res.json())
                                .then(bca => {
                                    document.getElementById('payLoadingOverlay').classList.remove('active');
                                    if (bca.va_number) {
                                        document.getElementById('bcaVaNumber').textContent = bca.va_number;
                                        document.getElementById('bcaTotal').textContent = 'Rp' + parseFloat(bca.total).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); document.getElementById('bcaSection').classList.remove('hidden');
                                        showToast('Nomor VA BCA berhasil dibuat!', 'success');
                                        document.getElementById('bcaSection').scrollIntoView({ behavior: 'smooth' });
                                        window._bcaOrderId = bca.order_id;
                                    } else {
                                        showToast(bca.error ?? 'Gagal mendapatkan nomor VA.', 'error');
                                    }
                                })
                                .catch(() => {
                                    document.getElementById('payLoadingOverlay').classList.remove('active');
                                    showToast('Gagal membuat BCA VA.', 'error');
                                });

                        } else if (paymentMethod === 'mandiri') {
                            fetch('{{ route("checkout.mandiri") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ order_id: data.order_id })
                            })
                                .then(res => res.json())
                                .then(mandiri => {
                                    document.getElementById('payLoadingOverlay').classList.remove('active');
                                    if (mandiri.bill_key) {
                                        document.getElementById('mandiriBillKey').textContent = mandiri.bill_key;
                                        document.getElementById('mandiriBillerCode').textContent = mandiri.biller_code;
                                        document.getElementById('mandiriTotal').textContent = 'Rp' + Math.round(mandiri.total).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                        document.getElementById('mandiriSection').classList.remove('hidden');
                                        showToast('Kode Mandiri Bill Payment berhasil dibuat!', 'success');
                                        document.getElementById('mandiriSection').scrollIntoView({ behavior: 'smooth' });
                                        window._mandiriOrderId = mandiri.order_id;
                                    } else {
                                        showToast(mandiri.error ?? 'Gagal mendapatkan kode Mandiri.', 'error');
                                    }
                                })
                                .catch(() => {
                                    document.getElementById('payLoadingOverlay').classList.remove('active');
                                    showToast('Gagal membuat Mandiri Bill.', 'error');
                                });

                        } else if (paymentMethod === 'alfamart') {
                            fetch('{{ route("checkout.alfamart") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ order_id: data.order_id })
                            })
                                .then(res => res.json())
                                .then(alfa => {
                                    document.getElementById('payLoadingOverlay').classList.remove('active');
                                    if (alfa.payment_code) {
                                        document.getElementById('alfamartCode').textContent = alfa.payment_code;
                                        document.getElementById('alfamartTotal').textContent = 'Rp' + Math.round(alfa.total).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                        document.getElementById('alfamartSection').classList.remove('hidden');
                                        showToast('Kode Alfamart berhasil dibuat!', 'success');
                                        document.getElementById('alfamartSection').scrollIntoView({ behavior: 'smooth' });
                                        window._alfamartOrderId = alfa.order_id;
                                    } else {
                                        showToast(alfa.error ?? 'Gagal mendapatkan kode Alfamart.', 'error');
                                    }
                                })
                                .catch(() => {
                                    document.getElementById('payLoadingOverlay').classList.remove('active');
                                    showToast('Gagal membuat kode Alfamart.', 'error');
                                });

                        } else if (paymentMethod === 'indomaret') {
                            fetch('{{ route("checkout.indomaret") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ order_id: data.order_id })
                            })
                                .then(res => res.json())
                                .then(indo => {
                                    document.getElementById('payLoadingOverlay').classList.remove('active');
                                    if (indo.payment_code) {
                                        document.getElementById('indomaretCode').textContent = indo.payment_code;
                                        document.getElementById('indomaretTotal').textContent = 'Rp' + Math.round(indo.total).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                        document.getElementById('indomaretSection').classList.remove('hidden');
                                        showToast('Kode Indomaret berhasil dibuat!', 'success');
                                        document.getElementById('indomaretSection').scrollIntoView({ behavior: 'smooth' });
                                        window._indomaretOrderId = indo.order_id;
                                    } else {
                                        showToast(indo.error ?? 'Gagal mendapatkan kode Indomaret.', 'error');
                                    }
                                })
                                .catch(() => {
                                    document.getElementById('payLoadingOverlay').classList.remove('active');
                                    showToast('Gagal membuat kode Indomaret.', 'error');
                                });

                        }
                        else {
                            document.getElementById('payLoadingOverlay').classList.remove('active');
                            showToast('Pembayaran ' + paymentMethod.toUpperCase() + ' akan segera diproses.', 'info');
                        }
                    } else {
                        document.getElementById('payLoadingOverlay').classList.remove('active');
                        showToast(data.error ?? 'Terjadi kesalahan.', 'error');
                    }
                })
                .catch(() => {
                    document.getElementById('payLoadingOverlay').classList.remove('active');
                    showToast('Gagal menghubungi server.', 'error');
                });
        });
    </script>

    {{--
    <script>
        const cityId = "{{ auth()->user()->city_id }}";
        const productTotal = {{ $total }};
        const checkoutProcessUrl = '{{ route("checkout.process") }}';
        const orderBaseUrl = '{{ url("/order") }}';

        // ✅ 1. Cek Ongkir
        document.getElementById('checkOngkirBtn').addEventListener('click', function () {
            const courier = document.getElementById('courierSelect').value;
            const weight = document.getElementById('weightInput').value;
            const resultDiv = document.getElementById('ongkirResult');

            resultDiv.innerHTML = '<p class="text-sm text-gray-500">Mengecek ongkir...</p>';
            resultDiv.classList.remove('hidden');

            fetch('{{ url("/api/cost") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    origin: '501',
                    destination: cityId,
                    weight: weight,
                    courier: courier
                })
            })
                .then(res => res.json())
                .then(data => {
                    const services = data?.data ?? [];

                    if (services.length === 0) {
                        resultDiv.innerHTML = '<p class="text-sm text-red-500">Tidak ada layanan tersedia.</p>';
                        return;
                    }

                    resultDiv.innerHTML = '<p class="font-semibold text-sm mb-2">Pilih Layanan:</p>';

                    services.forEach(service => {
                        const cost = service.cost;
                        const etd = service.etd;

                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'w-full text-left border px-4 py-2 rounded text-sm hover:border-black transition service-option';
                        btn.innerHTML = `
                    <div class="flex flex-wrap justify-between items-start gap-1">
                        <div>
                            <span class="font-semibold">${service.code.toUpperCase()} - ${service.service}</span>
                            <span class="text-gray-500 text-xs ml-1">${service.description}</span>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="font-semibold block">Rp${cost.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                            <span class="text-xs text-gray-500">ETD: ${etd}</span>
                        </div>
                    </div>
                `;

                        btn.addEventListener('click', function () {
                            document.querySelectorAll('.service-option').forEach(b => {
                                b.classList.remove('border-black', 'bg-gray-50');
                            });
                            this.classList.add('border-black', 'bg-gray-50');

                            document.getElementById('selectedService').value = service.service;
                            document.getElementById('selectedCost').value = cost;
                            document.getElementById('selectedCourier').value = service.code.toUpperCase();

                            const grandTotal = productTotal + cost;
                            document.getElementById('grandTotal').textContent = 'Rp' + grandTotal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            document.getElementById('grandTotalSection').classList.remove('hidden');

                            const payBtn = document.getElementById('payBtn');
                            payBtn.disabled = false;
                            payBtn.className = 'inline-block border bg-black text-white px-6 py-2 rounded hover:bg-white focus:bg-white focus:text-black focus:border-black hover:border-black hover:text-black cursor-pointer text-sm';
                            showToast('Ongkir dipilih: Rp' + cost.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }), 'success');
                        });

                        resultDiv.appendChild(btn);
                    });
                })
                .catch(() => {
                    resultDiv.innerHTML = '<p class="text-sm text-red-500">Gagal mengambil data ongkir.</p>';
                });
        });

        // ✅ 2. Pilih Metode Pembayaran
        function selectPayment(method, e) {
            document.querySelectorAll('.payment-option').forEach(btn => {
                btn.classList.remove('border-black', 'bg-gray-50');
                btn.classList.add('border-gray-200');
            });
            e.currentTarget.classList.add('border-black', 'bg-gray-50');
            e.currentTarget.classList.remove('border-gray-200');
            document.getElementById('selectedPayment').value = method;
            document.getElementById('paymentError').classList.add('hidden');
        }

        // ✅ 3. MODE 1: Snap UI bawaan Midtrans
        document.getElementById('payBtn').addEventListener('click', function () {
            if (!document.getElementById('selectedPayment').value) {
                document.getElementById('paymentError').classList.remove('hidden');
                document.getElementById('paymentError').scrollIntoView({ behavior: 'smooth' });
                return;
            }

            document.getElementById('payLoadingOverlay').classList.add('active');

            const form = document.querySelector('form');
            const formData = new FormData(form);

            fetch(checkoutProcessUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    document.getElementById('payLoadingOverlay').classList.remove('active');
                    if (data.snap_token) {
                        window.snap.pay(data.snap_token, {
                            onSuccess: function () {
                                showToast('Pembayaran berhasil!', 'success');
                                window.location.href = orderBaseUrl + '/' + data.order_id;
                            },
                            onPending: function () {
                                showToast('Menunggu pembayaran...', 'info');
                                window.location.href = orderBaseUrl + '/' + data.order_id;
                            },
                            onError: function () {
                                showToast('Pembayaran gagal.', 'error');
                            },
                            onClose: function () {
                                showToast('Popup ditutup sebelum selesai.', 'info');
                            }
                        });
                    } else {
                        showToast(data.error ?? 'Terjadi kesalahan.', 'error');
                    }
                })
                .catch(() => {
                    document.getElementById('payLoadingOverlay').classList.remove('active');
                    showToast('Gagal menghubungi server.', 'error');
                });
        });
    </script> --}}

    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>

    <script>
        function showToast(message, type = 'success') {
            const colors = {
                success: 'bg-black text-white',
                error: 'bg-red-600 text-white',
                info: 'bg-gray-700 text-white',
            };

            const toast = document.createElement('div');
            toast.className = `${colors[type]} px-4 py-3 border shadow-lg text-sm flex items-center gap-3 transition-all duration-300 opacity-0 translate-y-2 rounded`;
            toast.innerHTML = `
                <span class="flex-1">${message}</span>
                <button onclick="this.parentElement.remove()" class="ml-auto text-white opacity-70 hover:opacity-100 cursor-pointer shrink-0">✕</button>
            `;

            document.getElementById('toastContainer').appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('opacity-0', 'translate-y-2');
                toast.classList.add('opacity-100', 'translate-y-0');
            });

            setTimeout(() => {
                toast.classList.add('opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        function copyText(elementId) {
            const text = document.getElementById(elementId).textContent;
            navigator.clipboard.writeText(text).then(() => showToast('Berhasil disalin!', 'success'));
        }

        function checkQrisStatus() { window.location.href = '{{ url("/order") }}/' + window._qrisOrderId; }
        function checkBcaStatus() { window.location.href = '{{ url("/order") }}/' + window._bcaOrderId; }
        function checkMandiriStatus() { window.location.href = '{{ url("/order") }}/' + window._mandiriOrderId; }
        function checkAlfamartStatus() { window.location.href = '{{ url("/order") }}/' + window._alfamartOrderId; }
        function checkIndomaretStatus() { window.location.href = '{{ url("/order") }}/' + window._indomaretOrderId; } 
    </script>

    <script>
        window.addEventListener('beforeunload', function () {
            fetch('{{ route("buy.now.cancel") }}', {
                method: 'GET',
                credentials: 'same-origin'
            });
        });
    </script>

</body>

</html>