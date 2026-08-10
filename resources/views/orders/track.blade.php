@extends('layouts.app')

@section('title', 'Track Order ' . $order->order_number . ' — Pizza Viva')

@section('content')
<div class="py-12 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="mb-10 text-center">
        <h1 class="text-3xl sm:text-4xl font-extrabold text-[#1E1E1E] font-serif tracking-tight">Track Your Order</h1>
        <p class="text-stone-500 mt-2">Order <strong class="text-[#1E1E1E]">{{ $order->order_number }}</strong> placed on {{ $order->created_at->format('M j, g:i A') }}</p>
    </div>

    {{-- Main Tracking Card --}}
    <div class="bg-white border border-stone-200 rounded-3xl p-6 sm:p-10 mb-8 shadow-xl relative overflow-hidden">
        
        {{-- Background blur accent based on status --}}
        @php
            $accentColor = match($order->status->value) {
                'pending', 'confirmed' => 'bg-amber-100',
                'preparing' => 'bg-[#F37021]/10',
                'ready', 'out_for_delivery' => 'bg-[#FDB813]/20',
                'delivered', 'picked_up' => 'bg-emerald-100',
                'cancelled', 'refunded' => 'bg-rose-100',
                default => 'bg-stone-100'
            };
        @endphp
        <div class="absolute top-0 right-0 w-64 h-64 {{ $accentColor }} rounded-full blur-3xl opacity-50 -translate-y-1/2 translate-x-1/4 pointer-events-none"></div>

        <div class="relative z-10 text-center mb-12">
            <h2 class="text-4xl font-black text-[#1E1E1E] mb-2">{{ $order->status->label() }}</h2>
            @if($order->status->isActive())
                <p class="text-stone-500">Estimated {{ $order->type->value === 'delivery' ? 'arrival' : 'pickup' }} time: 
                    <strong class="text-[#F37021] text-lg">{{ $order->created_at->addMinutes($order->estimated_minutes)->format('g:i A') }}</strong>
                </p>
            @endif
        </div>

        {{-- Visual Timeline --}}
        <div class="relative max-w-lg mx-auto mb-8">
            <div class="absolute left-8 sm:left-1/2 top-0 bottom-0 w-0.5 bg-stone-100 sm:-translate-x-1/2 rounded-full overflow-hidden">
                {{-- Progress Fill line could go here, but doing it via step borders is cleaner --}}
            </div>

            <div class="space-y-8">
                @foreach($trackingSteps as $step)
                    <div class="relative flex items-center sm:justify-center group">
                        
                        {{-- Left side time (Desktop) --}}
                        <div class="hidden sm:block w-1/2 pr-12 text-right">
                            @if($step['timestamp'])
                                <span class="text-sm font-bold text-stone-600">{{ $step['timestamp'] }}</span>
                            @endif
                        </div>

                        {{-- Node Icon --}}
                        @php
                            $nodeClasses = match($step['status']) {
                                'completed' => 'bg-[#1E1E1E] border-[#1E1E1E] text-white',
                                'current'   => 'bg-white border-[#F37021] text-[#F37021] ring-4 ring-[#F37021]/20 scale-110',
                                'upcoming'  => 'bg-white border-stone-200 text-stone-300',
                            };
                        @endphp
                        <div class="w-16 h-16 rounded-full border-4 flex items-center justify-center bg-white z-10 shadow-sm transition-all duration-300 {{ $nodeClasses }}">
                            <i class="fa-solid {{ $step['icon'] }} text-xl"></i>
                        </div>

                        {{-- Right side content --}}
                        <div class="w-full sm:w-1/2 pl-6 sm:pl-12 text-left">
                            <h4 class="text-lg font-bold {{ $step['status'] === 'upcoming' ? 'text-stone-400' : 'text-[#1E1E1E]' }}">{{ $step['label'] }}</h4>
                            @if($step['timestamp'])
                                <span class="sm:hidden text-xs font-bold text-stone-500 block mt-1">{{ $step['timestamp'] }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if($order->status->value === 'cancelled')
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-center mb-8">
                <i class="fa-solid fa-circle-exclamation text-xl mb-2 block"></i>
                <h4 class="font-bold text-sm">Order Cancelled</h4>
                <p class="text-xs mt-1">{{ $order->cancellation_reason ?? 'Please contact the store for details.' }}</p>
            </div>
        @endif

        {{-- Support Box --}}
        <div class="border-t border-stone-100 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-center sm:text-left">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-stone-400 mb-1">Store Information</span>
                <span class="text-sm font-bold text-[#1E1E1E]">{{ $order->store->name }}</span>
                <span class="text-xs text-stone-500 block">{{ $order->store->phone ?? 'Contact store via app' }}</span>
            </div>
            @if($order->store->phone)
                <a href="tel:{{ $order->store->phone }}" class="bg-stone-100 hover:bg-stone-200 text-stone-800 px-6 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-phone"></i>
                    Call Store
                </a>
            @endif
        </div>
    </div>

    {{-- Order Summary Toggle --}}
    <div x-data="{ showSummary: false }" class="bg-white border border-stone-200 rounded-3xl overflow-hidden shadow-sm">
        <button @click="showSummary = !showSummary" class="w-full flex items-center justify-between p-6 hover:bg-stone-50 transition-colors">
            <span class="font-bold text-[#1E1E1E] font-serif text-lg">Order Details Summary</span>
            <i class="fa-solid fa-chevron-down text-stone-400 transition-transform duration-300" :class="showSummary ? 'rotate-180' : ''"></i>
        </button>
        
        <div x-show="showSummary" x-collapse class="border-t border-stone-100 px-6 pb-6">
            <div class="pt-4 divide-y divide-stone-100">
                @foreach($order->items as $item)
                    <div class="py-3 flex justify-between items-start text-sm">
                        <div>
                            <span class="font-bold text-[#1E1E1E]">{{ $item->quantity }}x {{ $item->name }}</span>
                            <span class="text-xs text-stone-500 block">{{ $item->variation_name }}</span>
                        </div>
                        <span class="font-bold text-[#1E1E1E]">${{ number_format($item->total_price, 2) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="border-t border-stone-100 pt-4 mt-2 flex justify-between text-base font-black text-[#1E1E1E]">
                <span>Total</span>
                <span class="text-[#F37021]">${{ number_format($order->total, 2) }}</span>
            </div>
        </div>
    </div>

</div>
@endsection
