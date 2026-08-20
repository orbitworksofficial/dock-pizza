@php
    // GTM wins outright. Because $gaId is only read when $gtmId is empty,
    // there is no configuration that can load both and double-count pageviews.
    $gtmId = config('services.gtm.id');
    $gaId = $gtmId ? null : config('services.ga.id');
@endphp

@if($gtmId || $gaId)
    {{--
        Consent Mode defaults must be in place BEFORE any tag loads, or the
        first pageview is recorded without consent. Written to dataLayer,
        which both GTM and gtag.js read.
    --}}
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }

        gtag('consent', 'default', {
            ad_storage: 'denied',
            analytics_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied',
            wait_for_update: 500
        });

        // Replay a previous grant before the tags initialise. Without this a
        // returning visitor — who never sees the banner again — would silently
        // stay denied.
        (function () {
            try {
                if (localStorage.getItem('dock_consent') === 'granted') {
                    gtag('consent', 'update', {
                        ad_storage: 'granted',
                        analytics_storage: 'granted',
                        ad_user_data: 'granted',
                        ad_personalization: 'granted'
                    });
                    // Third-party tags (Meta Pixel et al) do not understand
                    // Consent Mode, so they trigger on this event instead of
                    // All Pages.
                    dataLayer.push({ event: 'consent_granted' });
                }
            } catch (e) {
                // Storage unavailable (private mode) — stay denied.
            }
        })();
    </script>
@endif

@if($gtmId)
    {{-- Google Tag Manager — the only loader when GTM_ID is set --}}
    <script>
        (function (w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', '{{ $gtmId }}');
    </script>
@elseif($gaId)
    {{-- GA4 direct, only because GTM_ID is empty --}}
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>
        gtag('js', new Date());
        gtag('config', '{{ $gaId }}');
    </script>
@endif
