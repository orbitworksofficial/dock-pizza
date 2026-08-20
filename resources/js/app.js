import './bootstrap';
import Alpine from 'alpinejs';
import jQuery from 'jquery';

// ═══════════════════════════════════════════════════════════════
// Alpine.js Setup
// ═══════════════════════════════════════════════════════════════
Alpine.store('search', {
    query: ''
});

Alpine.store('cart', {
    items: JSON.parse(localStorage.getItem('viva_cart') || '[]'),

    add(item) {
        this.items.push(item);
        this.persist();
    },

    remove(index) {
        this.items.splice(index, 1);
        this.persist();
    },

    persist() {
        localStorage.setItem('viva_cart', JSON.stringify(this.items));
        window.dispatchEvent(new CustomEvent('cart-updated'));
    },

    get total() {
        return this.items.reduce((sum, item) => sum + parseFloat(item.price) * (item.quantity || 1), 0);
    },
});

// ═══════════════════════════════════════════════════════════════
// Admin helpers
// Registered here rather than in a separate bundle so the admin has no
// second entry point to deploy. Inert on the storefront.
// ═══════════════════════════════════════════════════════════════

window.adminTheme = function () {
    return {
        theme: document.documentElement.dataset.theme || 'light',
        toggle() {
            this.theme = this.theme === 'dark' ? 'light' : 'dark';
            document.documentElement.dataset.theme = this.theme;
            try {
                localStorage.setItem('admin_theme', this.theme);
            } catch (e) {
                // Private mode — the choice simply will not persist.
            }
        },
    };
};

Alpine.store('toasts', {
    items: [],
    nextId: 1,

    push(message, type = 'ok', timeout = 4500) {
        const id = this.nextId++;
        this.items.push({ id, message, type });
        if (timeout) setTimeout(() => this.dismiss(id), timeout);
        return id;
    },

    dismiss(id) {
        const i = this.items.findIndex((t) => t.id === id);
        if (i !== -1) this.items.splice(i, 1);
    },
});

window.toast = (message, type = 'ok') => Alpine.store('toasts').push(message, type);

Alpine.store('sidebar', {
    open: false,
    toggle() { this.open = !this.open; },
    close() { this.open = false; },
});

// Live character counter shared by the SEO fields.
window.charCounter = function (initial = '', warn = 60, max = 70) {
    return {
        value: initial,
        get count() { return this.value.length; },
        get counterClass() {
            if (this.count > max) return 'is-over';
            if (this.count > warn) return 'is-warn';
            return '';
        },
    };
};

window.Alpine = Alpine;
Alpine.start();

// ═══════════════════════════════════════════════════════════════
// jQuery Setup
// ═══════════════════════════════════════════════════════════════
window.$ = window.jQuery = jQuery;

function highlightZip($zipInput) {
    if (!$zipInput || !$zipInput.length) return;
    $zipInput.trigger('change').addClass('ring-2 ring-[#E07B2D]/40');
    setTimeout(function () {
        $zipInput.removeClass('ring-2 ring-[#E07B2D]/40');
    }, 900);
}

/**
 * Free US street autocomplete (Photon / OpenStreetMap).
 * Works without a Google Maps API key and auto-fills ZIP.
 */
