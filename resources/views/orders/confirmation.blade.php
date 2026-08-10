@extends('layouts.app')

@section('title', 'Order Confirmed — Pizza Viva')

@section('content')
<div class="py-12 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Success Header --}}
    <div class="text-center mb-10">
        <div class="w-20 h-20 bg-emerald-50 border border-emerald-200 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fa-solid fa-circle-check text-4xl"></i>
        </div>
        <h1 class="text-3xl sm:text-5xl font-extrabold text-[#1E1E1E] font-serif tracking-tight">Order Confirmed!</h1>
        <p class="text-stone-500 mt-2 max-w-md mx-auto">Your order has been placed and is being prepared. You'll receive updates at <strong class="text-[#1E1E1E]">{{ $order->customer_email }}</strong>.</p>
    </div>

    {{-- Order Reference Card --}}
    <div class="bg-white border border-stone-200 rounded-3xl p-6 sm:p-8 mb-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-stone-100 pb-6 mb-6">
            <div>
                <span class="block text-xs uppercase tracking-wider text-stone-400 font-bold mb-1">Order Number</span>
                <span class="text-2xl font-black text-[#1E1E1E]">{{ $order->order_number }}</span>
            </div>
            <div class="text-right">
                <span class="block text-xs uppercase tracking-wider text-stone-400 font-bold mb-1">Estimated Time</span>
                <span class="text-lg font-bold text-[#F37021]">
                    {{ $order->estimated_minutes ? $order->estimated_minutes . ' min' : 'TBD' }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-sm">
            <div>
                <span class="block text-xs uppercase tracking-wider text-stone-400 font-bold mb-1">Order Type</span>
                <span class="font-semibold text-[#1E1E1E] capitalize">
                    <i class="fa-solid {{ $order->type->value === 'delivery' ? 'fa-truck' : 'fa-basket-shopping' }} text-[#F37021] mr-1"></i>
                    {{ $order->type->label() }}
                </span>
            </div>
            <div>
                <span class="block text-xs uppercase tracking-wider text-stone-400 font-bold mb-1">Payment</span>
                <span class="font-semibold text-[#1E1E1E] capitalize">
                    {{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Credit/Debit Card' }}
                </span>
            </div>
            <div>
                <span class="block text-xs uppercase tracking-wider text-stone-400 font-bold mb-1">Status</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    <i class="fa-solid fa-clock mr-1.5"></i>
                    {{ $order->status->label() }}
                </span>
            </div>
        </div>

        @if($order->type->value === 'delivery' && $order->delivery_address)
            <div class="mt-6 p-4 bg-stone-50 rounded-2xl border border-stone-100">
                <span class="block text-xs uppercase tracking-wider text-stone-400 font-bold mb-1">Delivery Address</span>
                <span class="text-sm text-[#1E1E1E] font-medium">
                    {{ $order->delivery_address }}{{ $order->delivery_city ? ', ' . $order->delivery_city : '' }}{{ $order->delivery_state ? ', ' . $order->delivery_state : '' }} {{ $order->delivery_zip }}
                </span>
                @if($order->delivery_instructions)
                    <p class="text-xs text-stone-500 mt-1 italic">{{ $order->delivery_instructions }}</p>
                @endif
            </div>
        @endif

        @if($order->store)
            <div class="mt-4 p-4 bg-stone-50 rounded-2xl border border-stone-100">
                <span class="block text-xs uppercase tracking-wider text-stone-400 font-bold mb-1">{{ $order->type->value === 'pickup' ? 'Pickup From' : 'Prepared By' }}</span>
                <span class="text-sm text-[#1E1E1E] font-medium">{{ $order->store->name }}</span>
                <span class="text-xs text-stone-500 block">{{ $order->store->address }}, {{ $order->store->city }}</span>
            </div>
        @endif
    </div>

    {{-- Order Items --}}
    <div class="bg-white border border-stone-200 rounded-3xl p-6 sm:p-8 mb-6 shadow-sm">
        <h2 class="text-xl font-bold text-[#1E1E1E] font-serif border-b border-stone-100 pb-3 mb-4">Order Items</h2>

        <div class="divide-y divide-stone-100">
            @foreach($order->items as $item)
                <div class="py-4 flex justify-between items-start space-x-4">
                    <div class="flex-grow space-y-1">
                        <h4 class="text-sm font-bold text-[#1E1E1E]">{{ $item->name }}</h4>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs bg-stone-100 text-stone-700 py-0.5 px-2 rounded-full font-medium">{{ $item->variation_name }}</span>
                            <span class="text-xs text-stone-500">Qty: {{ $item->quantity }}</span>
                        </div>
                        @if($item->toppings->count() > 0)
                            <p class="text-[10px] text-stone-400">
                                + {{ $item->toppings->pluck('name')->implode(', ') }}
                            </p>
                        @endif
                        @if($item->special_instructions)
                            <p class="text-[10px] text-stone-400 italic">Note: {{ $item->special_instructions }}</p>
                        @endif
                    </div>
                    <span class="text-sm font-extrabold text-[#1E1E1E]">${{ number_format($item->total_price, 2) }}</span>
                </div>
            @endforeach
        </div>

        {{-- Totals --}}
        <div class="border-t border-stone-200 pt-4 mt-4 space-y-2">
            <div class="flex justify-between text-sm text-stone-600">
                <span>Subtotal</span>
                <span class="font-semibold text-[#1E1E1E]">${{ number_format($order->subtotal, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm text-stone-600">
                <span>Tax</span>
                <span class="font-semibold text-[#1E1E1E]">${{ number_format($order->tax_amount, 2) }}</span>
            </div>
            @if($order->type->value === 'delivery')
                <div class="flex justify-between text-sm text-stone-600">
                    <span>Delivery Fee</span>
                    <span class="font-semibold text-[#1E1E1E]">${{ number_format($order->delivery_fee, 2) }}</span>
                </div>
            @endif
            @if($order->discount_amount > 0)
                <div class="flex justify-between text-sm text-emerald-600">
                    <span>Discount</span>
                    <span class="font-semibold">-${{ number_format($order->discount_amount, 2) }}</span>
                </div>
            @endif
            <div class="flex justify-between text-lg font-bold border-t border-stone-100 pt-3 text-[#1E1E1E]">
                <span>Total</span>
                <span class="text-xl font-extrabold text-[#F37021]">${{ number_format($order->total, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-wrap gap-4 justify-center pt-4">
        <a href="{{ route('order.track', $order->order_number) }}" class="btn-orange px-8 py-3.5 rounded-full text-sm font-bold uppercase tracking-wider shadow-md shadow-[#F37021]/15 flex items-center space-x-2">
            <i class="fa-solid fa-location-arrow"></i>
            <span>Track Order</span>
        </a>
        <a href="{{ route('menu.index') }}" class="border border-[#FDB813] text-[#1E1E1E] hover:bg-[#FDB813]/10 px-8 py-3.5 rounded-full text-sm font-bold uppercase tracking-wider transition-all">
            Order More
        </a>
        <a href="{{ route('home') }}" class="border border-stone-200 text-stone-600 hover:bg-stone-50 px-8 py-3.5 rounded-full text-sm font-bold uppercase tracking-wider transition-all">
            Back to Home
        </a>
    </div>

</div>
@endsection
