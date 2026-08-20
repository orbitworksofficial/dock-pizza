@if(config('services.gtm.id') || config('services.ga.id'))
    <div id="dock-cookie-banner"
         class="fixed bottom-0 inset-x-0 z-[10060] hidden p-4 sm:p-5"
         role="dialog" aria-live="polite" aria-label="Cookie consent">
        <div class="max-w-4xl mx-auto bg-white border border-stone-200 rounded-2xl shadow-xl p-5 sm:p-6
                    flex flex-col sm:flex-row sm:items-center gap-4">
            <p class="text-sm text-stone-600 flex-grow">
                We use cookies to understand how our site is used and to improve your experience.
                You can accept or decline analytics cookies.
            </p>
            <div class="flex items-center gap-3 flex-shrink-0">
                <button type="button" id="dock-cookie-decline"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider
                               text-stone-600 hover:text-stone-900 transition-colors">
                    Decline
                </button>
                <button type="button" id="dock-cookie-accept"
                        class="btn-yellow px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider">
                    Accept
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var KEY = 'dock_consent';
            var banner = document.getElementById('dock-cookie-banner');
            if (!banner) return;

            function stored() {
                try { return localStorage.getItem(KEY); } catch (e) { return null; }
            }

            function remember(value) {
                try { localStorage.setItem(KEY, value); } catch (e) { /* private mode */ }
            }

            function grant() {
                window.dataLayer = window.dataLayer || [];

                // Fire on both code paths: gtag() exists when the head script
                // ran, but push directly otherwise so the queued form still
                // reaches GTM.
                if (typeof window.gtag === 'function') {
                    window.gtag('consent', 'update', {
                        ad_storage: 'granted',
                        analytics_storage: 'granted',
                        ad_user_data: 'granted',
                        ad_personalization: 'granted'
                    });
                } else {
                    window.dataLayer.push(['consent', 'update', {
                        ad_storage: 'granted',
                        analytics_storage: 'granted',
                        ad_user_data: 'granted',
                        ad_personalization: 'granted'
                    }]);
                }

                // Named event for tags that ignore Consent Mode entirely.
                window.dataLayer.push({ event: 'consent_granted' });
            }

            // Already answered — the head script replayed any grant.
            if (stored()) return;

            banner.classList.remove('hidden');

            document.getElementById('dock-cookie-accept').addEventListener('click', function () {
                remember('granted');
                grant();
                banner.classList.add('hidden');
            });

            document.getElementById('dock-cookie-decline').addEventListener('click', function () {
                remember('denied');
                banner.classList.add('hidden');
            });
        })();
    </script>
@endif