window.__bindStreetAutocomplete = function bindStreetAutocomplete() {
    const pairs = [
        { address: '#address', zip: '#zip_code' },
        { address: '#modal_address', zip: '#modal_zip_code' },
    ];

    pairs.forEach(function (pair) {
        const addressInput = document.querySelector(pair.address);
        const zipInput = document.querySelector(pair.zip);
        if (!addressInput || addressInput.dataset.streetAcBound === '1') {
            return;
        }

        // Prefer Google Places when it is already bound on this field
        if (addressInput.dataset.placesBound === '1') {
            return;
        }

        addressInput.dataset.streetAcBound = '1';

        const $input = $(addressInput);
        const $zip = $(zipInput);
        let $wrap = $input.parent();
        if ($wrap.css('position') === 'static') {
            $wrap.css('position', 'relative');
        }

        const $list = $('<ul class="dock-address-suggestions" role="listbox" aria-label="Address suggestions"></ul>');
        $wrap.append($list);

        let timer = null;
        let requestId = 0;

        function hideList() {
            $list.removeClass('is-open').empty();
        }

        function selectSuggestion(item) {
            $input.val(item.street);
            if (item.zip) {
                $zip.val(item.zip);
                highlightZip($zip);
            }
            hideList();
            $input.trigger('change');
        }

        function renderSuggestions(items) {
            $list.empty();
            if (!items.length) {
                hideList();
                return;
            }

            items.forEach(function (item) {
                const $li = $('<li role="option" tabindex="-1"></li>');
                $li.html(
                    '<span class="dock-address-suggestions__street"></span>' +
                    '<span class="dock-address-suggestions__meta"></span>'
                );
                $li.find('.dock-address-suggestions__street').text(item.street);
                $li.find('.dock-address-suggestions__meta').text(item.meta);
                $li.on('mousedown', function (e) {
                    e.preventDefault();
                    selectSuggestion(item);
                });
                $list.append($li);
            });

            $list.addClass('is-open');
        }

        function searchAddresses(query) {
            const currentRequest = ++requestId;
            const url = 'https://photon.komoot.io/api/?lang=en&limit=7&q=' + encodeURIComponent(query);

            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                timeout: 8000,
            })
                .done(function (data) {
                    if (currentRequest !== requestId) return;

                    const features = (data && data.features) ? data.features : [];
                    const items = [];

                    features.forEach(function (feature) {
                        const p = feature.properties || {};
                        const country = String(p.countrycode || p.country || '').toUpperCase();
                        if (country && country !== 'US' && country !== 'USA' && country !== 'UNITED STATES') {
                            return;
                        }

                        const street = [p.housenumber, p.street || p.name].filter(Boolean).join(' ').trim();
                        if (!street) return;

                        const zip = p.postcode ? String(p.postcode).split('-')[0].trim() : '';
                        const meta = [p.city || p.town || p.village || p.county, p.state, zip]
                            .filter(Boolean)
                            .join(', ');

                        items.push({ street: street, zip: zip, meta: meta });
                    });

                    // de-dupe by street+zip
                    const seen = {};
                    const unique = items.filter(function (item) {
                        const key = (item.street + '|' + item.zip).toLowerCase();
                        if (seen[key]) return false;
                        seen[key] = true;
                        return true;
                    }).slice(0, 6);

                    renderSuggestions(unique);
                })
                .fail(function () {
                    if (currentRequest !== requestId) return;
                    hideList();
                });
        }

        $input.on('input', function () {
            const q = String($input.val() || '').trim();
            clearTimeout(timer);
            if (q.length < 3) {
                hideList();
                return;
            }
            timer = setTimeout(function () {
                searchAddresses(q);
            }, 320);
        });

        $input.on('keydown', function (e) {
            if (e.key === 'Escape') {
                hideList();
            }
        });

        $(document).on('click.streetAc' + pair.address, function (e) {
            if (!$(e.target).closest($wrap).length) {
                hideList();
            }
        });
    });
};

/**
 * Bind Google Places Autocomplete to address fields (US only).
 * Selecting a suggestion fills street + ZIP automatically.
 */
