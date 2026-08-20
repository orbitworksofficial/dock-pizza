<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') · {{ config('seo.organization.name') }}</title>

    {{-- Resolve the theme before first paint so dark mode never flashes white --}}
    <script>
        (function () {
            try {
                var t = localStorage.getItem('admin_theme');
                if (!t) {
                    t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                document.documentElement.dataset.theme = t;
            } catch (e) {
                document.documentElement.dataset.theme = 'light';
            }
        })();
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @stack('head')
</head>

<body class="admin" x-data="adminTheme()">

<div class="layout">

    {{-- ── Sidebar ─────────────────────────────────────────── --}}
    <aside class="sidebar" :class="$store.sidebar.open && 'is-open'">
        <div class="sidebar__brand">
            <div class="sidebar__mark">{{ Str::substr(config('seo.organization.name'), 0, 1) }}</div>
            <span class="sidebar__name">{{ config('seo.organization.name') }}</span>
        </div>

        <nav class="sidebar__nav">
            @include('admin.partials.nav')
        </nav>

        <div class="sidebar__foot">
            <div class="row" style="padding: 4px 6px;">
                <div style="min-width:0; flex:1;">
                    <div class="truncate" style="font-size:12.5px; font-weight:550;">{{ auth()->user()->name }}</div>
                    <div class="truncate muted" style="font-size:11.5px;">{{ auth()->user()->role->label() }}</div>
                </div>
                <button type="button" class="btn btn--ghost btn--icon" @click="toggle()"
                        :title="theme === 'dark' ? 'Switch to light' : 'Switch to dark'"
                        aria-label="Toggle colour theme">
                    <i class="fa-solid" :class="theme === 'dark' ? 'fa-sun' : 'fa-moon'"></i>
                </button>
                <form method="POST" action="{{ route('admin.logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn--ghost btn--icon" title="Sign out" aria-label="Sign out">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="scrim" x-show="$store.sidebar.open" x-cloak
         @click="$store.sidebar.close()" x-transition.opacity></div>

    {{-- ── Main ────────────────────────────────────────────── --}}
    <div class="main">
        <header class="pageheader">
            <button type="button" class="btn btn--ghost btn--icon sidebar__toggle"
                    @click="$store.sidebar.toggle()" aria-label="Open navigation">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="pageheader__titles">
                <div class="pageheader__title">@yield('page_title', 'Dashboard')</div>
                @hasSection('page_sub')
                    <div class="pageheader__sub">@yield('page_sub')</div>
                @endif
            </div>

            <div class="pageheader__actions">@yield('page_actions')</div>
        </header>

        <main class="content @yield('content_class')">
            @yield('content')
        </main>
    </div>
</div>

{{-- ── Toasts ──────────────────────────────────────────────── --}}
<div class="toasts">
    <template x-for="t in $store.toasts.items" :key="t.id">
        <div class="toast" :class="`toast--${t.type}`" role="status">
            <i class="toast__icon fa-solid"
               :class="{ ok: 'fa-circle-check', danger: 'fa-circle-exclamation', warn: 'fa-triangle-exclamation' }[t.type] || 'fa-circle-info'"></i>
            <div class="toast__body" x-text="t.message"></div>
            <button type="button" class="toast__close" @click="$store.toasts.dismiss(t.id)" aria-label="Dismiss">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </template>
</div>

{{-- Server-side flashes become toasts --}}
@if(session('success') || session('error') || session('warning'))
    <script>
        document.addEventListener('alpine:init', () => {
            @if(session('success')) Alpine.store('toasts').push(@js(session('success')), 'ok'); @endif
            @if(session('error'))   Alpine.store('toasts').push(@js(session('error')), 'danger'); @endif
            @if(session('warning')) Alpine.store('toasts').push(@js(session('warning')), 'warn'); @endif
        });
    </script>
@endif

@stack('scripts')
</body>
</html>
