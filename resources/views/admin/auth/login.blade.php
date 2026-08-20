<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in · {{ config('seo.organization.name') }}</title>

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
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])

    <style>
        .auth { display: grid; grid-template-columns: 1fr 1fr; min-height: 100vh; }

        .auth__brand {
            background: var(--ink);
            color: #fff;
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        [data-theme='dark'] .auth__brand { background: var(--surface-2); }

        /* Soft accent wash — no heavy gradients or shadows */
        .auth__brand::after {
            content: '';
            position: absolute;
            width: 460px; height: 460px;
            right: -170px; bottom: -170px;
            border-radius: 50%;
            background: radial-gradient(circle, color-mix(in srgb, var(--accent) 26%, transparent), transparent 68%);
        }

        .auth__mark {
            width: 34px; height: 34px;
            border-radius: var(--radius);
            background: var(--accent);
            color: var(--accent-ink);
            display: grid; place-items: center;
            font-weight: 700; font-size: 15px;
        }

        .auth__pitch { position: relative; z-index: 1; max-width: 400px; }
        .auth__pitch h1 {
            font-size: 29px; font-weight: 650;
            letter-spacing: -0.026em; line-height: 1.22;
            color: #fff; margin-bottom: 12px;
        }
        .auth__pitch p { font-size: 14.5px; color: rgba(255,255,255,.62); line-height: 1.62; }

        .auth__points { list-style: none; padding: 0; margin: 26px 0 0; }
        .auth__points li {
            display: flex; align-items: center; gap: 9px;
            font-size: 13.5px; color: rgba(255,255,255,.72);
            padding: 5px 0;
        }
        .auth__points i { color: var(--accent); font-size: 12px; }

        .auth__foot { position: relative; z-index: 1; font-size: 12.5px; color: rgba(255,255,255,.42); }

        .auth__form {
            display: grid; place-items: center;
            padding: 48px 40px;
            background: var(--bg);
        }
        .auth__inner { width: 100%; max-width: 348px; }

        .auth__title { font-size: 21px; font-weight: 650; letter-spacing: -0.02em; }
        .auth__sub { font-size: 13.5px; color: var(--muted); margin-top: 5px; margin-bottom: 26px; }

        @media (max-width: 860px) {
            .auth { grid-template-columns: 1fr; }
            .auth__brand { display: none; }
            .auth__form { padding: 32px 20px; }
        }
    </style>
</head>

<body class="admin">
<div class="auth">

    {{-- Brand panel --}}
    <div class="auth__brand">
        <div class="auth__mark">{{ Str::substr(config('seo.organization.name'), 0, 1) }}</div>

        <div class="auth__pitch">
            <h1>{{ config('seo.organization.name') }} content studio</h1>
            <p>Publish posts, manage media and tune every page's search presence from one place.</p>
            <ul class="auth__points">
                <li><i class="fa-solid fa-check"></i> Editorial workflow with drafts and scheduling</li>
                <li><i class="fa-solid fa-check"></i> Per-page SEO and structured data</li>
                <li><i class="fa-solid fa-check"></i> Generated sitemap and robots.txt</li>
            </ul>
        </div>

        <div class="auth__foot">&copy; {{ date('Y') }} {{ config('seo.organization.name') }}</div>
    </div>

    {{-- Form panel --}}
    <div class="auth__form">
        <div class="auth__inner">
            <h2 class="auth__title">Sign in</h2>
            <p class="auth__sub">Enter your credentials to continue.</p>

            <form method="POST" action="{{ route('admin.login.attempt') }}" novalidate>
                @csrf

                <x-admin.field name="email" label="Email address" required>
                    <input type="email" name="email" id="email" class="input"
                           value="{{ old('email') }}" autocomplete="username"
                           autofocus required placeholder="you@example.com">
                </x-admin.field>

                <x-admin.field name="password" label="Password" required>
                    <input type="password" name="password" id="password" class="input"
                           autocomplete="current-password" required placeholder="••••••••">
                </x-admin.field>

                <div class="row-between" style="margin: 18px 0 20px;">
                    <label class="checkline" style="cursor:pointer;">
                        <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                        <span class="checkline__text">Keep me signed in</span>
                    </label>
                </div>

                <button type="submit" class="btn btn--primary btn--block">
                    Sign in <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i>
                </button>
            </form>

            <p class="small muted" style="margin-top:22px; text-align:center;">
                <a href="{{ route('home') }}" class="btn--link">Back to {{ config('seo.organization.name') }}</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