window.__bindDockPlaces = function bindDockPlaces() {
    if (!window.google || !google.maps || !google.maps.places) {
        window.__bindStreetAutocomplete();
        return;
    }

    const pairs = [
        { address: '#address', zip: '#zip_code' },
        { address: '#modal_address', zip: '#modal_zip_code' },
    ];

    pairs.forEach(function (pair) {
        const addressInput = document.querySelector(pair.address);
        const zipInput = document.querySelector(pair.zip);
        if (!addressInput || addressInput.dataset.placesBound === '1') {
            return;
        }

        const autocomplete = new google.maps.places.Autocomplete(addressInput, {
            types: ['address'],
            componentRestrictions: { country: 'us' },
            fields: ['address_components', 'formatted_address', 'name'],
        });

        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            if (!place || !place.address_components) {
                return;
            }

            let streetNumber = '';
            let route = '';
            let postalCode = '';
            let postalSuffix = '';

            place.address_components.forEach(function (component) {
                const types = component.types;
                if (types.includes('street_number')) streetNumber = component.long_name;
                if (types.includes('route')) route = component.long_name;
                if (types.includes('postal_code')) postalCode = component.long_name;
                if (types.includes('postal_code_suffix')) postalSuffix = component.long_name;
            });

            const streetLine = [streetNumber, route].filter(Boolean).join(' ').trim();
            if (streetLine) {
                addressInput.value = streetLine;
            } else if (place.formatted_address) {
                addressInput.value = place.formatted_address.split(',')[0];
            }

            if (zipInput && postalCode) {
                zipInput.value = postalSuffix ? `${postalCode}-${postalSuffix}` : postalCode;
                highlightZip($(zipInput));
            }
        });

        addressInput.dataset.placesBound = '1';
    });
};

window.initDockGooglePlaces = function () {
    window.__googlePlacesLoaded = true;
    window.__bindDockPlaces();
};

