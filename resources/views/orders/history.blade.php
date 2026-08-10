@extends('layouts.app')

@section('title', 'Order History — Pizza Viva')

@section('content')
<div class="py-12 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="mb-10">
        <h1 class="text-3xl sm:text-5xl font-extrabold text-[#1E1E1E] font-serif tracking-tight">Order History</h1>
        <p class="text-stone-500 mt-2">View your past orders, track active deliveries, and easily reorder your favorites.</p>
    </div>

    @if($orders->isEmpty())
        <div class="text-center py-20 bg-white border border-stone-200 rounded-3xl shadow-sm">
            <div class="w-20 h-20 bg-stone-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fa-solid fa-receipt text-4xl text-stone-300"></i>
            </div>
            <h2 class="text-2xl font-bold text-[#1E1E1E] font-serif mb-2">No Orders Yet</h2>
            <p class="text-stone-500 mb-8 max-w-sm mx-auto">You haven't placed any orders with us yet. Explore our artisan menu and treat yourself!</p>
            <a href="{{ route('menu.index') }}" class="btn-yellow px-8 py-3.5 rounded-full text-sm font-bold uppercase tracking-wider shadow-sm">
                Explore Menu
            </a>
        </div>
    @else
        <div class="space-y-6">
            @foreach($orders as $order)
                <div class="bg-white border border-stone-200 rounded-3xl p-6 sm:p-8 shadow-sm hover:shadow-md transition-shadow group flex flex-col sm:flex-row gap-6">
                    {{-- Left Side: Order Quick Info --}}
                    <div class="flex-grow space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-stone-100 pb-4">
                            <div>
                                <span class="text-2xl font-black text-[#1E1E1E] mr-3">{{ $order->order_number }}</span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-stone-100 text-stone-600">
                                    {{ $order->created_at->format('M j, Y - g:i A') }}
                                </span>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $order->status->isActive() ? 'bg-amber-50 text-amber-700 border border-amber-200' : ($order->status->isCompleted() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-stone-50 text-stone-600 border border-stone-200') }}">
                                <i class="fa-solid {{ $order->status->icon() }} mr-1.5"></i>
                                {{ $order->status->label() }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="block text-[10px] uppercase tracking-wider text-stone-400 font-bold mb-0.5">Order Type</span>
                                <span class="font-semibold text-[#1E1E1E] capitalize">
                                    <i class="fa-solid {{ $order->type->value === 'delivery' ? 'fa-truck' : 'fa-basket-shopping' }} text-stone-400 mr-1.5"></i>
                                    {{ $order->type->label() }}
                                </span>
                            </div>
                            <div>
                                <span class="block text-[10px] uppercase tracking-wider text-stone-400 font-bold mb-0.5">Total Amount</span>
                                <span class="font-bold text-[#F37021]">${{ number_format($order->total, 2) }}</span>
                            </div>
                            <div class="sm:col-span-3">
                                <span class="block text-[10px] uppercase tracking-wider text-stone-400 font-bold mb-0.5">Items ({{ $order->items->sum('quantity') }})</span>
                                <span class="text-stone-600 text-sm line-clamp-1">
                                    {{ $order->items->map(function($item) { return $item->quantity . 'x ' . $item->name; })->implode(', ') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Side: Actions --}}
                    <div class="flex flex-col justify-center gap-3 sm:border-l sm:border-stone-100 sm:pl-6 min-w-[160px]">
                        @if($order->status->isActive())
                            <a href="{{ route('order.track', $order->order_number) }}" class="w-full btn-orange text-center px-6 py-3 rounded-2xl text-xs font-bold uppercase tracking-wider shadow-sm">
                                Track Order
                            </a>
                        @endif
                        <a href="{{ route('order.confirmation', $order->order_number) }}" class="w-full text-center border border-[#FDB813] text-[#1E1E1E] hover:bg-[#FDB813]/10 px-6 py-3 rounded-2xl text-xs font-bold uppercase tracking-wider transition-all">
                            View Details
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
