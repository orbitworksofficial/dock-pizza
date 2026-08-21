<?php

/**
 * Hardcoded SEO fallbacks.
 *
 * Every value the front end renders resolves DB first, then this file. If the
 * database is unreachable or a field is blank, these still produce a complete
 * <head>. Nothing here may be empty.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Site-wide defaults
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'title' => 'Dock Pizza — Fresh off the dock',
        'description' => 'Hand-tossed pizza, subs and wings made fresh to order in Shady Side, Maryland. Delivery and pickup available daily.',
        'keywords' => 'pizza, delivery, Shady Side, Maryland, subs, wings, catering',
        'robots' => 'index, follow',
        'og_type' => 'website',
        'twitter_card' => 'summary_large_image',
        // A real image, never the homepage URL — social scrapers need a file.
        'og_image' => '/images/dock-pizza-social.jpg',
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-route fallbacks
    |--------------------------------------------------------------------------
    |
    | Keyed by page_key. 'noindex' marks routes that should never be exposed
    | in the admin list or the sitemap.
    |
    */

    'pages' => [
        '/' => [
            'name' => 'Homepage',
            'title' => 'Dock Pizza — Fresh off the dock | Shady Side, MD',
            'description' => 'Hand-tossed pizza made fresh to order in Shady Side, Maryland. Order delivery within 7 miles or pick up from our dock-side kitchen.',
        ],
        '/menu' => [
            'name' => 'Menu',
            'title' => 'Menu — Pizza, Subs & Wings | Dock Pizza',
            'description' => 'Browse the full Dock Pizza menu: hand-tossed pizzas, oven-baked subs, wings and sides. Order online for delivery or pickup.',
        ],
        '/catering' => [
            'name' => 'Catering',
            'title' => 'Catering & Event Pizza | Dock Pizza',
            'description' => 'Pizza catering for parties, offices and events around Shady Side and Anne Arundel County. Request a custom quote.',
        ],
        '/blog' => [
            'name' => 'Blog',
            'title' => 'Blog — Pizza, Recipes & Local News | Dock Pizza',
            'description' => 'Stories from the dock: pizza-making, local events around Shady Side, and what is coming out of our kitchen.',
        ],
        '/login' => [
            'name' => 'Sign In',
            'title' => 'Sign In | Dock Pizza',
            'description' => 'Sign in to your Dock Pizza account to track orders and reorder favourites.',
            'robots' => 'noindex, follow',
            'hidden' => true,
        ],
        '/register' => [
            'name' => 'Create Account',
            'title' => 'Create an Account | Dock Pizza',
            'description' => 'Create a Dock Pizza account for faster checkout and order history.',
            'robots' => 'noindex, follow',
            'hidden' => true,
        ],
        '/checkout' => [
            'name' => 'Checkout',
            'title' => 'Checkout | Dock Pizza',
            'description' => 'Complete your Dock Pizza order.',
            'robots' => 'noindex, nofollow',
            'hidden' => true,
        ],
        '/orders' => [
            'name' => 'Order History',
            'title' => 'Your Orders | Dock Pizza',
            'description' => 'View your past Dock Pizza orders.',
            'robots' => 'noindex, nofollow',
            'hidden' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Organization / NAP
    |--------------------------------------------------------------------------
    |
    | Repeated verbatim in both Organization and ProfessionalService, because
    | validators match on shape rather than type name.
    |
    */

    'organization' => [
        'name' => 'Dock Pizza',
        'legal_name' => 'Dock Pizza LLC',
        'description' => 'Hand-tossed pizza, subs and wings made fresh to order in Shady Side, Maryland.',
        'logo' => '/images/dock-pizza-logo.png',
        'email' => 'info@dockpizza.com',
        'telephone' => '+1-443-203-6404',
        'price_range' => '$$',
        'address' => [
            'street' => '1484 Snug Harbor Road',
            'locality' => 'Shady Side',
            'region' => 'MD',
            'postal_code' => '20764',
            'country' => 'US',
        ],
        'geo' => [
            'latitude' => 38.8411850,
            'longitude' => -76.5100040,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Social profiles (sameAs)
    |--------------------------------------------------------------------------
    |
    | Canonical profile URLs only. Tracking params are stripped on save by
    | SocialUrlNormalizer; sameAs matching is URL-exact.
    |
    */

    'social' => [
        'facebook' => 'https://www.facebook.com/dockpizza',
        'instagram' => 'https://www.instagram.com/dockpizza',
    ],

    /*
    |--------------------------------------------------------------------------
    | Opening hours (schema.org openingHoursSpecification)
    |--------------------------------------------------------------------------
    */

    'opening_hours' => [
        ['days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'], 'opens' => '10:30', 'closes' => '22:00'],
        ['days' => ['Friday', 'Saturday'], 'opens' => '10:30', 'closes' => '00:00'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation limits
    |--------------------------------------------------------------------------
    */

    'limits' => [
        'title' => 70,
        'description' => 200,
        'faq_question' => 300,
        'faq_answer' => 2000,
        // Advisory counters in the UI — not enforced.
        'title_warn' => 60,
        'description_warn' => 160,
    ],
];