$(document).ready(function () {

    // ─── Sticky Header with scroll effect ─────────────────────
    const $header = $('.header');
    $(window).on('scroll', function () {
        if ($(this).scrollTop() > 50) {
            $header.addClass('scrolled');
        } else {
            $header.removeClass('scrolled');
        }
    });

    // ─── Smooth Scroll for all anchor links ───────────────────
    $('a[href^="#"]').on('click', function (e) {
        const targetId = this.getAttribute('href');
        if (targetId === '#' || targetId === '#0') return;
        const $target = $(targetId);
        if ($target.length) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: $target.offset().top - 100
            }, 600);
        }
    });

    // ─── Scroll Reveal Animations ─────────────────────────────
    const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale, .stagger-children');

    if (revealElements.length > 0) {
        const revealObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                        revealObserver.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.1, rootMargin: '0px 0px -50px 0px' }
        );

        revealElements.forEach((el) => revealObserver.observe(el));
    }

    // ─── Hero Carousel ────────────────────────────────────────
    const $carousel = $('.hero-carousel');
    if ($carousel.length) {
        let currentSlide = 0;
        const $slides = $carousel.find('.slide');
        const $dots = $carousel.find('.dot');
        const totalSlides = $slides.length;

        function showSlide(index) {
            $slides.removeClass('active').hide();
            $dots.removeClass('active');
            $slides.eq(index).addClass('active').fadeIn(600);
            $dots.eq(index).addClass('active');
            currentSlide = index;
        }

        function nextSlide() {
            showSlide((currentSlide + 1) % totalSlides);
        }

        function prevSlide() {
            showSlide((currentSlide - 1 + totalSlides) % totalSlides);
        }

        // Auto-play
        let autoPlay = setInterval(nextSlide, 5000);

        // Navigation
        $carousel.find('.carousel-next').on('click', function () {
            clearInterval(autoPlay);
            nextSlide();
            autoPlay = setInterval(nextSlide, 5000);
        });

        $carousel.find('.carousel-prev').on('click', function () {
            clearInterval(autoPlay);
            prevSlide();
            autoPlay = setInterval(nextSlide, 5000);
        });

        // Dots navigation
        $dots.on('click', function () {
            clearInterval(autoPlay);
            showSlide($(this).index());
            autoPlay = setInterval(nextSlide, 5000);
        });

        // Touch support
        let touchStartX = 0;
        let touchEndX = 0;
        $carousel[0].addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });
        $carousel[0].addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            if (touchStartX - touchEndX > 50) nextSlide();
            if (touchEndX - touchStartX > 50) prevSlide();
        });

        // Init first slide
        showSlide(0);
    }

    // ─── Mobile Menu Toggle ───────────────────────────────────
    const $mobileToggle = $('#mobile-menu-toggle');
    const $mobileMenu = $('#mobile-menu');
    const $mobileOverlay = $('#mobile-overlay');
    const $mobileClose = $('#mobile-menu-close');

    function openMobileMenu() {
        $mobileMenu.addClass('open');
        $mobileOverlay.addClass('open');
        $('body').css('overflow', 'hidden');
    }

    function closeMobileMenu() {
        $mobileMenu.removeClass('open');
        $mobileOverlay.removeClass('open');
        $('body').css('overflow', '');
    }

    $mobileToggle.on('click', openMobileMenu);
    $mobileClose.on('click', closeMobileMenu);
    $mobileOverlay.on('click', closeMobileMenu);
    $mobileMenu.find('a').on('click', closeMobileMenu);

    // ─── Counter Animation ────────────────────────────────────
    const $counters = $('[data-counter]');
    if ($counters.length) {
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const $el = $(entry.target);
                    const target = parseInt($el.data('counter'));
                    const duration = 2000;
                    const step = target / (duration / 16);
                    let current = 0;

                    const timer = setInterval(() => {
                        current += step;
                        if (current >= target) {
                            $el.text(target.toLocaleString());
                            clearInterval(timer);
                        } else {
                            $el.text(Math.floor(current).toLocaleString());
                        }
                    }, 16);

                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        $counters.each(function () {
            counterObserver.observe(this);
        });
    }

    // ─── Parallax effect on hero ──────────────────────────────
    $(window).on('scroll', function () {
        const scrolled = $(this).scrollTop();
        $('.parallax-bg').css('transform', `translateY(${scrolled * 0.3}px)`);
    });

    // ─── Navbar active state ──────────────────────────────────
    const currentPath = window.location.pathname;
    $('.nav-link').each(function () {
        const linkPath = $(this).attr('href');
        if (linkPath === currentPath || (currentPath === '/' && linkPath === '/')) {
            $(this).addClass('active');
        }
    });

    // ─── Soft page transitions (no hard flash) ────────────────
    $(document).on('click', 'a.js-soft-nav', function (e) {
        const href = $(this).attr('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.which === 2) return;

        e.preventDefault();
        $('body').addClass('is-page-transitioning');
        setTimeout(function () {
            window.location.href = href;
        }, 180);
    });

    // ─── AJAX location forms (no full-page refresh on errors) ─
    $(document).on('submit', 'form[data-location-form], form.js-location-form', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $btn = $form.find('.js-location-submit, button[type="submit"]').first();
        const $scope = $form.closest('.space-y-6, [x-data], form').parent();
        const $errorBox = $scope.find('.location-ajax-error').first().length
            ? $scope.find('.location-ajax-error').first()
            : $form.siblings('.location-ajax-error').first();
        const originalBtnHtml = $btn.html();
        const csrf = $('meta[name="csrf-token"]').attr('content');

        if ($errorBox.length) {
            $errorBox.removeClass('is-visible').text('');
        }
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Please wait...');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
            },
        })
            .done(function (res) {
                if (res.success && res.redirect) {
                    $('body').addClass('is-page-transitioning');
                    setTimeout(function () {
                        window.location.href = res.redirect;
                    }, 200);
                    return;
                }

                if ($errorBox.length) {
                    $errorBox.text(res.message || 'Unable to set location.').addClass('is-visible');
                } else {
                    alert(res.message || 'Unable to set location.');
                }
                $btn.prop('disabled', false).html(originalBtnHtml);
            })
            .fail(function (xhr) {
                let message = 'Something went wrong. Please try again.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const first = Object.values(xhr.responseJSON.errors)[0];
                        message = Array.isArray(first) ? first[0] : first;
                    }
                }
                if ($errorBox.length) {
                    $errorBox.text(message).addClass('is-visible');
                } else {
                    alert(message);
                }
                $btn.prop('disabled', false).html(originalBtnHtml);
            });
    });

    // Street autocomplete (Photon fallback, or Google when available)
    window.__bindDockPlaces();
    window.__bindStreetAutocomplete();
});
