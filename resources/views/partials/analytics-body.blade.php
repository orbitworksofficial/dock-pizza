@if(config('services.gtm.id'))
    {{-- GTM noscript fallback — must be immediately after <body> --}}
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id={{ config('services.gtm.id') }}"
                height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
@endif
