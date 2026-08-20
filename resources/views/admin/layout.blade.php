<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- The admin area is never indexed --}}
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('admin_title', 'Admin') — {{ config('seo.organization.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
          rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#F9F9FB] text-[#1E1E1E] font-sans antialiased min-h-screen">

    <header class="bg-white border-b border-stone-200 sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-4">
            <div class="flex items-center gap-6">
                <a href="{{ route('admin.seo.index') }}" class="font-bold text-sm tracking-tight">
                    {{ config('seo.organization.name') }} <span class="text-stone-400 font-medium">Admin</span>
                </a>
                <nav class="hidden sm:flex items-center gap-1 text-xs font-bold uppercase tracking-wider">
                    <a href="{{ route('admin.seo.index') }}"
                       class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.seo.index', 'admin.seo.edit', 'admin.seo.create') ? 'bg-[#FEF6E4] text-[#B4530A]' : 'text-stone-500 hover:text-stone-900' }}">
                        Page SEO
                    </a>
                    <a href="{{ route('admin.seo.technical') }}"
                       class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.seo.technical') ? 'bg-[#FEF6E4] text-[#B4530A]' : 'text-stone-500 hover:text-stone-900' }}">
                        Technical SEO
                    </a>
                </nav>
            </div>
            <a href="{{ route('home') }}" class="text-xs text-stone-500 hover:text-stone-900">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back to site
            </a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-sm">
                <i class="fa-solid fa-circle-check mr-1.5"></i> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-sm">
                <p class="font-bold mb-1"><i class="fa-solid fa-triangle-exclamation mr-1.5"></i>Please fix the following:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('admin_content')
    </main>

</body>
</html>
