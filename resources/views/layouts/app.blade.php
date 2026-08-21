<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Consent defaults and the tag loader must come before everything else --}}
    @include('partials.analytics-head')

    @isset($seo)
        @include('partials.seo-head')
    @else
        {{-- Composer did not run (non-standard render path): still never blank --}}
        <title>@yield('title', config('seo.defaults.title'))</title>
        <meta name="description" content="@yield('meta_description', config('seo.defaults.description'))">
    @endisset

    <link rel="icon" type="image/png"
        href="{{ asset('assets/images/dock-pizza-logo.png') }}@if(file_exists(public_path('assets/images/dock-pizza-logo.png')))?v={{ filemtime(public_path('assets/images/dock-pizza-logo.png')) }}@endif">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&display=swap"
        rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS & JS (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>

<body class="bg-[#F9F9FB] text-[#1E1E1E] font-sans antialiased overflow-x-clip"
    x-data="{ mobileMenuOpen: false, cartOpen: false, locationModalOpen: false }"
    @add-to-cart.window="if($event.detail) { $store.cart.add($event.detail); cartOpen = true }" @cart-updated.window="">

    @include('partials.analytics-body')

    <!-- Navigation Header (Cheezious Style) -->
    <header class="sticky top-0 z-50 w-full bg-white border-b border-stone-100 header-shadow">
        <div class="w-full flex items-center justify-between px-3 py-2 sm:px-6 lg:px-8 gap-2 sm:gap-4">

            <!-- Left: Toggle & Logo -->
            <div class="flex items-center space-x-2 sm:space-x-3">
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                    class="text-stone-700 hover:text-black p-1 sm:p-2 focus:outline-none -ml-1 sm:ml-0">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <a href="{{ route('home') }}" class="flex items-center shrink-0">
                    <img src="{{ asset('assets/images/dock-pizza-logo.png') }}@if(file_exists(public_path('assets/images/dock-pizza-logo.png')))?v={{ filemtime(public_path('assets/images/dock-pizza-logo.png')) }}@endif"
                        alt="Dock Pizza — Fresh off the dock" class="dock-brand-logo dock-brand-logo--header"
                        fetchpriority="high" decoding="async">
                </a>
            </div>

            <!-- Desktop Navigation Links -->
            <nav class="hidden lg:flex items-center space-x-6 ml-6">
                <a href="{{ route('menu.index') }}"
                    class="text-sm font-bold text-stone-600 hover:text-[#E07B2D] transition-colors uppercase tracking-wider">Menu</a>
                <a href="{{ route('catering.index') }}"
                    class="text-sm font-bold text-stone-600 hover:text-[#E07B2D] transition-colors uppercase tracking-wider">Catering</a>
                <a href="{{ route('blog.index') }}"
                    class="text-sm font-bold text-stone-600 hover:text-[#E07B2D] transition-colors uppercase tracking-wider">Blog</a>
            </nav>

            <!-- Center: Search Input & Location Selection -->
            <div class="hidden md:flex flex-grow max-w-2xl items-center space-x-3">
                <!-- Search Box -->
                <form action="{{ route('menu.index') }}" method="GET" class="relative flex-grow">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-stone-400"></i>
                    <input type="text" name="search" x-model="$store.search.query" placeholder="Find in Dock Pizza..."
                        class="w-full bg-[#F3F4F6] border border-transparent rounded-full py-2.5 pl-11 pr-4 text-sm text-[#1B3A5C] focus:outline-none focus:bg-white focus:border-[#E07B2D] transition-all">
                </form>

                <!-- Location Selector Trigger -->
                <button @click="locationModalOpen = true"
                    class="flex items-center space-x-2 bg-[#F3F4F6] hover:bg-[#E5E7EB] border border-transparent rounded-full py-2.5 px-6 text-sm font-semibold text-stone-700 transition-all">
                    <i class="fa-solid fa-location-dot text-[#E07B2D]"></i>
                    <span class="truncate max-w-[180px]">
                        @if(session()->has('order_location'))
                            @if(session('order_location.type') === 'delivery')
                                Delivery:
                                {{ session('order_location.address') ? session('order_location.address') . ' (' . session('order_location.zip_code') . ')' : session('order_location.zip_code') }}
                            @else
                                Pickup: {{ session('order_location.store_name') }}
                            @endif
                        @else
                            Enter Delivery Location
                        @endif
                    </span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-stone-400"></i>
                </button>
            </div>

            <!-- Right: Cart & Login Buttons -->
            <div class="flex items-center space-x-2 sm:space-x-3">
                <!-- Cart Button -->
                <button @click="cartOpen = true"
                    class="btn-yellow flex items-center space-x-1 sm:space-x-2 rounded-full py-2 px-3 sm:py-2.5 sm:px-6 text-sm font-bold shadow-sm shadow-[#FDB813]/10">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="hidden sm:inline">CART</span>
                    <span
                        class="bg-[#1E1E1E] text-white text-[11px] font-bold rounded-full w-5 h-5 flex items-center justify-center"
                        x-text="$store.cart.items.length">0</span>
                </button>

                <!-- Auth Buttons -->
                @auth
                    <a href="{{ route('orders.history') }}"
                        class="btn-yellow flex items-center space-x-1 sm:space-x-2 rounded-full py-2 px-3 sm:py-2.5 sm:px-6 text-sm font-bold shadow-sm shadow-[#FDB813]/10">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span class="hidden sm:inline">MY ORDERS</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="m-0 p-0 flex items-center">
                        @csrf
                        <button type="submit"
                            class="border border-[#FDB813] text-[#1E1E1E] hover:bg-[#FDB813]/10 rounded-full py-2 px-3 sm:py-2.5 sm:px-6 text-sm font-bold transition-all flex items-center space-x-1 sm:space-x-2">
                            <i class="fa-solid fa-arrow-right-from-bracket sm:hidden"></i>
                            <span class="hidden sm:inline">LOGOUT</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="btn-yellow flex items-center space-x-1 sm:space-x-2 rounded-full py-2 px-3 sm:py-2.5 sm:px-6 text-sm font-bold shadow-sm shadow-[#FDB813]/10">
                        <i class="fa-solid fa-user text-xs"></i>
                        <span class="hidden sm:inline">LOGIN</span>
                    </a>
                @endauth
            </div>
        </div>

        <!-- Mobile Left-Sliding Drawer Menu -->
        <div x-show="mobileMenuOpen" class="fixed inset-0 z-50" style="display: none;">
            <!-- Backdrop -->
            <div x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-sm"
                @click="mobileMenuOpen = false"></div>

            <!-- Drawer Content -->
            <div class="fixed inset-y-0 left-0 max-w-xs w-full bg-white shadow-2xl flex flex-col justify-between"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full">

                <!-- Drawer Body -->
                <div>
                    <!-- Drawer Header (User / Login Section) -->
                    <div class="bg-stone-50 border-b border-stone-100 p-6">
                        <div class="flex items-center space-x-4">
                            <div
                                class="w-12 h-12 rounded-full bg-[#E07B2D] flex items-center justify-center text-white">
                                <i class="fa-solid fa-user text-lg"></i>
                            </div>
                            <div class="flex-grow">
                                @auth
                                    <h4 class="text-sm font-bold text-[#1E1E1E]">{{ auth()->user()->name }}</h4>
                                    <span class="text-[10px] text-stone-500 uppercase font-bold tracking-wider">Active
                                        Customer</span>
                                @else
                                    <h4 class="text-sm font-bold text-stone-500">Login to explore</h4>
                                    <span class="text-xs font-extrabold text-[#1E1E1E]">World of flavors</span>
                                @endauth
                            </div>
                            @guest
                                <a href="{{ route('login') }}"
                                    @click.prevent="mobileMenuOpen = false; setTimeout(() => window.location.href = '{{ route('login') }}', 150)"
                                    class="border border-[#FDB813] hover:bg-[#FDB813]/10 text-stone-800 rounded-full py-1.5 px-4 text-xs font-bold transition-all">
                                    LOGIN
                                </a>
                            @endguest
                        </div>
                    </div>

                    <!-- Navigation Items -->
                    <div class="p-6 space-y-4">
                        <a href="{{ route('menu.index') }}"
                            @click.prevent="mobileMenuOpen = false; setTimeout(() => window.location.href = '{{ route('menu.index') }}', 150)"
                            class="flex items-center space-x-3 text-stone-700 hover:text-black font-semibold py-2">
                            <i class="fa-solid fa-border-all text-stone-400 w-5"></i>
                            <span>Explore Menu</span>
                        </a>
                        <a href="{{ route('home') }}#locations"
                            @click.prevent="mobileMenuOpen = false; setTimeout(() => window.location.href = '{{ route('home') }}#locations', 150)"
                            class="flex items-center space-x-3 text-stone-700 hover:text-black font-semibold py-2">
                            <i class="fa-solid fa-store text-stone-400 w-5"></i>
                            <span>Branch Locator</span>
                        </a>
                        <a href="{{ route('catering.index') }}"
                            @click.prevent="mobileMenuOpen = false; setTimeout(() => window.location.href = '{{ route('catering.index') }}', 150)"
                            class="flex items-center space-x-3 text-stone-700 hover:text-black font-semibold py-2">
                            <i class="fa-solid fa-utensils text-stone-400 w-5"></i>
                            <span>Catering</span>
                        </a>
                        <a href="{{ route('blog.index') }}"
                            @click.prevent="mobileMenuOpen = false; setTimeout(() => window.location.href = '{{ route('blog.index') }}', 150)"
                            class="flex items-center space-x-3 text-stone-700 hover:text-black font-semibold py-2">
                            <i class="fa-solid fa-newspaper text-stone-400 w-5"></i>
                            <span>Blog</span>
                        </a>
                        @auth
                            <a href="{{ route('orders.history') }}"
                                @click.prevent="mobileMenuOpen = false; setTimeout(() => window.location.href = '{{ route('orders.history') }}', 150)"
                                class="flex items-center space-x-3 text-[#F37021] hover:text-[#D95D14] font-bold py-2 border-t border-stone-100 pt-4 mt-2">
                                <i class="fa-solid fa-clock-rotate-left w-5"></i>
                                <span>My Orders</span>
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Drawer Footer (Hotline Banner) -->
                <div class="bg-[#E07B2D] p-4 flex items-center justify-between">
                    <div class="flex items-center space-x-2 text-white">
                        <img src="{{ asset('assets/images/dock-pizza-logo.png') }}@if(file_exists(public_path('assets/images/dock-pizza-logo.png')))?v={{ filemtime(public_path('assets/images/dock-pizza-logo.png')) }}@endif"
                            alt="Dock Pizza" class="dock-brand-logo h-9 w-auto max-w-[120px] object-contain" width="120" height="36"
                            decoding="async">
                        <div class="flex flex-col">
                            <span class="text-sm font-bold uppercase tracking-wider leading-none mb-1">Hotline</span>
                            <span class="text-xs font-semibold">443-203-6404</span>
                        </div>
                    </div>
                    <a href="tel:4432036404"
                        class="w-8 h-8 rounded-full bg-black flex items-center justify-center text-white hover:scale-105 transition-all">
                        <i class="fa-solid fa-phone text-xs"></i>
                    </a>
                </div>

            </div>
        </div>
    </header>

    <!-- Page Notifications -->
    @if(session('success'))
        <div class="max-w-[1600px] mx-auto px-4 mt-4 sm:px-6 lg:px-8">
            <div
                class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center space-x-3">
                <i class="fa-solid fa-circle-check text-lg text-emerald-600"></i>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('info'))
        <div class="max-w-[1600px] mx-auto px-4 mt-4 sm:px-6 lg:px-8">
            <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl flex items-center space-x-3">
                <i class="fa-solid fa-circle-info text-lg text-amber-600"></i>
                <span class="text-sm font-semibold">{{ session('info') }}</span>
            </div>
        </div>
    @endif

    <!-- Main Content Area -->
    <main class="relative z-10">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-stone-200 mt-24">
        <div class="mx-auto max-w-[1600px] px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-8 items-start">
                <!-- Col 1: Brand -->
                <div class="space-y-4">
                    <a href="{{ route('home') }}" class="inline-flex items-center">
                        <img src="{{ asset('assets/images/dock-pizza-logo.png') }}@if(file_exists(public_path('assets/images/dock-pizza-logo.png')))?v={{ filemtime(public_path('assets/images/dock-pizza-logo.png')) }}@endif"
                            alt="Dock Pizza — Fresh off the dock"
                            class="dock-brand-logo h-[4.5rem] sm:h-20 w-auto max-w-[200px] object-contain object-left" width="200"
                            height="80" decoding="async">
                    </a>
                    <p class="text-stone-500 text-sm leading-relaxed max-w-xs">
                        Premium specialty pizzas, fresh salads, and crispy sides — fresh off the dock.
                    </p>
                </div>

                <!-- Col 2: Store Hours -->
                <div>
                    <h4 class="text-[#1B3A5C] font-bold text-sm uppercase tracking-wider mb-4">Hours of Operation</h4>
                    <ul class="text-stone-500 text-sm space-y-2">
                        <li>Monday - Thursday: 10am - 10pm</li>
                        <li>Friday - Saturday: 10am - 11pm</li>
                        <li>Sunday: 10am - 10pm</li>
                    </ul>
                </div>

                <!-- Col 3: Quick Links -->
                <div>
                    <h4 class="text-[#1B3A5C] font-bold text-sm uppercase tracking-wider mb-4">Quick Links</h4>
                    <ul class="text-stone-500 text-sm space-y-2">
                        <li><a href="{{ route('menu.index') }}" class="hover:text-[#E07B2D] transition-colors">Order
                                Online</a></li>
                        <li><a href="{{ route('catering.index') }}"
                                class="hover:text-[#E07B2D] transition-colors">Catering Packages</a></li>
                    </ul>
                </div>

                <!-- Col 4: Location (right side) -->
                <div class="lg:justify-self-end w-full max-w-sm">
                    <div
                        class="rounded-3xl border border-[#1B3A5C]/10 bg-gradient-to-br from-[#F5F0E8] to-white p-6 shadow-sm">
                        <div class="flex items-start gap-3 mb-4">
                            <span
                                class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#E07B2D]/10 text-[#E07B2D] border border-[#E07B2D]/15">
                                <i class="fa-solid fa-location-dot text-lg"></i>
                            </span>
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#E07B2D]">Find Us</p>
                                <h4 class="text-xl font-bold font-serif text-[#1B3A5C] leading-tight mt-0.5">Shady Side,
                                    MD</h4>
                            </div>
                        </div>

                        <p class="text-sm text-stone-600 leading-relaxed mb-5">
                            1484 Snug Harbor Rd<br>
                            Shady Side, MD 20764
                        </p>

                        <div class="flex flex-wrap gap-2">
                            <a href="https://www.google.com/maps/search/?api=1&query=1484+Snug+Harbor+Rd,+Shady+Side,+MD+20764"
                                target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-full bg-[#E07B2D] hover:bg-[#C96A22] text-white text-xs font-bold uppercase tracking-wider px-4 py-2.5 transition-colors shadow-sm">
                                <i class="fa-solid fa-map"></i> Directions
                            </a>
                            <a href="tel:4432036404"
                                class="inline-flex items-center gap-2 rounded-full border border-[#1B3A5C]/15 bg-white text-[#1B3A5C] text-xs font-bold uppercase tracking-wider px-4 py-2.5 hover:border-[#E07B2D] hover:text-[#E07B2D] transition-colors">
                                <i class="fa-solid fa-phone"></i> 443-203-6404
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-stone-200 mt-16 pt-8 text-center text-stone-400 text-xs">
                &copy; {{ date('Y') }} Dock Pizza. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Cart Drawer -->
    <div x-show="cartOpen" class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true"
        style="display: none;">
        <div x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="cartOpen = false"></div>
        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-0 sm:pl-10">
                    <div class="pointer-events-auto w-screen max-w-md" x-show="cartOpen"
                        x-transition:enter="transform transition ease-in-out duration-300"
                        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                        x-transition:leave="transform transition ease-in-out duration-300"
                        x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
                        <div class="flex h-full flex-col bg-white border-l border-stone-200 shadow-2xl">
                            <div class="flex-1 overflow-y-auto px-4 py-6 sm:px-6">
                                <div class="flex items-start justify-between">
                                    <h2 class="text-lg font-bold text-[#1E1E1E] font-serif" id="slide-over-title">Your
                                        Order Bag</h2>
                                    <button type="button" @click="cartOpen = false"
                                        class="text-stone-500 hover:text-black transition-colors">
                                        <i class="fa-solid fa-xmark text-lg"></i>
                                    </button>
                                </div>
                                <div class="mt-8">
                                    <!-- Empty Cart State -->
                                    <template x-if="$store.cart.items.length === 0">
                                        <div class="text-center py-12">
                                            <i class="fa-solid fa-bag-shopping text-5xl text-stone-300 mb-4"></i>
                                            <p class="text-stone-500 text-sm">Your order bag is empty.</p>
                                        </div>
                                    </template>

                                    <!-- Cart Items List -->
                                    <template x-if="$store.cart.items.length > 0">
                                        <div class="space-y-6">
                                            <div class="divide-y divide-stone-100 overflow-y-auto max-h-[50vh] pr-2">
                                                <template x-for="(item, index) in $store.cart.items" :key="index">
                                                    <div class="py-4 flex justify-between items-start space-x-4">
                                                        <div class="flex-grow space-y-1">
                                                            <h4 class="text-sm font-bold text-[#1E1E1E]"
                                                                x-text="item.product.name"></h4>
                                                            <p class="text-xs text-stone-500"
                                                                x-text="item.variation.name"></p>
                                                            <template x-if="item.toppings.length > 0">
                                                                <p class="text-[10px] text-stone-400">
                                                                    Toppings added
                                                                </p>
                                                            </template>
                                                        </div>
                                                        <div class="flex flex-col items-end space-y-2">
                                                            <span class="text-sm font-extrabold text-[#1E1E1E]"
                                                                x-text="'$' + parseFloat(item.price * item.quantity).toFixed(2)"></span>
                                                            <button @click="$store.cart.remove(index)"
                                                                class="text-stone-400 hover:text-rose-500 transition-colors">
                                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>

                                            <!-- Cart Total & Checkout -->
                                            <div class="border-t border-stone-200 pt-6 space-y-4">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-semibold text-stone-600">Subtotal</span>
                                                    <span class="text-xl font-extrabold text-[#1E1E1E]"
                                                        x-text="'$' + $store.cart.total.toFixed(2)"></span>
                                                </div>
                                                @if(request()->routeIs('checkout'))
                                                    <button @click="cartOpen = false"
                                                        class="w-full btn-orange flex items-center justify-center py-3 rounded-full uppercase tracking-wider text-sm shadow-md shadow-[#F37021]/15">
                                                        Close to Complete Order
                                                    </button>
                                                @else
                                                    <a href="{{ route('checkout') }}"
                                                        @click.prevent="cartOpen = false; setTimeout(() => window.location.href = '{{ route('checkout') }}', 150)"
                                                        class="w-full btn-orange flex items-center justify-center py-3 rounded-full uppercase tracking-wider text-sm shadow-md shadow-[#F37021]/15">
                                                        Proceed to Checkout
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Location Modal Drawer -->
    <div x-show="locationModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        x-transition.opacity style="display: none;" @keydown.escape.window="locationModalOpen = false">
        <div class="bg-white border border-stone-200 rounded-3xl max-w-md w-full p-5 sm:p-6 space-y-6 relative max-h-[90vh] overflow-y-auto"
            @click.away="locationModalOpen = false" x-show="locationModalOpen"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            x-data="{ orderType: 'delivery' }">
            <button @click="locationModalOpen = false" class="absolute top-5 right-5 text-stone-400 hover:text-black">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
            <div class="text-center space-y-2">
                <h2 class="text-2xl font-bold text-[#1E1E1E] font-serif">Select Order Type</h2>
                <p class="text-stone-500 text-sm">Choose delivery or carryout to customize the menu.</p>
            </div>
            <!-- Tabs Toggle -->
            <div class="grid grid-cols-2 p-1 bg-[#F3F4F6] rounded-2xl border border-stone-100">
                <button @click="orderType = 'delivery'"
                    :class="orderType === 'delivery' ? 'bg-[#FDB813] text-black' : 'text-stone-500 hover:text-black'"
                    class="py-2.5 rounded-xl text-sm font-bold transition-all uppercase tracking-wider">
                    Delivery
                </button>
                <button @click="orderType = 'pickup'"
                    :class="orderType === 'pickup' ? 'bg-[#FDB813] text-black' : 'text-stone-500 hover:text-black'"
                    class="py-2.5 rounded-xl text-sm font-bold transition-all uppercase tracking-wider">
                    Pickup
                </button>
            </div>
            <!-- Forms -->
            <div class="location-ajax-error p-3 bg-rose-50 border border-rose-100 text-rose-800 text-xs rounded-xl"
                role="alert"></div>
            <form x-show="orderType === 'delivery'" action="{{ route('location.select') }}" method="POST"
                class="space-y-4 js-location-form" data-location-form>
                @csrf
                <input type="hidden" name="order_type" value="delivery">
                <div>
                    <label for="modal_address"
                        class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Street
                        Address</label>
                    <input type="text" name="address" id="modal_address" value="{{ session('order_location.address') }}"
                        required
                        class="js-places-address w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all"
                        placeholder="Type street (e.g. 3201 St Paul St)" autocomplete="off">
                </div>
                <div>
                    <label for="modal_zip_code"
                        class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">ZIP Code</label>
                    <input type="text" name="zip_code" id="modal_zip_code"
                        value="{{ session('order_location.zip_code') }}" required
                        class="js-places-zip w-full bg-white border border-stone-200 rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:border-[#FDB813] text-sm transition-all"
                        placeholder="Auto-fills when you pick a street" autocomplete="postal-code" inputmode="numeric">
                </div>
                <button type="submit"
                    class="w-full btn-yellow py-3.5 rounded-2xl uppercase tracking-wider text-sm js-location-submit">Find
                    Store & Order</button>
            </form>

            <form x-show="orderType === 'pickup'" action="{{ route('location.select') }}" method="POST"
                class="space-y-4 js-location-form" data-location-form style="display: none;">
                @csrf
                <input type="hidden" name="order_type" value="pickup">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Nearest
                        Store</label>
                    <select name="store_id" required
                        class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3.5 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all">
                        @isset($stores)
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" @selected($store->slug === 'dock-pizza-shady-side' || $loop->first)>
                                    {{ $store->name }}
                                </option>
                            @endforeach
                        @endisset
                    </select>
                </div>
                <button type="submit"
                    class="w-full btn-yellow py-3.5 rounded-2xl uppercase tracking-wider text-sm js-location-submit">Select
                    Store &
                    Order</button>
            </form>
        </div>
    </div>

    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <style>
        .dock-address-suggestions {
            display: none;
            position: absolute;
            left: 0;
            right: 0;
            top: calc(100% + 6px);
            z-index: 60;
            margin: 0;
            padding: 6px 0;
            list-style: none;
            background: #fff;
            border: 1px solid #e7e5e4;
            border-radius: 16px;
            box-shadow: 0 16px 36px rgba(27, 58, 92, .14);
            max-height: 260px;
            overflow-y: auto
        }

        .dock-address-suggestions.is-open {
            display: block
        }

        .dock-address-suggestions li {
            padding: 10px 14px;
            cursor: pointer
        }

        .dock-address-suggestions li:hover {
            background: #F5F0E8
        }

        .dock-address-suggestions__street {
            display: block;
            font-size: .875rem;
            font-weight: 700;
            color: #1B3A5C
        }

        .dock-address-suggestions__meta {
            display: block;
            margin-top: 2px;
            font-size: .75rem;
            color: #78716c
        }

        .location-ajax-error {
            display: none
        }

        .location-ajax-error.is-visible {
            display: block
        }
    </style>
    <script>
        window.initDockGooglePlaces = window.initDockGooglePlaces || function () {
            window.__googlePlacesLoaded = true;
            if (typeof window.__bindDockPlaces === 'function') {
                window.__bindDockPlaces();
            }
        };

        document.addEventListener('DOMContentLoaded', function () {
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    once: false,
                    offset: 40,
                    duration: 650,
                    easing: 'ease-out-cubic',
                    mirror: false,
                });
            }

            function bindStreetAutocomplete() {
                var pairs = [
                    { address: '#address', zip: '#zip_code' },
                    { address: '#modal_address', zip: '#modal_zip_code' }
                ];

                pairs.forEach(function (pair) {
                    var addressInput = document.querySelector(pair.address);
                    var zipInput = document.querySelector(pair.zip);
                    if (!addressInput || addressInput.dataset.streetAcBound === '1') return;
                    if (addressInput.dataset.placesBound === '1') return;

                    addressInput.dataset.streetAcBound = '1';
                    var wrap = addressInput.parentElement;
                    if (wrap && getComputedStyle(wrap).position === 'static') {
                        wrap.style.position = 'relative';
                    }

                    var list = document.createElement('ul');
                    list.className = 'dock-address-suggestions';
                    list.setAttribute('role', 'listbox');
                    wrap.appendChild(list);

                    var timer = null;
                    var requestId = 0;

                    function hideList() {
                        list.classList.remove('is-open');
                        list.innerHTML = '';
                    }

                    function selectSuggestion(item) {
                        addressInput.value = item.street;
                        if (zipInput && item.zip) {
                            zipInput.value = item.zip;
                        }
                        hideList();
                    }

                    function renderSuggestions(items) {
                        list.innerHTML = '';
                        if (!items.length) {
                            hideList();
                            return;
                        }
                        items.forEach(function (item) {
                            var li = document.createElement('li');
                            li.setAttribute('role', 'option');
                            li.innerHTML = '<span class="dock-address-suggestions__street"></span><span class="dock-address-suggestions__meta"></span>';
                            li.querySelector('.dock-address-suggestions__street').textContent = item.street;
                            li.querySelector('.dock-address-suggestions__meta').textContent = item.meta;
                            li.addEventListener('mousedown', function (e) {
                                e.preventDefault();
                                selectSuggestion(item);
                            });
                            list.appendChild(li);
                        });
                        list.classList.add('is-open');
                    }

                    function searchAddresses(query) {
                        var current = ++requestId;
                        fetch('https://photon.komoot.io/api/?lang=en&limit=7&q=' + encodeURIComponent(query))
                            .then(function (r) { return r.json(); })
                            .then(function (data) {
                                if (current !== requestId) return;
                                var features = (data && data.features) ? data.features : [];
                                var items = [];
                                var seen = {};

                                features.forEach(function (feature) {
                                    var p = feature.properties || {};
                                    var country = String(p.countrycode || p.country || '').toUpperCase();
                                    if (country && country !== 'US' && country !== 'USA' && country !== 'UNITED STATES') return;

                                    var street = [p.housenumber, p.street || p.name].filter(Boolean).join(' ').trim();
                                    if (!street) return;

                                    var zip = p.postcode ? String(p.postcode).split('-')[0].trim() : '';
                                    var meta = [p.city || p.town || p.village || p.county, p.state, zip].filter(Boolean).join(', ');
                                    var key = (street + '|' + zip).toLowerCase();
                                    if (seen[key]) return;
                                    seen[key] = true;
                                    items.push({ street: street, zip: zip, meta: meta });
                                });

                                renderSuggestions(items.slice(0, 6));
                            })
                            .catch(function () {
                                if (current === requestId) hideList();
                            });
                    }

                    addressInput.addEventListener('input', function () {
                        var q = String(addressInput.value || '').trim();
                        clearTimeout(timer);
                        if (q.length < 3) {
                            hideList();
                            return;
                        }
                        timer = setTimeout(function () { searchAddresses(q); }, 320);
                    });

                    document.addEventListener('click', function (e) {
                        if (!wrap.contains(e.target)) hideList();
                    });
                });
            }

            bindStreetAutocomplete();

            document.querySelectorAll('form[data-location-form], form.js-location-form').forEach(function (form) {
                if (form.dataset.ajaxBound === '1') return;
                form.dataset.ajaxBound = '1';

                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    var btn = form.querySelector('.js-location-submit, button[type="submit"]');
                    var scope = form.closest('[x-data], .space-y-6') || form.parentElement;
                    var errorBox = scope ? scope.querySelector('.location-ajax-error') : null;
                    var original = btn ? btn.innerHTML : '';
                    var csrfMeta = document.querySelector('meta[name="csrf-token"]');

                    if (errorBox) {
                        errorBox.classList.remove('is-visible');
                        errorBox.textContent = '';
                    }
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Please wait...';
                    }

                    var headers = {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    };
                    if (csrfMeta) headers['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content');

                    fetch(form.action, {
                        method: 'POST',
                        headers: headers,
                        body: new FormData(form)
                    })
                        .then(async function (res) {
                            var data = {};
                            try { data = await res.json(); } catch (err) { }
                            if (!res.ok) {
                                var message = data.message || 'Something went wrong. Please try again.';
                                if (data.errors) {
                                    var first = Object.values(data.errors)[0];
                                    message = Array.isArray(first) ? first[0] : first;
                                }
                                throw new Error(message);
                            }
                            return data;
                        })
                        .then(function (data) {
                            if (data.success && data.redirect) {
                                window.location.href = data.redirect;
                                return;
                            }
                            throw new Error(data.message || 'Unable to set location.');
                        })
                        .catch(function (err) {
                            if (errorBox) {
                                errorBox.textContent = err.message || 'Something went wrong.';
                                errorBox.classList.add('is-visible');
                            } else {
                                alert(err.message || 'Something went wrong.');
                            }
                            if (btn) {
                                btn.disabled = false;
                                btn.innerHTML = original;
                            }
                        });
                }, true);
            });
        });
    </script>
    @if(config('services.google.maps_api_key'))
        <script
            src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initDockGooglePlaces"
            async defer></script>
    @endif

    @include('partials.cookie-banner')
</body>

</html>