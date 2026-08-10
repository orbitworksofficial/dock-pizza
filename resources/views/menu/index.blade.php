@extends('layouts.app')

@section('title', 'Our Menu — Dock Pizza')

@section('content')
<div class="py-12 max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8" x-data="menuPage()" x-init="$store.search.query = @js(request('search', ''))">

    @if(empty($hasLocation))
        <div class="mb-8 p-4 bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4" data-aos="fade-down">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-location-dot text-[#E07B2D] text-lg"></i>
                <span class="text-sm font-semibold">Enter your delivery address or select a store to start ordering.</span>
            </div>
            <a href="{{ route('home') }}" class="bg-[#E07B2D] hover:bg-[#C96A22] text-white text-xs font-bold py-2.5 px-5 rounded-full uppercase tracking-wider transition-all whitespace-nowrap">
                Set Location
            </a>
        </div>
    @endif

    <!-- Printed Menu Preview -->
    <section class="mb-10" data-aos="fade-down">
        <div class="text-center space-y-2 mb-5">
            <span class="inline-flex items-center gap-2 text-[#E07B2D] text-xs font-bold uppercase tracking-widest">
                <i class="fa-solid fa-book-open"></i> Menu Preview
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1B3A5C] font-serif">Fresh off the dock</h1>
            <p class="text-stone-500 text-sm">Tap a menu page to view full size</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-3xl mx-auto">
            <button type="button" @click="openPreviewLightbox('premium')" class="dock-menu-card rounded-2xl p-3 text-left hover:shadow-lg transition-all border border-[#1B3A5C]/10 group">
                <img src="{{ asset('assets/images/menu/dock-pizza-menu-premium.png') }}" alt="Premium Pizzas Menu" class="w-full h-44 sm:h-52 max-h-52 object-cover object-top rounded-xl border border-[#1B3A5C]/10" style="max-height: 13rem;">
                <p class="mt-3 text-sm font-bold text-[#1B3A5C] uppercase tracking-wider">Premium Pizzas</p>
                <p class="text-xs text-[#E07B2D] font-semibold mt-1 group-hover:underline">View full menu <i class="fa-solid fa-expand ml-1"></i></p>
            </button>
            <button type="button" @click="openPreviewLightbox('salads')" class="dock-menu-card rounded-2xl p-3 text-left hover:shadow-lg transition-all border border-[#1B3A5C]/10 group">
                <img src="{{ asset('assets/images/menu/dock-pizza-menu-salads-sides.png') }}" alt="Salads and Sides Menu" class="w-full h-44 sm:h-52 max-h-52 object-cover object-top rounded-xl border border-[#1B3A5C]/10" style="max-height: 13rem;">
                <p class="mt-3 text-sm font-bold text-[#1B3A5C] uppercase tracking-wider">Salads & Sides</p>
                <p class="text-xs text-[#E07B2D] font-semibold mt-1 group-hover:underline">View full menu <i class="fa-solid fa-expand ml-1"></i></p>
            </button>
        </div>

        <div class="text-center mt-6">
            <a href="#order-online" class="inline-flex items-center gap-2 bg-[#E07B2D] hover:bg-[#C96A22] text-white text-sm font-bold py-3 px-8 rounded-full uppercase tracking-wider transition-all shadow-sm">
                Order Online <i class="fa-solid fa-arrow-down"></i>
            </a>
        </div>
    </section>

    <!-- Order Online Section -->
    <div id="order-online" class="scroll-mt-28">
    <div class="text-center space-y-3 mb-10" data-aos="fade-up">
        <h2 class="text-2xl sm:text-4xl font-extrabold text-[#1B3A5C] font-serif tracking-tight">Order Online</h2>
        <p class="text-stone-600 max-w-lg mx-auto">Select items and add them to your bag.</p>
    </div>

    <!-- Layout Grid: Side Category Bar & Menu Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        <!-- Category Sidebar (Sticky) -->
        <div class="lg:col-span-1" data-aos="fade-up">
            <div class="sticky top-28 dock-menu-card rounded-3xl p-6 space-y-2 shadow-sm">
                <h3 class="text-lg font-bold text-[#1B3A5C] font-serif mb-4 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-compass text-[#E07B2D]"></i> Categories
                </h3>
                @foreach($categories as $category)
                    <a href="#{{ $category->slug }}" class="flex items-center space-x-3 w-full p-3 rounded-xl transition-all text-stone-600 hover:text-[#1B3A5C] hover:bg-white/60">
                        <i class="fa-solid fa-{{ $category->icon ?? 'pizza-slice' }} text-[#E07B2D]"></i>
                        <span class="text-sm font-semibold">{{ $category->name }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Products List -->
        <div class="lg:col-span-3 space-y-16">
            @foreach($categories as $category)
                <div id="{{ $category->slug }}" class="scroll-mt-32 space-y-6">
                    <div class="flex items-center space-x-3 border-b-2 border-[#1B3A5C]/10 pb-4">
                        <div class="w-12 h-12 rounded-2xl bg-[#1B3A5C] flex items-center justify-center">
                            <i class="fa-solid fa-{{ $category->icon ?? 'pizza-slice' }} text-[#E07B2D] text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl sm:text-4xl font-extrabold text-[#1B3A5C] font-serif">{{ $category->name }}</h2>
                            <p class="text-stone-500 text-sm">{{ $category->description }}</p>
                        </div>
                    </div>

                    @if($category->products->isEmpty())
                        <p class="text-stone-500 text-sm italic py-4">No items available in this category right now.</p>
                    @elseif($category->slug === 'premium-pizzas')
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($category->products as $product)
                                @php
                                    $icons = ['supreme-pizza' => 'star', 'dock-pizza' => 'anchor', 'polo-ranch-pizza' => 'compass', 'mega-meat' => 'fish', 'feta-feast' => 'ship'];
                                    $icon = $icons[$product->slug] ?? 'pizza-slice';
                                @endphp
                                <div x-show="matchesSearch(@js($product->name), @js($product->description ?? ''))" class="dock-menu-card rounded-2xl p-6 hover:shadow-lg transition-all group flex flex-col sm:flex-row sm:items-center justify-between gap-4" data-aos="fade-up">
                                    <div class="flex items-start space-x-4 flex-grow">
                                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden bg-[#E07B2D]/10 border border-[#1B3A5C]/10 flex items-center justify-center flex-shrink-0">
                                            @include('menu.partials.product-image', [
                                                'product' => $product,
                                                'fallbackIcon' => $icon,
                                                'fallbackIconClass' => 'text-[#E07B2D] text-lg',
                                            ])
                                        </div>
                                        <div class="space-y-1 flex-grow">
                                            <h3 class="text-lg font-bold text-[#1B3A5C] group-hover:text-[#E07B2D] transition-colors">{{ $product->name }}</h3>
                                            <p class="text-stone-500 text-sm leading-relaxed">{{ $product->description }}</p>
                                            <div class="flex flex-wrap gap-2 pt-1">
                                                @foreach($product->variations as $var)
                                                    <span class="text-xs bg-white/80 border border-[#1B3A5C]/10 rounded-full px-3 py-1 text-[#1B3A5C] font-medium">{{ $var->name }} — ${{ number_format($var->price, 2) }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4 sm:flex-shrink-0">
                                        <div class="text-right">
                                            <span class="text-stone-400 text-[10px] uppercase tracking-wider block">From</span>
                                            <span class="text-xl font-extrabold text-[#E07B2D]">${{ number_format($product->base_price, 2) }}</span>
                                        </div>
                                        <button type="button" @click="openCustomizer('{{ $product->slug }}')" class="bg-[#1B3A5C] hover:bg-[#142D47] text-white text-xs font-bold py-2.5 px-5 rounded-full uppercase tracking-wider transition-all border-0 cursor-pointer">
                                            Customize
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($category->products as $product)
                                <div x-show="matchesSearch(@js($product->name), @js($product->description ?? ''))" class="dock-menu-card rounded-3xl overflow-hidden hover:shadow-lg transition-all flex flex-col justify-between p-6 space-y-6 group" data-aos="fade-up">
                                    <div class="flex space-x-4">
                                        <div class="w-24 h-24 rounded-2xl overflow-hidden flex-shrink-0 bg-[#1B3A5C]/5 border border-[#1B3A5C]/10 flex items-center justify-center">
                                            @include('menu.partials.product-image', [
                                                'product' => $product,
                                                'fallbackIcon' => $category->icon ?? 'utensils',
                                                'fallbackIconClass' => 'text-3xl text-[#1B3A5C]/20',
                                            ])
                                        </div>
                                        <div class="space-y-1">
                                            <h3 class="text-lg font-bold text-[#1B3A5C] group-hover:text-[#E07B2D] transition-colors">{{ $product->name }}</h3>
                                            <p class="text-stone-500 text-xs line-clamp-2 leading-relaxed">{{ $product->description }}</p>
                                            @if($product->variations->count() > 1)
                                                <div class="flex flex-wrap gap-1.5 pt-1">
                                                    @foreach($product->variations as $var)
                                                        <span class="text-[10px] bg-white/80 border border-[#1B3A5C]/10 rounded-full px-2 py-0.5 text-[#1B3A5C] font-medium">{{ $var->name }} — ${{ number_format($var->price, 2) }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between pt-4 border-t border-[#1B3A5C]/10">
                                        <div class="flex flex-col">
                                            <span class="text-stone-400 text-[10px] uppercase tracking-wider">{{ $product->variations->count() > 1 ? 'From' : 'Price' }}</span>
                                            <span class="text-lg font-extrabold text-[#1B3A5C]">${{ number_format($product->base_price, 2) }}</span>
                                        </div>
                                        @if($product->is_customizable || $product->variations->count() > 1)
                                            <button type="button" @click="openCustomizer('{{ $product->slug }}')" class="border-2 border-[#E07B2D] hover:bg-[#E07B2D] hover:text-white text-[#E07B2D] text-xs font-bold py-2.5 px-5 rounded-full uppercase tracking-wider transition-all cursor-pointer bg-white">
                                                {{ $product->is_customizable ? 'Customize & Add' : 'Choose Size' }}
                                            </button>
                                        @else
                                            <button type="button" @click="addToCart('{{ $product->slug }}')" class="bg-[#E07B2D] hover:bg-[#C96A22] text-white text-xs font-bold py-2.5 px-5 rounded-full uppercase tracking-wider transition-all shadow-sm border-0 cursor-pointer">
                                                Add to Bag
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    </div>

    <!-- Full-Size Menu Lightbox -->
    <div x-show="previewLightbox" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/85 backdrop-blur-sm" x-transition.opacity style="display: none;" @keydown.escape.window="previewLightbox = null" @click.self="previewLightbox = null">
        <div class="relative max-w-5xl w-full max-h-[90vh] flex flex-col" @click.stop>
            <div class="flex items-center justify-between mb-3">
                <div class="flex gap-2">
                    <button type="button" @click="previewLightbox = 'premium'" :class="previewLightbox === 'premium' ? 'bg-[#E07B2D] text-white' : 'bg-white/20 text-white'" class="px-4 py-1.5 rounded-full text-xs font-bold uppercase">Premium Pizzas</button>
                    <button type="button" @click="previewLightbox = 'salads'" :class="previewLightbox === 'salads' ? 'bg-[#E07B2D] text-white' : 'bg-white/20 text-white'" class="px-4 py-1.5 rounded-full text-xs font-bold uppercase">Salads & Sides</button>
                </div>
                <button type="button" @click="previewLightbox = null" class="w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="overflow-y-auto rounded-2xl bg-white p-2 shadow-2xl">
                <img x-show="previewLightbox === 'premium'" src="{{ asset('assets/images/menu/dock-pizza-menu-premium.png') }}" alt="Premium Pizzas Menu full size" class="w-full h-auto" style="display: none;">
                <img x-show="previewLightbox === 'salads'" src="{{ asset('assets/images/menu/dock-pizza-menu-salads-sides.png') }}" alt="Salads and Sides Menu full size" class="w-full h-auto" style="display: none;">
            </div>
        </div>
    </div>

    <!-- Product Image Lightbox -->
    <div
        x-show="productImageLightbox"
        class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/90 backdrop-blur-sm"
        x-transition.opacity
        style="display: none;"
        @keydown.escape.window="closeProductImageLightbox()"
        @keydown.arrow-left.window="prevProductImage()"
        @keydown.arrow-right.window="nextProductImage()"
        @click.self="closeProductImageLightbox()"
    >
        <div class="relative max-w-2xl sm:max-w-3xl w-full max-h-[85vh] flex flex-col" @click.stop>
            <div class="flex items-center justify-between gap-4 mb-3">
                <div class="min-w-0">
                    <p class="text-white font-bold text-sm sm:text-base truncate" x-text="productImageLightbox?.name"></p>
                    <p class="text-white/60 text-xs" x-show="productImageLightbox && productImageLightbox.images.length > 1">
                        <span x-text="(productImageLightbox?.index ?? 0) + 1"></span>
                        /
                        <span x-text="productImageLightbox?.images.length"></span>
                    </p>
                </div>
                <button type="button" @click="closeProductImageLightbox()" class="w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-all flex-shrink-0">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="relative flex items-center justify-center rounded-2xl bg-white p-2 sm:p-3 shadow-2xl overflow-hidden max-h-[58vh]">
                <button
                    type="button"
                    x-show="productImageLightbox && productImageLightbox.images.length > 1"
                    @click="prevProductImage()"
                    class="absolute left-2 sm:left-4 z-10 w-10 h-10 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center transition-all"
                    aria-label="Previous photo"
                >
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                <img
                    x-show="productImageLightbox"
                    :src="productImageLightbox?.images[productImageLightbox.index]?.url"
                    :alt="productImageLightbox?.images[productImageLightbox.index]?.alt"
                    class="w-full h-auto max-h-[52vh] max-w-full object-contain"
                >

                <button
                    type="button"
                    x-show="productImageLightbox && productImageLightbox.images.length > 1"
                    @click="nextProductImage()"
                    class="absolute right-2 sm:right-4 z-10 w-10 h-10 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center transition-all"
                    aria-label="Next photo"
                >
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

            <div class="flex justify-center gap-2 mt-4 flex-wrap" x-show="productImageLightbox && productImageLightbox.images.length > 1">
                <template x-for="(image, index) in productImageLightbox?.images ?? []" :key="index">
                    <button
                        type="button"
                        @click="productImageLightbox.index = index"
                        :class="productImageLightbox.index === index ? 'ring-2 ring-[#E07B2D] opacity-100' : 'opacity-60 hover:opacity-100'"
                        class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl overflow-hidden transition-all"
                    >
                        <img :src="image.url" :alt="image.alt" class="w-full h-full object-cover">
                    </button>
                </template>
            </div>
        </div>
    </div>

    <!-- Dynamic Pizza / Product Customizer Modal -->
    <div x-show="customizerOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-transition.opacity style="display: none;" @keydown.escape.window="customizerOpen = false">
        <div class="bg-[#F5F0E8] border border-[#1B3A5C]/10 rounded-3xl max-w-2xl w-full max-h-[85vh] overflow-y-auto p-4 sm:p-6 space-y-6 relative flex flex-col justify-between" @click.away="customizerOpen = false" x-show="customizerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4">

            <button @click="customizerOpen = false" class="absolute top-4 right-4 sm:top-6 sm:right-6 text-stone-500 hover:text-[#1B3A5C] transition-colors z-10">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>

            <template x-if="loading">
                <div class="text-center py-12">
                    <i class="fa-solid fa-circle-notch fa-spin text-3xl text-[#E07B2D] mb-4"></i>
                    <p class="text-stone-500 text-sm">Loading options...</p>
                </div>
            </template>

            <template x-if="!loading && activeProduct">
                <div class="space-y-6 flex-grow">
                    <div class="flex items-start space-x-4 pr-6 sm:pr-0">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl overflow-hidden bg-[#1B3A5C]/10 border border-[#1B3A5C]/10 flex-shrink-0 flex items-center justify-center">
                            <template x-if="mainImageUrl()">
                                <button
                                    type="button"
                                    @click.stop="openProductImageLightbox(activeProduct.name, galleryImages(), Math.max(0, galleryImages().findIndex(img => img.url === mainImageUrl())))"
                                    class="w-full h-full cursor-zoom-in group/image relative"
                                    aria-label="View full size photos"
                                >
                                    <img :src="mainImageUrl()" :alt="activeProduct.name" class="w-full h-full object-cover">
                                    <span class="absolute inset-0 bg-black/0 group-hover/image:bg-black/20 transition-colors flex items-center justify-center">
                                        <i class="fa-solid fa-expand text-white opacity-0 group-hover/image:opacity-100 transition-opacity text-xs drop-shadow"></i>
                                    </span>
                                </button>
                            </template>
                            <template x-if="!mainImageUrl()">
                                <i class="fa-solid fa-pizza-slice text-2xl text-[#E07B2D]"></i>
                            </template>
                        </div>
                        <div class="space-y-1">
                            <h2 class="text-xl sm:text-2xl font-bold text-[#1B3A5C] font-serif" x-text="activeProduct.name"></h2>
                            <p class="text-stone-500 text-xs sm:text-sm" x-text="activeProduct.description"></p>
                        </div>
                    </div>

                    <div class="space-y-3" x-show="activeProduct.variations && activeProduct.variations.length > 0">
                        <h3 class="text-[#1B3A5C] font-bold text-sm uppercase tracking-wider border-b border-[#1B3A5C]/10 pb-2" x-text="activeProduct.variations.length > 1 ? 'Choose Size / Option' : 'Selected Option'"></h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                            <template x-for="variation in activeProduct.variations" :key="variation.id">
                                <button type="button" @click="selectedVariation = variation" :class="selectedVariation && selectedVariation.id === variation.id ? 'border-[#E07B2D] bg-[#E07B2D]/10 text-[#1B3A5C]' : 'border-stone-200 bg-white text-stone-700 hover:text-[#1B3A5C]'" class="border-2 rounded-2xl p-4 text-center transition-all text-sm font-semibold flex flex-col items-center justify-center">
                                    <template x-if="variationImage(variation)">
                                        <img :src="variationImage(variation)" :alt="variation.name" class="w-12 h-12 object-contain mb-2">
                                    </template>
                                    <span x-text="variation.name"></span>
                                    <span class="text-xs text-stone-500 mt-1" x-text="'$' + parseFloat(variation.price).toFixed(2)"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="space-y-6" x-show="Object.keys(toppingsByCategory).length > 0">
                        <template x-for="(toppings, categoryName) in toppingsByCategory" :key="categoryName">
                            <div class="space-y-3">
                                <h3 class="text-[#1B3A5C] font-bold text-sm uppercase tracking-wider border-b border-[#1B3A5C]/10 pb-2" x-text="categoryName"></h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <template x-for="topping in toppings" :key="topping.id">
                                        <label class="flex items-center justify-between p-3 rounded-2xl border border-stone-200 bg-white cursor-pointer hover:bg-[#E07B2D]/5 transition-all">
                                            <div class="flex items-center space-x-3">
                                                <input type="checkbox" :value="topping.id" x-model="selectedToppings" class="rounded border-stone-300 text-[#E07B2D] focus:ring-[#E07B2D]">
                                                <span class="text-sm font-medium text-[#1B3A5C]" x-text="topping.name"></span>
                                            </div>
                                            <span class="text-xs font-semibold text-stone-500" x-text="'+$' + parseFloat(topping.price).toFixed(2)"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="pt-6 border-t border-[#1B3A5C]/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex flex-col">
                            <span class="text-stone-400 text-xs uppercase tracking-wider">Total Price</span>
                            <span class="text-2xl font-extrabold text-[#1B3A5C]" x-text="'$' + calculateTotal().toFixed(2)"></span>
                        </div>
                        <button @click="confirmAdd()" class="w-full sm:w-auto bg-[#E07B2D] hover:bg-[#C96A22] text-white text-sm font-bold py-3 px-8 rounded-full uppercase tracking-wider transition-all shadow-sm">
                            Add to Bag
                        </button>
                    </div>
                </div>
            </template>

        </div>
    </div>
</div>

<script>
    function menuPage() {
        return {
            previewLightbox: null,
            productImageLightbox: null,
            customizerOpen: false,
            loading: false,
            activeProduct: null,
            toppingsByCategory: {},
            selectedVariation: null,
            selectedToppings: [],

            // Static fallback images for cold drink sizes (Can / 20oz).
            // These are used whenever a variation doesn't already have its
            // own `image_path` coming from the backend, so drinks show a
            // picture immediately without needing per-product uploads.
            drinkSizeImages: {
                can: '{{ asset("assets/images/menu/drink-can.png") }}',
                '20oz': '{{ asset("assets/images/menu/drink-20oz.png") }}',
            },

            matchesSearch(name, description) {
                const query = (Alpine.store('search')?.query ?? '').toString().toLowerCase().trim();
                if (!query) return true;
                return (name + ' ' + description).toLowerCase().includes(query);
            },

            openPreviewLightbox(which) {
                this.previewLightbox = which;
            },

            // Resolves the image to use for a given variation (e.g. "Can", "20oz").
            // Priority: an explicit image_path from the backend, then the
            // built-in can/20oz fallback matched by name, else null.
            variationImage(variation) {
                if (!variation) return null;

                if (variation.image_path) {
                    return '/' + variation.image_path;
                }

                const name = (variation.name || '').toString().toLowerCase();

                if (name.includes('can')) {
                    return this.drinkSizeImages.can;
                }

                if (name.includes('20oz') || name.includes('20 oz') || (name.includes('20') && name.includes('oz'))) {
                    return this.drinkSizeImages['20oz'];
                }

                return null;
            },

            // The image shown in the customizer header: the selected
            // variation's image if there is one, otherwise the product's
            // own photo(s).
            mainImageUrl() {
                const variationUrl = this.variationImage(this.selectedVariation);
                if (variationUrl) return variationUrl;

                if (this.activeProduct?.images?.length) {
                    return '/' + this.activeProduct.images[0].path;
                }

                if (this.activeProduct?.primary_image?.path) {
                    return '/' + this.activeProduct.primary_image.path;
                }

                return null;
            },

            // Full set of images for the lightbox gallery: product photos
            // plus one entry per distinct variation image (Can, 20oz, ...).
            galleryImages() {
                const images = [];

                if (this.activeProduct?.images?.length) {
                    this.activeProduct.images.forEach(image => {
                        images.push({ url: '/' + image.path, alt: image.alt_text || this.activeProduct.name });
                    });
                } else if (this.activeProduct?.primary_image?.path) {
                    images.push({ url: '/' + this.activeProduct.primary_image.path, alt: this.activeProduct.name });
                }

                (this.activeProduct?.variations || []).forEach(variation => {
                    const url = this.variationImage(variation);
                    if (url && !images.some(image => image.url === url)) {
                        images.push({ url, alt: variation.name });
                    }
                });

                return images;
            },

            openProductImageLightbox(name, images, index = 0) {
                if (!images || !images.length) {
                    return;
                }

                this.productImageLightbox = {
                    name,
                    images,
                    index: Math.max(0, Math.min(index, images.length - 1)),
                };
            },

            closeProductImageLightbox() {
                this.productImageLightbox = null;
            },

            prevProductImage() {
                if (!this.productImageLightbox || this.productImageLightbox.images.length <= 1) {
                    return;
                }

                const total = this.productImageLightbox.images.length;
                this.productImageLightbox.index = (this.productImageLightbox.index - 1 + total) % total;
            },

            nextProductImage() {
                if (!this.productImageLightbox || this.productImageLightbox.images.length <= 1) {
                    return;
                }

                const total = this.productImageLightbox.images.length;
                this.productImageLightbox.index = (this.productImageLightbox.index + 1) % total;
            },

            async openCustomizer(slug) {
                this.customizerOpen = true;
                this.loading = true;
                try {
                    const response = await fetch(@json(url('/menu/product')) + '/' + slug);
                    const data = await response.json();
                    this.activeProduct = data.product;
                    this.toppingsByCategory = data.toppingsByCategory;
                    this.selectedVariation = data.product.variations.find(v => v.is_default) || data.product.variations[0];
                    this.selectedToppings = [];
                } catch (e) {
                    console.error("Error loading product", e);
                } finally {
                    this.loading = false;
                }
            },

            calculateTotal() {
                if (!this.selectedVariation) return 0;
                let total = parseFloat(this.selectedVariation.price);

                this.selectedToppings.forEach(toppingId => {
                    Object.values(this.toppingsByCategory).forEach(toppings => {
                        const top = toppings.find(t => t.id == toppingId);
                        if (top) total += parseFloat(top.price);
                    });
                });

                return total;
            },

            confirmAdd() {
                const cartItem = {
                    product: this.activeProduct,
                    variation: this.selectedVariation,
                    toppings: this.selectedToppings,
                    price: this.calculateTotal(),
                    quantity: 1
                };

                Alpine.store('cart').add(cartItem);
                window.dispatchEvent(new CustomEvent('add-to-cart', { detail: cartItem }));
                this.customizerOpen = false;
            },

            addToCart(slug) {
                const url = @json(url('/menu/product')) + '/' + slug;

                fetch(url)
                    .then(response => {
                        if (!response.ok) throw new Error('Could not load product');
                        return response.json();
                    })
                    .then(data => {
                        const variations = data.product?.variations || [];
                        const defaultVar = variations.find(v => v.is_default) || variations[0];

                        if (!defaultVar) {
                            alert('This item is not available right now. Please try again.');
                            return;
                        }

                        const cartItem = {
                            product: data.product,
                            variation: defaultVar,
                            toppings: [],
                            price: parseFloat(defaultVar.price),
                            quantity: 1
                        };

                        Alpine.store('cart').add(cartItem);
                        window.dispatchEvent(new CustomEvent('add-to-cart', { detail: cartItem }));
                    })
                    .catch(error => {
                        console.error('Add to cart failed:', error);
                        alert('Could not add item to bag. Please refresh and try again.');
                    });
            }
        };
    }
</script>
@endsection