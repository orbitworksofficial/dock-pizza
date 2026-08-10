<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Faq;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class SystemSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Banners for Homepage Hero Slider
        $banners = [
            [
                'title' => 'Fresh off the dock',
                'subtitle' => 'Premium specialty pizzas, crispy sides, and fresh salads — made with dock seasoning and coastal flair.',
                'image' => 'assets/images/menu/dock-pizza-menu-premium.png',
                'link_url' => '/menu',
                'link_text' => 'Explore Our Menu',
                'badge_text' => 'Dock Special',
                'badge_color' => '#1B3A5C',
                'position' => 'hero',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Premium Pizzas & Salads',
                'subtitle' => 'From Supreme to Feta Feast — handcrafted pizzas and garden-fresh salads for every appetite.',
                'image' => 'assets/images/menu/dock-pizza-menu-salads-sides.png',
                'link_url' => '/menu#premium-pizzas',
                'link_text' => 'Order Premium Pizzas',
                'badge_text' => 'Fresh Daily',
                'badge_color' => '#E07B2D',
                'position' => 'hero',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $b) {
            Banner::create($b);
        }

        // 2. Seed Testimonials
        $testimonials = [
            [
                'name' => 'Michael Chang',
                'location' => 'Baltimore, MD',
                'content' => 'The Dock Pizza is out of this world! Incredible flavor, fresh crust, and it arrived hot and fresh. This is now my go-to pizza place.',
                'rating' => 5,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Sophia Martinez',
                'location' => 'Uptown Resident',
                'content' => 'Exceptional service and extremely fast delivery. The buffalo wings are nice and crispy, just the way I like them.',
                'rating' => 5,
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($testimonials as $t) {
            Testimonial::create($t);
        }

        // 3. Seed FAQs
        $faqs = [
            [
                'question' => 'Do you offer gluten-free options?',
                'answer' => 'Yes, we offer gluten-free pizza crusts for our 10" pizzas. Please note that while we take strict precautions, our kitchen is not a 100% gluten-free environment.',
                'category' => 'Dietary',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'question' => 'What is the estimated delivery time?',
                'answer' => 'Typically, delivery takes between 35 to 45 minutes depending on traffic and order volume. You can track your order status in real-time from your account.',
                'category' => 'Delivery',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($faqs as $f) {
            Faq::create($f);
        }

        // 4. Seed Global Settings
        $settings = [
            ['group' => 'general', 'key' => 'site_name', 'value' => 'Dock Pizza', 'type' => 'string'],
            ['group' => 'general', 'key' => 'contact_email', 'value' => 'info@dockpizza.com', 'type' => 'string'],
            ['group' => 'general', 'key' => 'contact_phone', 'value' => '443-203-6404', 'type' => 'string'],
            ['group' => 'general', 'key' => 'website', 'value' => 'https://www.dockpizzamd.com', 'type' => 'string'],
            ['group' => 'general', 'key' => 'social_facebook', 'value' => 'https://facebook.com/dockpizza', 'type' => 'string'],
            ['group' => 'general', 'key' => 'social_instagram', 'value' => 'https://instagram.com/dockpizza', 'type' => 'string'],
        ];

        foreach ($settings as $s) {
            Setting::create($s);
        }
    }
}
