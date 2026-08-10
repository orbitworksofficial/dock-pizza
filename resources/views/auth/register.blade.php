@extends('layouts.app')

@section('title', 'Register — Pizza Viva')

@section('content')
<div class="py-24 max-w-md mx-auto px-4 sm:px-6">
    <div class="bg-white border border-stone-200 rounded-3xl p-8 space-y-6 shadow-sm" data-aos="zoom-in" data-aos-duration="500">
        <div class="text-center space-y-2">
            <h1 class="text-3xl font-extrabold text-[#1E1E1E] font-serif">Create Account</h1>
            <p class="text-stone-500 text-sm">Join the Pizza Viva club to earn points and get special offers.</p>
        </div>

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-600 text-xs rounded-xl space-y-1">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Full Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all placeholder-stone-400" placeholder="John Doe">
            </div>

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all placeholder-stone-400" placeholder="you@example.com">
            </div>

            <div>
                <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Phone Number</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all placeholder-stone-400" placeholder="+1 (555) 000-0000">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Password</label>
                <input type="password" name="password" id="password" required class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all placeholder-stone-400" placeholder="Create a password">
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full bg-[#F3F4F6] border border-transparent rounded-2xl py-3 px-4 text-[#1E1E1E] focus:outline-none focus:bg-white focus:border-[#FDB813] text-sm transition-all placeholder-stone-400" placeholder="Confirm your password">
            </div>

            <button type="submit" class="w-full btn-orange font-bold py-3.5 px-4 rounded-2xl uppercase tracking-wider text-sm transition-all hover:scale-[1.01] shadow-lg shadow-[#F37021]/20">
                Register
            </button>
        </form>

        <div class="border-t border-stone-100 pt-6 text-center text-stone-500 text-sm">
            Already have an account? 
            <a href="{{ route('login') }}" class="text-[#F37021] hover:text-[#FDB813] font-semibold transition-colors">Sign In</a>
        </div>
    </div>
</div>
@endsection
