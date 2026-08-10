@extends('layouts.app')

@section('title', 'Checkout — Pizza Viva')

@section('content')
    <div class="py-12 max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8" x-data="checkoutPage()" x-init="initCheckout()">
        <!-- Page Header -->
        <div class="mb-12" data-aos="fade-down">
            <h1 class="text-3xl sm:text-5xl font-extrabold text-[#1E1E1E] font-serif tracking-tight">Checkout</h1>
            <p class="text-stone-500 mt-2">Please review your order bag and complete the checkout process.</p>
        </div>

        <!-- Empty State -->
        <template x-if="cart.length === 0">
            <div class="text-center py-24 bg-white border border-stone-200 rounded-3xl shadow-sm">
                <div class="w-20 h-20 bg-stone-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fa-solid fa-bag-shopping text-4xl text-stone-300"></i>
                </div>
                <h2 class="text-2xl font-bold text-[#1E1E1E] font-serif mb-2">Your Bag is Empty</h2>
                <p class="text-stone-500 mb-8 max-w-sm mx-auto">Add some of our artisan pizzas to your bag to proceed with
                    checkout.</p>
                <a href="{{ route('menu.index') }}"
                    class="btn-yellow px-8 py-3 rounded-full text-sm font-bold uppercase tracking-wider shadow-sm">
                    Explore Our Menu
                </a>
            </div>
        </template>

        <!-- Checkout Form and Summary -->
        <template x-if="cart.length > 0">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- Left Side: Details Form -->
                <form @submit.prevent="submitOrder()" class="lg:col-span-7 space-y-6" data-aos="fade-up">
                    @csrf

                    <!-- Contact Information -->
                    <div class="bg-white border border-stone-200 rounded-3xl p-6 sm:p-8 space-y-4 shadow-sm">
                        <h2
                            class="text-xl font-bold text-[#1E1E1E] font-serif border-b border-stone-100 pb-3 flex items-center space-x-2">
                            <i class="fa-solid fa-user text-[#F37021]"></i>
                            <span>Contact Information</span>
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Full
                                    Name</label>
                                <input type="text" x-model="form.name" required
                                    class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Phone
                                    Number</label>
                                <input type="text" x-model="form.phone" required
                                    class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Email
                                Address</label>
                            <input type="email" x-model="form.email" required
                                class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all">
                        </div>
                    </div>

                    <!-- Order Type & Address Selection -->
                    <div class="bg-white border border-stone-200 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
                        <h2
                            class="text-xl font-bold text-[#1E1E1E] font-serif border-b border-stone-100 pb-3 flex items-center space-x-2">
                            <i class="fa-solid fa-truck text-[#F37021]"></i>
                            <span>Order Method</span>
                        </h2>

                        <!-- Order Type Selector Tabs -->
                        <div class="grid grid-cols-2 p-1 bg-[#F3F4F6] rounded-2xl border border-stone-100">
                            <button type="button" @click="setOrderType('delivery')"
                                :class="form.order_type === 'delivery' ? 'bg-[#FDB813] text-black font-bold shadow-sm' : 'text-stone-500 hover:text-black font-semibold'"
                                class="py-2.5 rounded-xl text-sm transition-all uppercase tracking-wider">
                                Delivery
                            </button>
                            <button type="button" @click="setOrderType('pickup')"
                                :class="form.order_type === 'pickup' ? 'bg-[#FDB813] text-black font-bold shadow-sm' : 'text-stone-500 hover:text-black font-semibold'"
                                class="py-2.5 rounded-xl text-sm transition-all uppercase tracking-wider">
                                Pickup
                            </button>
                        </div>

                        <!-- Delivery Options -->
                        <div x-show="form.order_type === 'delivery'" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Delivery
                                    Address</label>
                                <input type="text" x-model="form.address" :required="form.order_type === 'delivery'"
                                    class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all"
                                    placeholder="House/Apartment/Suite, Street Address">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">City</label>
                                    <input type="text" x-model="form.city" :required="form.order_type === 'delivery'"
                                        class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">State</label>
                                    <input type="text" x-model="form.state" :required="form.order_type === 'delivery'"
                                        class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">ZIP
                                        Code</label>
                                    <input type="text" x-model="form.zip_code" :required="form.order_type === 'delivery'"
                                        class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Delivery
                                    Instructions (Optional)</label>
                                <textarea x-model="form.delivery_instructions" rows="2"
                                    class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all"
                                    placeholder="Leave at the door, buzz code, etc."></textarea>
                            </div>
                        </div>

                        <!-- Pickup Options -->
                        <div x-show="form.order_type === 'pickup'" class="space-y-4" style="display: none;">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Select
                                    Pickup Location</label>
                                <select x-model="form.store_id" :required="form.order_type === 'pickup'"
                                    @change="updateStoreDetails()"
                                    class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3.5 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all">
                                    <option value="">Choose a store...</option>
                                    @isset($stores)
                                        @foreach($stores as $store)
                                            <option value="{{ $store->id }}" data-tax="{{ $store->tax_rate }}">{{ $store->name }}
                                                ({{ $store->address }})</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Details -->
                    <div class="bg-white border border-stone-200 rounded-3xl p-6 sm:p-8 space-y-4 shadow-sm">
                        <h2
                            class="text-xl font-bold text-[#1E1E1E] font-serif border-b border-stone-100 pb-3 flex items-center space-x-2">
                            <i class="fa-solid fa-credit-card text-[#F37021]"></i>
                            <span>Payment Method</span>
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label
                                class="flex items-center justify-between p-4 rounded-2xl border-2 cursor-pointer transition-all"
                                :class="form.payment_method === 'cod' ? 'border-[#FDB813] bg-[#FDB813]/5' : 'border-stone-200 hover:bg-stone-50'">
                                <div class="flex items-center space-x-3">
                                    <input type="radio" value="cod" x-model="form.payment_method"
                                        class="text-[#FDB813] focus:ring-[#FDB813]">
                                    <span class="text-sm font-bold text-[#1E1E1E]">Cash on Delivery</span>
                                </div>
                                <i class="fa-solid fa-money-bill-wave text-stone-400 text-lg"></i>
                            </label>

                            <label
                                class="flex items-center justify-between p-4 rounded-2xl border-2 cursor-pointer transition-all"
                                :class="form.payment_method === 'card' ? 'border-[#FDB813] bg-[#FDB813]/5' : 'border-stone-200 hover:bg-stone-50'">
                                <div class="flex items-center space-x-3">
                                    <input type="radio" value="card" x-model="form.payment_method"
                                        class="text-[#FDB813] focus:ring-[#FDB813]">
                                    <span class="text-sm font-bold text-[#1E1E1E]">Credit/Debit Card</span>
                                </div>
                                <i class="fa-solid fa-credit-card text-stone-400 text-lg"></i>
                            </label>
                        </div>

                        <!-- Payment Web SDKs Card Form -->
                        <div x-show="form.payment_method === 'card'"
                            class="mt-4 p-5 border border-stone-100 bg-stone-50 rounded-2xl space-y-3" x-transition>

                            <!-- Square UI -->
                            <template x-if="paymentGateway === 'square'">
                                <div>
                                    <div x-show="window.location.hostname === '127.0.0.1'"
                                        class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-800 space-y-2 mb-2">
                                        <p class="font-bold flex items-center"><i
                                                class="fa-solid fa-triangle-exclamation mr-1.5 text-amber-600 text-sm"></i>
                                            Local Testing Notice</p>
                                        <p>Square Web Payments SDK only supports <strong>localhost</strong> for non-HTTPS
                                            local development. Please switch to localhost to test card payments.</p>
                                        <a :href="'http://localhost:8000/checkout?import_cart=' + encodeURIComponent(localStorage.getItem('viva_cart') || '[]')"
                                            class="inline-block bg-[#F37021] text-white px-3.5 py-2 rounded-xl font-bold text-[11px] hover:bg-[#d65f18] transition-colors shadow-sm uppercase tracking-wider">
                                            Switch to localhost (Keeps Cart)
                                        </a>
                                    </div>
                                    <label
                                        class="block text-[10px] font-bold uppercase tracking-wider text-stone-600 mb-2">Card
                                        Details <span class="text-stone-400 font-normal">(Secured by Square)</span></label>
                                    <div id="card-container"
                                        class="min-h-[90px] bg-white border border-stone-200 rounded-xl overflow-hidden">
                                    </div>
                                </div>
                            </template>

                            <!-- Clover UI -->
                            <template x-if="paymentGateway === 'clover'">
                                <div class="space-y-3">
                                    <div x-show="cloverIsMock"
                                        class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-900 space-y-1.5">
                                        <p class="font-bold flex items-center">
                                            <i class="fa-solid fa-flask mr-1.5 text-emerald-600 text-sm"></i>
                                            Localhost Clover mock mode
                                        </p>
                                        <p>Card payments and POS sync are simulated without live keys.</p>
                                    </div>

                                    <div x-show="!cloverIsMock && !cloverCanProcessCards"
                                        class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-950 space-y-1.5">
                                        <p class="font-bold flex items-center">
                                            <i class="fa-solid fa-triangle-exclamation mr-1.5 text-amber-600 text-sm"></i>
                                            Online card pay not ready
                                        </p>
                                        <p>Add <code class="bg-white/70 px-1 rounded">CLOVER_PUBLIC_KEY</code> to enable card charges.</p>
                                    </div>

                                    <div x-show="cloverIsMock || cloverCanProcessCards">
                                        <label
                                            class="block text-[10px] font-bold uppercase tracking-wider text-stone-600 mb-3">Card
                                            Details <span class="text-stone-400 font-normal">(Secured by Clover)</span></label>

                                        {{-- Separate field boxes with clear gaps and dividers --}}
                                        <div id="clover-card-form" class="clover-card-form space-y-3">
                                            <div class="clover-field-box">
                                                <span class="clover-field-label">Card number</span>
                                                <div id="clover-card-number" class="clover-iframe-host"></div>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                <div class="clover-field-box">
                                                    <span class="clover-field-label">Expiry</span>
                                                    <div id="clover-card-date" class="clover-iframe-host"></div>
                                                </div>
                                                <div class="clover-field-box">
                                                    <span class="clover-field-label">CVV</span>
                                                    <div id="clover-card-cvv" class="clover-iframe-host"></div>
                                                </div>
                                                <div class="clover-field-box" x-show="!cloverIsMock">
                                                    <span class="clover-field-label">ZIP</span>
                                                    <div id="clover-card-postal" class="clover-iframe-host"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <p x-show="cardError" x-text="cardError" class="text-xs text-red-500 font-medium"></p>
                            <div class="flex items-center space-x-2 mt-1">
                                <i class="fa-solid fa-lock text-emerald-500 text-xs"></i>
                                <span class="text-[10px] text-stone-400">Your card details are encrypted and processed
                                    securely. We never store your card data.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" :disabled="submitting"
                        class="w-full btn-orange py-4 rounded-3xl uppercase tracking-wider text-sm font-bold shadow-md shadow-[#F37021]/15 hover:scale-[1.01] transition-all flex items-center justify-center space-x-2"
                        data-aos="zoom-in">
                        <span x-show="!submitting" x-text="'Place Order (' + formatPrice(calculateTotal()) + ')'"></span>
                        <span x-show="submitting"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Placing Order...</span>
                    </button>
                </form>

                <!-- Right Side: Order Summary -->
                <div class="lg:col-span-5 sticky top-28 space-y-6" data-aos="fade-up">
                    <div class="bg-white border border-stone-200 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
                        <h2
                            class="text-xl font-bold text-[#1E1E1E] font-serif border-b border-stone-100 pb-3 flex items-center justify-between">
                            <span>Order Summary</span>
                            <span class="text-xs text-stone-400 font-sans" x-text="cart.length + ' item(s)'"></span>
                        </h2>

                        <div class="divide-y divide-stone-100 max-h-[40vh] overflow-y-auto pr-2">
                            <template x-for="(item, index) in cart" :key="index">
                                <div class="py-4 flex justify-between items-start space-x-4">
                                    <div class="space-y-1 flex-grow">
                                        <h4 class="text-sm font-bold text-[#1E1E1E]" x-text="item.product.name"></h4>
                                        <div class="flex items-center space-x-2">
                                            <span
                                                class="text-xs bg-stone-100 text-stone-700 py-0.5 px-2 rounded-full font-medium"
                                                x-text="item.variation.name"></span>
                                            <span class="text-xs text-stone-500" x-text="'Qty: ' + item.quantity"></span>
                                        </div>
                                        <template x-if="item.toppings && item.toppings.length > 0">
                                            <p class="text-[10px] text-stone-400">
                                                Toppings added
                                            </p>
                                        </template>
                                    </div>
                                    <span class="text-sm font-extrabold text-[#1E1E1E]"
                                        x-text="formatPrice(item.price * item.quantity)"></span>
                                </div>
                            </template>
                        </div>

                        <div class="border-t border-stone-100 pt-4 space-y-2">
                            <div class="flex justify-between text-sm text-stone-600">
                                <span>Subtotal</span>
                                <span class="font-semibold text-[#1E1E1E]" x-text="formatPrice(calculateSubtotal())"></span>
                            </div>
                            <div class="flex justify-between text-sm text-stone-600">
                                <span>Tax (Est.)</span>
                                <span class="font-semibold text-[#1E1E1E]" x-text="formatPrice(calculateTax())"></span>
                            </div>
                            <div class="flex justify-between text-sm text-stone-600"
                                x-show="form.order_type === 'delivery'">
                                <span>Delivery Fee</span>
                                <span class="font-semibold text-[#1E1E1E]" x-text="formatPrice(deliveryFee)"></span>
                            </div>
                            <div
                                class="flex justify-between text-lg font-bold border-t border-stone-100 pt-3 text-[#1E1E1E]">
                                <span>Grand Total</span>
                                <span class="text-xl font-extrabold text-[#F37021]"
                                    x-text="formatPrice(calculateTotal())"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Coupon Box -->
                    <div class="bg-white border border-stone-200 rounded-3xl p-6 sm:p-8 shadow-sm">
                        <h3 class="text-sm font-bold text-[#1E1E1E] uppercase tracking-wider mb-3">Have a Coupon?</h3>
                        <div class="flex space-x-2">
                            <input type="text" x-model="form.coupon_code" placeholder="Enter code"
                                class="flex-grow bg-[#F3F4F6] border border-transparent rounded-2xl py-2.5 px-4 text-sm focus:outline-none focus:bg-white focus:border-[#FDB813] transition-all">
                            <button type="button"
                                class="btn-yellow px-4 rounded-2xl text-xs font-bold uppercase tracking-wider shadow-sm">Apply</button>
                        </div>
                    </div>
                </div>

            </div>
        </template>
    </div>

    <script>
        function checkoutPage() {
            return {
                paymentGateway: '{{ $paymentGateway ?? "clover" }}',
                cloverPublicKey: @js($cloverPublicKey ?? ''),
                cloverIsMock: @js((bool) ($cloverIsMock ?? true)),
                cloverCanProcessCards: @js((bool) ($cloverCanProcessCards ?? false)),
                cloverEnvironment: @js($cloverEnvironment ?? 'sandbox'),
                cart: [],
                submitting: false,
                deliveryFee: 3.50,
                taxRate: 0.06, // Default 6%
                cardError: '',
                squareCard: null,
                cloverInstance: null,
                initializingCard: false,
                form: {
                    name: '{{ auth()->user()->name ?? "" }}',
                    phone: '{{ auth()->user()->phone ?? "" }}',
                    email: '{{ auth()->user()->email ?? "" }}',
                    order_type: 'delivery',
                    address: '',
                    zip_code: '',
                    city: '',
                    state: '',
                    delivery_instructions: '',
                    store_id: '',
                    payment_method: 'cod',
                    payment_token: '',
                    coupon_code: ''
                },

                initCheckout() {
                    // Check if we need to import a cart from another host (e.g. 127.0.0.1 to localhost)
                    const urlParams = new URLSearchParams(window.location.search);
                    const importedCart = urlParams.get('import_cart');
                    if (importedCart) {
                        try {
                            const parsed = JSON.parse(decodeURIComponent(importedCart));
                            if (Array.isArray(parsed)) {
                                localStorage.setItem('viva_cart', JSON.stringify(parsed));
                                window.dispatchEvent(new CustomEvent('cart-updated'));
                            }
                        } catch (e) {
                            console.error('Failed to import cart', e);
                        }
                        // Clean URL
                        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                        window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
                    }

                    let rawCart = JSON.parse(localStorage.getItem('viva_cart') || '[]');

                    // Filter out any old/invalid cart formats from before the database integration
                    this.cart = rawCart.filter(item => item.product && item.product.id && item.variation && item.variation.id);

                    if (this.cart.length !== rawCart.length) {
                        localStorage.setItem('viva_cart', JSON.stringify(this.cart));
                        window.dispatchEvent(new CustomEvent('cart-updated')); // trigger update
                    }

                    // Initialize location details if in session
                    const orderLoc = @json(session('order_location'));
                    if (orderLoc) {
                        this.form.order_type = orderLoc.type || 'delivery';
                        if (this.form.order_type === 'delivery') {
                            this.form.address = orderLoc.address || '';
                            this.form.zip_code = orderLoc.zip_code || '';
                            this.form.city = orderLoc.city || '';
                            this.deliveryFee = 3.50;
                        } else {
                            this.form.store_id = orderLoc.store_id || '';
                            this.deliveryFee = 0;
                            // Wait for DOM to update tax rate
                            setTimeout(() => this.updateStoreDetails(), 100);
                        }
                    }

                    // Watch payment method — auto-init card form when user selects card
                    this.$watch('form.payment_method', async (val) => {
                        if (val === 'card') {
                            // Wait enough time for Alpine's x-transition to finish so the div has a height
                            setTimeout(() => this.initPaymentForm(), 400);
                        }
                    });
                },

                setOrderType(type) {
                    this.form.order_type = type;
                    this.deliveryFee = type === 'delivery' ? 3.50 : 0;
                },

                updateStoreDetails() {
                    if (this.form.store_id) {
                        const select = document.querySelector('select[x-model="form.store_id"]');
                        const option = select.options[select.selectedIndex];
                        if (option && option.dataset.tax) {
                            this.taxRate = parseFloat(option.dataset.tax);
                        }
                    }
                },

                calculateSubtotal() {
                    return this.cart.reduce((sum, item) => sum + parseFloat(item.price * item.quantity), 0);
                },

                calculateTax() {
                    return this.calculateSubtotal() * this.taxRate;
                },

                calculateTotal() {
                    let total = this.calculateSubtotal() + this.calculateTax();
                    if (this.form.order_type === 'delivery') {
                        total += this.deliveryFee;
                    }
                    return total;
                },

                formatPrice(price) {
                    return '$' + parseFloat(price).toFixed(2);
                },

                async initPaymentForm() {
                    if (this.initializingCard) return;

                    if (this.paymentGateway === 'clover') {
                        await this.initCloverCard();
                    } else {
                        await this.initSquareCard();
                    }
                },

                async initCloverCard() {
                    if (this.cloverInstance) return;
                    this.initializingCard = true;
                    this.cardError = '';

                    try {
                        // Localhost-friendly mock only when mock mode is intentional
                        if (this.cloverIsMock) {
                            this.cloverInstance = {
                                createToken: async () => ({
                                    token: 'clv_mock_tok_' + Math.random().toString(36).substring(2, 12),
                                }),
                            };

                            const numberEl = document.getElementById('clover-card-number');
                            const dateEl = document.getElementById('clover-card-date');
                            const cvvEl = document.getElementById('clover-card-cvv');
                            if (numberEl) numberEl.innerText = '•••• •••• •••• 4242';
                            if (dateEl) dateEl.innerText = 'MM / YY';
                            if (cvvEl) cvvEl.innerText = 'CVV';
                            const postalEl = document.getElementById('clover-card-postal');
                            if (postalEl) postalEl.innerText = 'ZIP';

                            this.initializingCard = false;
                            return;
                        }

                        if (!this.cloverPublicKey || String(this.cloverPublicKey).startsWith('mock-')) {
                            this.cardError = 'Clover ecommerce public key is missing. Add CLOVER_PUBLIC_KEY to enable card payments.';
                            this.initializingCard = false;
                            return;
                        }

                        const cloverUrl = this.cloverEnvironment === 'production'
                            ? 'https://checkout.clover.com/sdk.js'
                            : 'https://checkout.sandbox.dev.clover.com/sdk.js';

                        if (typeof window.Clover === 'undefined') {
                            await new Promise((resolve, reject) => {
                                const script = document.createElement('script');
                                script.src = cloverUrl;
                                script.onload = resolve;
                                script.onerror = reject;
                                document.head.appendChild(script);
                            });
                        }

                        const clover = new window.Clover(this.cloverPublicKey);
                        const elements = clover.elements();

                        // Compact field styling inside Clover iframes
                        // (Our layout uses external labels so each box is separate & clear.)
                        const elOptions = {
                            styles: {
                                body: {
                                    fontFamily: 'Outfit, system-ui, sans-serif',
                                    fontSize: '14px',
                                    margin: '0',
                                    padding: '0',
                                },
                                input: {
                                    fontSize: '14px',
                                    fontFamily: 'Outfit, system-ui, sans-serif',
                                    color: '#1E1E1E',
                                    letterSpacing: '0.02em',
                                },
                                // Prefer our outer labels; hide default iframe labels when supported
                                label: {
                                    fontSize: '0',
                                    lineHeight: '0',
                                    height: '0',
                                    margin: '0',
                                    padding: '0',
                                    overflow: 'hidden',
                                    color: 'transparent',
                                },
                            },
                        };

                        const cardNumber = elements.create('CARD_NUMBER', elOptions);
                        const cardDate = elements.create('CARD_DATE', elOptions);
                        const cardCvv = elements.create('CARD_CVV', elOptions);
                        const cardPostal = elements.create('CARD_POSTAL_CODE', elOptions);

                        cardNumber.mount('#clover-card-number');
                        cardDate.mount('#clover-card-date');
                        cardCvv.mount('#clover-card-cvv');
                        cardPostal.mount('#clover-card-postal');

                        this.cloverInstance = clover;
                    } catch (e) {
                        console.error('Clover SDK init error', e);
                        this.cardError = 'Could not load Clover form. Check CLOVER_PUBLIC_KEY / network.';
                    } finally {
                        this.initializingCard = false;
                    }
                },

                async initSquareCard() {
                    if (this.squareCard) return;
                    this.initializingCard = true;
                    this.cardError = '';

                    try {
                        if (typeof window.Square === 'undefined') {
                            await new Promise((resolve, reject) => {
                                const script = document.createElement('script');
                                script.src = "https://sandbox.web.squarecdn.com/v1/square.js";
                                script.onload = resolve;
                                script.onerror = reject;
                                document.head.appendChild(script);
                            });
                        }

                        const appId = '{{ env("SQUARE_APPLICATION_ID", config("payment.gateways.square.application_id")) }}';
                        const locId = '{{ env("SQUARE_LOCATION_ID", config("payment.gateways.square.location_id")) }}';
                        if (!appId || !locId) {
                            this.cardError = 'Square SDK configuration is missing.';
                            this.initializingCard = false;
                            return;
                        }
                        const payments = window.Square.payments(appId, locId);
                        this.squareCard = await payments.card();
                        await this.squareCard.attach('#card-container');
                    } catch (e) {
                        console.error('Square SDK init error', e);
                        this.cardError = 'Could not load the secure card form. Please refresh.';
                    } finally {
                        this.initializingCard = false;
                    }
                },

                async submitOrder() {
                    this.submitting = true;
                    this.cardError = '';

                    // ── Step 1: Web SDK Tokenization ──
                    if (this.form.payment_method === 'card') {
                        if (this.paymentGateway === 'clover') {
                            if (!this.cloverIsMock && !this.cloverCanProcessCards) {
                                this.cardError = 'Online card pay is not ready yet. Choose Cash on delivery, or add CLOVER_PUBLIC_KEY.';
                                this.submitting = false;
                                return;
                            }
                            if (!this.cloverInstance) await this.initCloverCard();
                            if (!this.cloverInstance) {
                                this.cardError = 'Secure card form failed to load.';
                                this.submitting = false;
                                return;
                            }
                            try {
                                const result = await this.cloverInstance.createToken();
                                if (result.errors) {
                                    const errs = result.errors;
                                    this.cardError = (Array.isArray(errs)
                                        ? errs.map(e => e.message || e).join('. ')
                                        : Object.values(errs).join('. ')) || 'Invalid card details.';
                                    this.submitting = false;
                                    return;
                                }
                                const token = result.token || result.id || '';
                                if (!token || !String(token).startsWith('clv_')) {
                                    this.cardError = 'Could not secure the card (no Clover token). Re-enter card details and try again.';
                                    console.error('Clover createToken unexpected result', result);
                                    this.submitting = false;
                                    return;
                                }
                                this.form.payment_token = token;
                            } catch (e) {
                                this.cardError = 'Payment verification failed.';
                                this.submitting = false;
                                return;
                            }
                        } else {
                            // Square tokenization
                            if (!this.squareCard) await this.initSquareCard();
                            if (!this.squareCard) {
                                this.cardError = 'Secure card form failed to load.';
                                this.submitting = false;
                                return;
                            }
                            try {
                                const tokenResult = await this.squareCard.tokenize();
                                if (tokenResult.status === 'OK') {
                                    this.form.payment_token = tokenResult.token;
                                } else {
                                    this.cardError = tokenResult.errors?.[0]?.message || 'Card tokenization failed.';
                                    this.submitting = false;
                                    return;
                                }
                            } catch (e) {
                                this.cardError = 'Payment processing error. Please try again.';
                                this.submitting = false;
                                return;
                            }
                        }
                    } else {
                        this.form.payment_token = '';
                    }

                    // ── Step 2: Submit order to backend ──
                    try {
                        const response = await fetch('{{ route("checkout.place") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                ...this.form,
                                cart: this.cart
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            localStorage.removeItem('viva_cart');
                            window.dispatchEvent(new CustomEvent('cart-updated'));
                            this.cart = [];
                            window.location.href = data.redirect;
                        } else {
                            let errorMsg = data.message || 'Something went wrong.';
                            if (data.errors) {
                                errorMsg += '\n' + Object.values(data.errors).flat().join('\n');
                            }
                            alert(errorMsg);
                        }
                    } catch (e) {
                        console.error('Checkout submission failed', e);
                        alert('An error occurred during checkout. Please try again.');
                    } finally {
                        this.submitting = false;
                    }
                }
            };
        }

    </script>

    <style>
        /* Professional Clover card form — each field is its own box with space between */
        .clover-card-form {
            width: 100%;
        }

        .clover-card-form .clover-field-box {
            background: #fff;
            border: 1px solid #e7e5e4;
            border-radius: 0.75rem;
            padding: 0.55rem 0.85rem 0.4rem;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .clover-card-form .clover-field-box:focus-within {
            border-color: #FDB813;
            box-shadow: 0 0 0 3px rgba(253, 184, 19, 0.18);
        }

        .clover-card-form .clover-field-label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #78716c;
            margin-bottom: 0.15rem;
            line-height: 1.2;
        }

        .clover-card-form .clover-iframe-host {
            width: 100%;
            height: 36px;
            max-height: 36px;
            overflow: hidden;
            position: relative;
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #a8a29e;
            line-height: 36px;
        }

        /* Clover injects wrapper + iframe; keep host height steady */
        .clover-card-form .clover-iframe-host > div,
        .clover-card-form .clover-iframe-host iframe {
            width: 100% !important;
            height: 36px !important;
            min-height: 36px !important;
            max-height: 36px !important;
            border: 0 !important;
            display: block !important;
        }
    </style>

    {{-- Note: SDKs are now dynamically loaded in JS to prevent conflicting multiple payment iframes --}}
@endsection