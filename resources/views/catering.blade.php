@extends('layouts.app')

@section('title', 'Catering Services — Pizza Viva')

@section('content')
<div class="py-12 max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="text-center space-y-4 mb-16" data-aos="fade-down">
        <h1 class="text-4xl sm:text-6xl font-extrabold text-[#1E1E1E] font-serif tracking-tight">Premium Pizza Catering</h1>
        <p class="text-stone-500 max-w-lg mx-auto">Make your next party, corporate event, or family gathering unforgettable with our artisan wood-fired pizza catering packages.</p>
    </div>

    <!-- Packages Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-24">
        @foreach($packages as $package)
            <div class="bg-white border border-stone-200 rounded-3xl overflow-hidden hover:border-[#F37021] transition-all flex flex-col justify-between p-8 space-y-6 group shadow-sm" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="space-y-4">
                    <span class="inline-block px-3 py-1 rounded-full bg-[#F37021]/10 text-[#F37021] text-xs font-bold uppercase tracking-wider">
                        Popular Package
                    </span>
                    <h3 class="text-2xl font-bold text-[#1E1E1E] group-hover:text-[#F37021] transition-colors">{{ $package->name }}</h3>
                    <p class="text-stone-500 text-sm leading-relaxed">{{ $package->description }}</p>
                    
                    <div class="border-t border-stone-100 pt-4 space-y-2">
                        <span class="text-stone-400 text-xs uppercase tracking-wider block font-bold">Includes:</span>
                        <ul class="text-stone-600 text-sm space-y-1 font-medium">
                            @if($package->includes)
                                @foreach($package->includes as $inc)
                                    <li class="flex items-center space-x-2">
                                        <i class="fa-solid fa-circle-check text-[#F37021] text-xs"></i>
                                        <span>{{ $inc }}</span>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>

                <div class="flex items-end justify-between border-t border-stone-100 pt-6">
                    <div class="flex flex-col">
                        <span class="text-stone-400 text-xs font-bold uppercase tracking-wider">Starting at</span>
                        <span class="text-3xl font-extrabold text-[#1E1E1E]">${{ number_format($package->starting_price, 2) }}</span>
                    </div>
                    <span class="text-stone-400 text-xs font-semibold uppercase tracking-wider">
                        Min. {{ $package->min_people }} guests
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Catering Inquiry Form -->
    <div class="max-w-2xl mx-auto bg-white border border-stone-200 rounded-3xl p-8 space-y-6 shadow-xl relative overflow-hidden" data-aos="zoom-in">
        <div class="absolute top-0 right-0 w-32 h-32 bg-[#FDB813]/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="text-center space-y-2">
            <h2 class="text-3xl font-bold text-[#1E1E1E] font-serif">Request a Custom Catering Quote</h2>
            <p class="text-stone-500 text-sm">Tell us about your event and our coordinator will get back to you with options.</p>
        </div>

        <form action="{{ route('catering.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Name</label>
                    <input type="text" name="name" id="name" required class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all">
                </div>
                <div>
                    <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Phone</label>
                    <input type="text" name="phone" id="phone" required class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all">
                </div>
            </div>

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Email Address</label>
                <input type="email" name="email" id="email" required class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="event_date" class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Event Date</label>
                    <input type="date" name="event_date" id="event_date" required class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all">
                </div>
                <div>
                    <label for="guest_count" class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Approx. Guest Count</label>
                    <input type="number" name="guest_count" id="guest_count" min="5" required class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all">
                </div>
            </div>

            <div>
                <label for="special_requests" class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Special Requests / Preferences</label>
                <textarea name="special_requests" id="special_requests" rows="4" placeholder="Dietary restrictions, preferred pizza varieties, etc." class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all"></textarea>
            </div>

            <button type="submit" class="w-full btn-orange py-4 px-4 rounded-2xl uppercase tracking-wider text-sm transition-all hover:scale-[1.01] shadow-lg shadow-[#F37021]/20 font-bold">
                Submit Request
            </button>
        </form>
    </div>
</div>
@endsection
