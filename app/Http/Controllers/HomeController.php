<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Testimonial;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $heroBanners = Banner::where('position', 'hero')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $categories = Category::where('is_active', true)
            ->whereNotIn('slug', ['salad-add-ons'])
            ->orderBy('sort_order')
            ->get();

        $featuredProducts = Product::with(['primaryImage', 'variations'])
            ->where('is_featured', true)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->take(6)
            ->get();

        $testimonials = Testimonial::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $stores = \App\Models\Store::query()
            ->where('is_active', true)
            ->where('slug', '!=', 'dock-pizza-annapolis')
            ->orderByRaw("CASE WHEN slug = 'dock-pizza-shady-side' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        return view('home', compact('heroBanners', 'categories', 'featuredProducts', 'testimonials', 'stores'));
    }
}
