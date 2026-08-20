<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Access denied &middot; {{ config('seo.organization.name', 'Site') }}</title>

    <script>
        (function () {
            try {
                var t = localStorage.getItem('admin_theme');
                if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                document.documentElement.dataset.theme = t;
            } catch (e) { document.documentElement.dataset.theme = 'light'; }
        })();
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css'])
</head>

<body class="admin">
<div style="min-height:100vh; display:grid; place-items:center; padding:24px;">
    <div style="max-width:400px; text-align:center;">
        <div style="width:46px; height:46px; margin:0 auto 16px; border-radius:var(--radius-lg);
                    background:var(--danger-soft); border:1px solid var(--danger-line);
                    display:grid; place-items:center; color:var(--danger); font-size:18px;">
            <i class="fa-solid fa-lock"></i>
        </div>

        <h1 style="font-size:20px; font-weight:650; letter-spacing:-0.02em;">Access denied</h1>

        <p class="muted" style="font-size:13.5px; margin:8px 0 22px; line-height:1.6;">
            {{ $exception?->getMessage() ?: "You don't have permission to view this page." }}
            @auth
                @if(!auth()->user()->role->canAuthor())
                    <br><br>You're signed in as <strong>{{ auth()->user()->email }}</strong>,
                    which doesn't have admin access.
                @endif
            @endauth
        </p>

        <div class="row" style="justify-content:center;">
            @auth
                @if(auth()->user()->role->canAuthor())
                    <a href="{{ route('admin.dashboard') }}" class="btn btn--primary">
                        <i class="fa-solid fa-gauge" style="font-size:11px;"></i> Dashboard
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn--ghost">Sign out</button>
                </form>
            @else
                <a href="{{ route('admin.login') }}" class="btn btn--primary">Sign in</a>
            @endauth
            <a href="{{ route('home') }}" class="btn btn--ghost">Go to site</a>
        </div>
    </div>
</div>
</body>
</html>
