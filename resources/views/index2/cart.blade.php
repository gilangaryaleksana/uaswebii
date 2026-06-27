@extends('v_user.v_cart.app')

@section('content')
    <div
        class="md:pt-[14rem] pt-4 px-4 md:pl-5 md:pr-0 flex flex-col gap-2 md:w-5xl w-full justify-center items-center md:items-start md:justify-start">
        <h2 class="text-2xl font-semibold mb-4">Shopping Cart</h2>


        @if ($cartItems->isEmpty())
            <p class="text-gray-500">Cart is still empty</p>
        @else
            <form action="{{ route('checkout.select') }}" method="POST" id="checkout-form">
                @csrf
                {{-- Header --}}
                <div class="hidden md:grid grid-cols-5 py-3 border-1 border-black bg-black text-white px-2 my-2">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="select-all" class="w-4 h-4 cursor-pointer">
                        <p>Pilih</p>
                    </div>
                    <p>Name</p>
                    <p>Price</p>
                    <p>Total Price</p>
                    <p class="text-center">Action</p>
                </div>
                {{-- Cart Items --}}
                @foreach ($cartItems as $item)
                    <div class="flex md:flex-row flex-col gap-4 mb-4 p-4 items-center border border-black shadow-sm w-full"
                            id="cart-item-{{ $item->id }}">

                        {{-- Checkbox --}}
                        <input type="checkbox" name="selected_items[]" value="{{ $item->id }}"
                            class="item-checkbox w-5 h-5 cursor-pointer self-center" checked>

                        {{-- Product Image --}}
                        @if($item->product && $item->product->image)
                            <a href="{{ route('product.show', ['id' => $item->product->id, 'slug' => $item->product->slug]) }}">
                                <img src="{{ $item->product->image }}" alt="{{ $item->product->name }}"
                                    class="md:w-20 md:h-20 w-40 h-40 object-cover rounded">
                            </a>
                        @else
                            <div class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center">
                                <span class="text-gray-400">No Image</span>
                            </div>
                        @endif

                        {{-- Product Info --}}
                        <div class="grid md:grid-cols-4 grid-cols-1 w-full gap-4 items-center">
                            {{-- Name --}}
                            <a
                                href="{{ $item->product ? route('product.show', ['id' => $item->product->id, 'slug' => $item->product->slug]) : '#' }}">
                                <div class="text-xl md:text-2xl">
                                    {{ $item->product->name ?? 'Product Not Found' }}
                                </div>
                            </a>

                            <div class="price-per-item" data-cart-id="{{ $item->id }}"
                                data-base-price="{{ $item->product->price ?? 0 }}"
                                data-additional-price="{{ $item->size?->pivot->additional_price ?? 0 }}">
                                Rp{{ number_format(($item->product->price ?? 0) + ($item->size?->pivot->additional_price ?? 0), 2, ',', '.') }}
                            </div>


                            <div class="price text-sm md:ml-5" data-cart-id="{{ $item->id }}"
                                data-base-price="{{ $item->product->price ?? 0 }}"
                                data-additional-price="{{ $item->size?->pivot->additional_price ?? 0 }}">
                                Subtotal:
                                Rp{{ number_format((($item->product->price ?? 0) + ($item->size?->pivot->additional_price ?? 0)) * ($item->quantity ?? 1), 2, ',', '.') }}
                            </div>


                            {{-- Actions --}}
                            <div class="border py-3 px-3 border-gray-400 cart-item" data-cart-id="{{ $item->id }}">
                                {{-- Update Size --}}
                                <div class="mt-2">
                                    <label class="text-gray-600 mr-2">Size:</label>
                                    <select name="size_id" data-cart-id="{{ $item->id }}"
                                        class="border border-gray-600 text-gray-600 rounded px-2 py-1 text-sm update-size cursor-pointer">

                                        @foreach($item->product?->sizes ?? [] as $size)
                                            @php
                                                $stock = $size->pivot->stock ?? 0;
                                                $additionalPrice = $size->pivot->additional_price ?? 0;
                                            @endphp
                                            <option value="{{ $size->id }}" data-stock="{{ $stock }}"
                                                data-additional-price="{{ $additionalPrice }}" {{ $item->size_id == $size->id ? 'selected' : '' }} {{ $stock <= 0 ? 'disabled' : '' }}>
                                                {{ $size->code }} {{ $stock <= 0 ? '(Out of Stock)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>

                                @php
                                    $initialStock = $item->size?->pivot->stock ?? 0;
                                @endphp

                                {{-- Update Quantity --}}
                                <div class="mt-2 flex flex-col gap-1">
                                    <label class="text-gray-600">Quantity:</label>
                                    <div class="flex items-center justify-between">
                                        <button
                                            class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50 decrease-qty cursor-pointer"
                                            data-cart-id="{{ $item->id }}">
                                            <i class="fas fa-minus text-gray-600 text-xs"></i>
                                        </button>

                                        <input id="quantity-{{ $item->id }}" type="number" value="{{ $item->quantity ?? 1 }}"
                                            min="1" max="{{ $initialStock }}"
                                            class="w-16 h-8 text-center text-gray-600 border border-gray-300 rounded-2xl quantity-input"
                                            data-cart-id="{{ $item->id }}">

                                        <button
                                            class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50 increase-qty cursor-pointer"
                                            data-cart-id="{{ $item->id }}">
                                            <i class="fas fa-plus text-gray-600 text-xs"></i>
                                        </button>
                                    </div>
                                    <span class="text-xs text-gray-500 max-label">
                                        Max: {{ $initialStock }}
                                    </span>

                                </div>

                                {{-- Delete Button --}}
                                <div class="mt-3">
                                    <button type="button" onclick="deleteItem('{{ route('cart.destroy', $item->id) }}')"
                                        class="flex gap-2 justify-center items-center text-sm cursor-pointer border rounded-sm w-full py-2 bg-black text-white hover:bg-white hover:text-black">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Total -->
                <div class="mt-6 p-4 border-t border-gray-200">
                    <div class="text-xl mb-4">
                        Total: <span id="cart-total" class="text-gray-600">
                            Rp{{ number_format($cartItems->sum(function ($item) {
                $qty = intval($item->quantity);
                if ($qty < 1)
                    $qty = 1;
                return (($item->product->price ?? 0) + ($item->size?->pivot->additional_price ?? 0)) * $qty;
            }), 2, ',', '.') }} </span>
                    </div>

                    <div class="flex gap-4 mt-6 justify-between">
                        @if (Auth::check())
                            {{-- Jika sudah login → ke beranda --}}
                            <a href="{{ route('beranda') }}"
                                class="group inline-flex items-center gap-2 hover:text-black text-gray-600! px-6 py-2 border-b-white border-b-1 hover:border-b hover:border-gray-600 duration-200">

                                <svg class="w-5 h-5 transform transition-all duration-300 -translate-x-2 group-hover:translate-x-0"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>

                                Continue Shopping
                            </a>

                        @else
                            {{-- Jika belum login → ke welcome --}}
                            <a href="{{ route('welcome') }}"
                                class="group inline-flex items-center gap-2 hover:text-black text-gray-600! px-6 py-2 border-b-white border-b-1 hover:border-b hover:border-gray-600 duration-200">

                                <svg class="w-5 h-5 transform transition-all duration-300 -translate-x-2 group-hover:translate-x-0"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>

                                Continue Shopping
                            </a>
                        @endif
                        <button type="submit" id="checkout-btn"
                            class="inline-block bg-black text-white px-6 py-2 rounded hover:bg-white hover:border hover:text-black focus:bg-white border-1 focus:text-black focus:border focus:border-black cursor-pointer text-sm">
                            Proceed to Checkout
                            <span id="selected-count">{{ $cartItems->count() }}</span>
                        </button>

                    </div>
            </form>
        @endif
    </div>

    <style>
        .quantity-input {
            -moz-appearance: textfield;
        }

        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .item-checkbox,
        #select-all {
            appearance: none;
            -webkit-appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            background-color: white;
            border: 1.5px solid #9ca3af;
            /* abu-abu, sesuaikan */
            border-radius: 0.25rem;
            cursor: pointer;
            position: relative;
        }

        .item-checkbox:checked,
        #select-all:checked {
            background-color: white;
            border-color: black;
        }

        .item-checkbox:checked::after,
        #select-all:checked::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 1px;
            width: 6px;
            height: 11px;
            border: solid black;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
    </style>

    <script>
        function deleteItem(url) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.innerHTML = `
                                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                                <input type="hidden" name="_method" value="DELETE">
                            `;
            document.body.appendChild(form);
            form.submit();
        }

        document.addEventListener('DOMContentLoaded', function () {

            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.item-checkbox');
            const checkoutBtn = document.getElementById('checkout-btn');
            const selectedCount = document.getElementById('selected-count');

            /* =========================
               SUBTOTAL & TOTAL
            ========================== */

            function updateSubtotal(cartId, quantity) {
                const subtotalEl = document.querySelector(`.price[data-cart-id="${cartId}"]`);
                if (!subtotalEl) return;

                const base = parseFloat(subtotalEl.dataset.basePrice) || 0;
                const additional = parseFloat(subtotalEl.dataset.additionalPrice) || 0;

                const subtotal = (base + additional) * quantity;
                subtotalEl.textContent = `Subtotal: Rp${Math.round(subtotal).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

                // FIX #1: Ganti updateCartTotal() → updateCheckoutState()
                // agar total hanya dihitung dari item yang dicentang
                updateCheckoutState();
            }

            function updatePricePerItem(cartId, additional) {
                const el = document.querySelector(`.price-per-item[data-cart-id="${cartId}"]`);
                if (!el) return;

                const base = parseFloat(el.dataset.basePrice) || 0;
                el.dataset.additionalPrice = additional;
                el.textContent = `Rp${Math.round(base + additional).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            }

            /* =========================
               UPDATE QUANTITY (CORE)
            ========================== */

            function updateQuantity(cartId, newQty) {
                const input = document.querySelector(`.quantity-input[data-cart-id="${cartId}"]`);
                if (!input) return;

                const max = parseInt(input.max) || 9999;

                if (isNaN(newQty) || newQty < 1) newQty = 1;
                if (newQty > max) newQty = max;

                input.value = newQty;

                updateSubtotal(cartId, newQty);

                const decreaseBtn = document.querySelector(`.decrease-qty[data-cart-id="${cartId}"]`);
                const increaseBtn = document.querySelector(`.increase-qty[data-cart-id="${cartId}"]`);

                // FIX #2: Gunakan parseInt() agar tidak ada perbandingan string vs number
                if (decreaseBtn) decreaseBtn.disabled = (parseInt(input.value) <= 1);
                if (increaseBtn) increaseBtn.disabled = (parseInt(input.value) >= parseInt(input.max));

                fetch(`/cart/${cartId}/update-quantity`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ quantity: newQty })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            alert(data.message || 'Gagal update quantity');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Terjadi kesalahan saat update quantity');
                    });
            }

            /* =========================
               BUTTON + / -
            ========================== */

            document.querySelectorAll('.increase-qty').forEach(btn => {
                btn.addEventListener('click', function () {
                    const cartId = this.dataset.cartId;
                    const input = document.querySelector(`.quantity-input[data-cart-id="${cartId}"]`);
                    updateQuantity(cartId, parseInt(input.value) + 1);
                });
            });

            document.querySelectorAll('.decrease-qty').forEach(btn => {
                btn.addEventListener('click', function () {
                    const cartId = this.dataset.cartId;
                    const input = document.querySelector(`.quantity-input[data-cart-id="${cartId}"]`);
                    updateQuantity(cartId, parseInt(input.value) - 1);
                });
            });

            /* =========================
               INPUT MANUAL
            ========================== */

            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('change', function () {
                    updateQuantity(this.dataset.cartId, parseInt(this.value));
                });

                input.addEventListener('wheel', e => e.preventDefault());
            });

            /* =========================
               INIT SIZE (PAGE LOAD)
            ========================== */

            document.querySelectorAll('.update-size').forEach(select => {
                const option = select.selectedOptions[0];
                if (!option || !option.value) return;

                const cartId = select.dataset.cartId;
                const stock = parseInt(option.dataset.stock) || 0;
                const additional = parseFloat(option.dataset.additionalPrice) || 0;

                const input = document.querySelector(`.quantity-input[data-cart-id="${cartId}"]`);
                if (!input) return;

                const maxQty = stock > 0 ? stock : 1;
                input.max = maxQty;
                if (parseInt(input.value) > maxQty) input.value = maxQty;

                const maxLabel = input.closest('.mt-2')?.querySelector('.max-label');
                if (maxLabel) maxLabel.textContent = `Max: ${stock}`;

                updatePricePerItem(cartId, additional);

                const subtotalEl = document.querySelector(`.price[data-cart-id="${cartId}"]`);
                if (subtotalEl) subtotalEl.dataset.additionalPrice = additional;

                updateSubtotal(cartId, parseInt(input.value));

                const decreaseBtn = document.querySelector(`.decrease-qty[data-cart-id="${cartId}"]`);
                const increaseBtn = document.querySelector(`.increase-qty[data-cart-id="${cartId}"]`);

                // FIX #2: parseInt() konsisten
                if (decreaseBtn) decreaseBtn.disabled = (parseInt(input.value) <= 1);
                if (increaseBtn) increaseBtn.disabled = (parseInt(input.value) >= parseInt(input.max));
            });

            /* =========================
               SIZE CHANGE (USER ACTION)
            ========================== */

            document.querySelectorAll('.update-size').forEach(select => {
                select.addEventListener('change', function () {
                    const cartId = this.dataset.cartId;
                    const option = this.selectedOptions[0];
                    if (!option) return;

                    const stock = parseInt(option.dataset.stock) || 0;
                    const additional = parseFloat(option.dataset.additionalPrice) || 0;

                    const input = document.querySelector(`.quantity-input[data-cart-id="${cartId}"]`);
                    if (!input) return;

                    const maxQty = stock > 0 ? stock : 1;
                    input.max = maxQty;
                    if (parseInt(input.value) > maxQty) input.value = maxQty;

                    const maxLabel = input.closest('.mt-2')?.querySelector('.max-label');
                    if (maxLabel) maxLabel.textContent = `Max: ${stock}`;

                    updatePricePerItem(cartId, additional);

                    const subtotalEl = document.querySelector(`.price[data-cart-id="${cartId}"]`);
                    if (subtotalEl) subtotalEl.dataset.additionalPrice = additional;

                    updateSubtotal(cartId, parseInt(input.value));

                    const decreaseBtn = document.querySelector(`.decrease-qty[data-cart-id="${cartId}"]`);
                    const increaseBtn = document.querySelector(`.increase-qty[data-cart-id="${cartId}"]`);

                    // FIX #2: parseInt() konsisten
                    if (decreaseBtn) decreaseBtn.disabled = (parseInt(input.value) <= 1);
                    if (increaseBtn) increaseBtn.disabled = (parseInt(input.value) >= parseInt(input.max));

                    fetch(`/cart/${cartId}/update-size`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            size_id: option.value,
                            quantity: parseInt(input.value)
                        })
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (!data.success) {
                                alert(data.message || 'Gagal update size');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert('Terjadi kesalahan saat update size');
                        });
                });
            });

            /* =========================
            CHECKBOX BORDER
            ========================== */
            function updateItemBorder(checkbox) {
                const cartItem = document.getElementById(`cart-item-${checkbox.value}`);
                if (!cartItem) return;

                if (checkbox.checked) {
                    cartItem.classList.remove('border-gray-300');
                    cartItem.classList.add('border-black');
                } else {
                    cartItem.classList.remove('border-black');
                    cartItem.classList.add('border-gray-300');
                }
            }

            /* =========================
               CHECKBOX LOGIC
            ========================== */

            function updateCheckoutState() {
                const checked = document.querySelectorAll('.item-checkbox:checked');
                const count = checked.length;

                selectedCount.textContent = count + ' item';
                checkoutBtn.disabled = count === 0;
                checkoutBtn.classList.toggle('opacity-50', count === 0);
                checkoutBtn.classList.toggle('cursor-not-allowed', count === 0);

                // Hitung total hanya dari item yang dicentang
                let total = 0;
                checked.forEach(cb => {
                    const cartId = cb.value;
                    const subtotalEl = document.querySelector(`.price[data-cart-id="${cartId}"]`);
                    if (!subtotalEl) return;
                    const base = parseFloat(subtotalEl.dataset.basePrice) || 0;
                    const additional = parseFloat(subtotalEl.dataset.additionalPrice) || 0;
                    const input = document.querySelector(`.quantity-input[data-cart-id="${cartId}"]`);
                    const qty = input ? parseInt(input.value) || 1 : 1;
                    total += (base + additional) * qty;
                });

                const totalEl = document.getElementById('cart-total');
                if (totalEl) totalEl.textContent = `Rp${Math.round(total).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

                // Update select all state
                selectAll.checked = count === checkboxes.length;
                selectAll.indeterminate = count > 0 && count < checkboxes.length;
            }

            // Select All
            selectAll?.addEventListener('change', function () {
                checkboxes.forEach(cb => {
                    cb.checked = this.checked;
                    updateItemBorder(cb);
                });
                updateCheckoutState();
            });

            // Per item
            checkboxes.forEach(cb => {
                cb.addEventListener('change', function () {
                    updateItemBorder(this);
                    updateCheckoutState();
                });
            });

            // Init
            checkboxes.forEach(cb => updateItemBorder(cb));
            updateCheckoutState();

        });
    </script>
@endsection