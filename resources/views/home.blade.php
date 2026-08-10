@extends('layouts.app')

@section('title', 'Dock Pizza — Fresh off the dock')

@section('content')
    <!-- Hero Slider with Location Selector -->
    <div class="relative min-h-[85vh] w-full overflow-hidden bg-black flex items-center pt-8">
        @if($heroBanners->count() > 0)
            @foreach($heroBanners as $index => $banner)
                <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out hero-slide {{ $index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' }}" data-index="{{ $index }}">
                    <div class="absolute inset-0 bg-gradient-to-r from-black via-black/80 to-transparent z-10"></div>
                    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('assets/images/menu/dock-pizza.png') }}');"></div>
                </div>
            @endforeach
        @else
            <div class="absolute inset-0 bg-gradient-to-r from-black via-black/80 to-transparent z-10"></div>
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('assets/images/menu/dock-pizza.png') }}');"></div>
        @endif

        <div class="relative z-20 mx-auto max-w-[1600px] w-full px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            <!-- Left Side: Title -->
            <div class="lg:col-span-7 space-y-6 page-enter" data-aos="fade-right" data-aos-duration="800">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-[#E07B2D] text-white" data-aos="zoom-in" data-aos-delay="100">
                    Fresh off the dock
                </span>
                <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-white font-serif leading-tight" data-aos="fade-up" data-aos-delay="150">
                    Premium Specialty Pizzas
                </h1>
                <p class="text-stone-300 text-lg sm:text-xl leading-relaxed max-w-xl" data-aos="fade-up" data-aos-delay="250">
                    Handcrafted pizzas with dock seasoning, fresh salads, and crispy sides — delivered hot to your door.
                </p>
            </div>

            <!-- Right Side: Location Search Card -->
            <div class="lg:col-span-5 w-full page-enter-delay" x-data="{ orderType: 'delivery' }" data-aos="fade-left" data-aos-duration="800" data-aos-delay="100">
                <div class="bg-white border border-stone-200 rounded-3xl p-6 sm:p-8 space-y-6 shadow-2xl">
                    <div class="text-center space-y-2">
                        <h2 class="text-2xl font-bold text-[#1E1E1E] font-serif">Start Your Order</h2>
                        <p class="text-stone-500 text-sm">Select order type to see menu and store availability.</p>
                    </div>

                    <!-- Tabs Toggle -->
                    <div class="grid grid-cols-2 p-1 bg-[#F3F4F6] rounded-2xl border border-stone-100">
                        <button type="button" @click="orderType = 'delivery'" :class="orderType === 'delivery' ? 'bg-[#E07B2D] text-white font-bold' : 'text-stone-500 hover:text-black font-semibold'" class="py-2.5 rounded-xl text-sm transition-all uppercase tracking-wider">
                            <i class="fa-solid fa-truck mr-2"></i> Delivery
                        </button>
                        <button type="button" @click="orderType = 'pickup'" :class="orderType === 'pickup' ? 'bg-[#E07B2D] text-white font-bold' : 'text-stone-500 hover:text-black font-semibold'" class="py-2.5 rounded-xl text-sm transition-all uppercase tracking-wider">
                            <i class="fa-solid fa-basket-shopping mr-2"></i> Pickup
                        </button>
                    </div>

                    <div class="location-ajax-error p-3 bg-rose-50 border border-rose-100 text-rose-800 text-xs rounded-xl" role="alert"></div>

                    <!-- Delivery Form -->
                    <form x-show="orderType === 'delivery'" action="{{ route('location.select') }}" method="POST" class="space-y-4 js-location-form" data-location-form>
                        @csrf
                        <input type="hidden" name="order_type" value="delivery">
                        <div>
                            <label for="address" class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Street Address</label>
                            <input type="text" name="address" id="address" class="js-places-address w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all" value="{{ session('order_location.address') }}" placeholder="Type street (e.g. 3201 St Paul St)" required autocomplete="off">
                        </div>
                        <div>
                            <label for="zip_code" class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">ZIP Code</label>
                            <input type="text" name="zip_code" id="zip_code" class="js-places-zip w-full bg-white border border-stone-200 rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:border-[#FDB813] text-sm transition-all" value="{{ session('order_location.zip_code') }}" placeholder="Auto-fills when you pick a street" required autocomplete="postal-code" inputmode="numeric">
                        </div>
                        <button type="submit" class="w-full btn-yellow py-3.5 rounded-2xl uppercase tracking-wider text-sm shadow-sm shadow-[#FDB813]/20 js-location-submit">
                            Find Store & Order
                        </button>
                    </form>

                    <!-- Pickup Form -->
                    <form x-show="orderType === 'pickup'" action="{{ route('location.select') }}" method="POST" class="space-y-4 js-location-form" data-location-form style="display: none;">
                        @csrf
                        <input type="hidden" name="order_type" value="pickup">
                        <div>
                            <label for="store_id" class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Select Nearest Store</label>
                            <select name="store_id" id="store_id" required class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3.5 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all">
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" @selected($store->slug === 'dock-pizza-shady-side' || $loop->first)>
                                        {{ $store->name }} ({{ $store->address }}, {{ $store->city }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full btn-yellow py-3.5 rounded-2xl uppercase tracking-wider text-sm shadow-sm shadow-[#FDB813]/20 js-location-submit">
                            Select Store & Order
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- Explore Menu Section -->
    <section class="py-24 max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12" data-aos="fade-up">
            <h2 class="text-3xl font-extrabold text-[#1B3A5C] font-serif tracking-tight">Explore Menu</h2>
            <a href="{{ route('menu.index') }}" class="text-[#E07B2D] hover:text-[#C96A22] font-bold text-sm uppercase tracking-wider transition-colors js-soft-nav">
                View All
            </a>
        </div>

        <div class="relative" x-data="{ scrollAmt: 0 }">
            <!-- Left Arrow -->
            <button type="button" @click="$refs.slider.scrollBy({ left: -300, behavior: 'smooth' })" class="absolute left-[-20px] top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white border border-stone-200 shadow-lg flex items-center justify-center text-stone-700 hover:text-black transition-all hover:scale-110">
                <i class="fa-solid fa-chevron-left"></i>
            </button>

            <!-- Slider Wrapper -->
            <div x-ref="slider" class="flex justify-center overflow-x-auto gap-6 scrollbar-hide py-4 px-2" style="-ms-overflow-style: none; scrollbar-width: none;">
                @foreach($categories as $index => $category)
                    <a href="{{ route('menu.index') }}#{{ $category->slug }}"
                        class="menu-cat-card flex-shrink-0 w-64 bg-white border border-stone-200 rounded-3xl p-6 text-center flex flex-col items-center justify-between space-y-4 group js-soft-nav"
                        data-aos="zoom-in"
                        data-aos-delay="{{ 100 + ($index * 80) }}"
                        data-aos-duration="650">
                        <div class="w-36 h-36 rounded-full overflow-hidden bg-[#F5F0E8] border-2 border-[#1B3A5C]/10 flex items-center justify-center relative group-hover:border-[#E07B2D]/40 transition-colors">
                            @if($category->image && file_exists(public_path($category->image)))
                                <img src="{{ asset($category->image) }}" alt="{{ $category->name }}" class="w-full h-full object-cover group-hover:scale-110" loading="lazy">
                            @else
                                <i class="fa-solid fa-{{ $category->icon ?? 'pizza-slice' }} text-5xl text-[#1B3A5C]/40 group-hover:text-[#E07B2D] transition-colors"></i>
                            @endif
                        </div>
                        <span class="text-sm font-extrabold text-[#1B3A5C] uppercase tracking-wider">{{ $category->name }}</span>
                    </a>
                @endforeach
            </div>

            <!-- Right Arrow -->
            <button type="button" @click="$refs.slider.scrollBy({ left: 300, behavior: 'smooth' })" class="absolute right-[-20px] top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white border border-stone-200 shadow-lg flex items-center justify-center text-stone-700 hover:text-black transition-all hover:scale-110">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </section>
@endsection
