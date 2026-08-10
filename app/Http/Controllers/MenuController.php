<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index()
    {
        $hiddenCategorySlugs = ['calzones', 'calzone', 'pastas', 'pasta'];

        $categories = Category::with(['products' => function ($q) {
            $q->where('is_active', true)->orderBy('sort_order');
        }, 'products.primaryImage', 'products.images', 'products.variations' => function ($q) {
            $q->where('is_active', true)->orderBy('sort_order');
        }])
            ->where('is_active', true)
            ->whereNotIn('slug', $hiddenCategorySlugs)
            ->orderBy('sort_order')
            ->get();

        $hasLocation = session()->has('order_location');

        return view('menu.index', compact('categories', 'hasLocation'));
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['variations', 'toppings.category', 'primaryImage', 'images']);
        
        // Group toppings by category for the customizer UI
        $toppingsByCategory = $product->toppings->groupBy(function ($topping) {
            return $topping->category->name;
        });

        return response()->json([
            'product' => $product,
            'toppingsByCategory' => $toppingsByCategory,
        ]);
    }
}
