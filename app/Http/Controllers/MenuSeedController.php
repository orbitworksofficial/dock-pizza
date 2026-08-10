<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\DockPizzaMenuUpdateSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class MenuSeedController extends Controller
{
    /**
     * Seed / sync the Dock Pizza printed menu via browser.
     * Visit: /seed-menu?token=YOUR_TOKEN
     */
    public function seed(Request $request): JsonResponse
    {
        if (! $this->tokenIsValid($request)) {
            abort(403, 'Invalid or missing token. Set SEED_MENU_TOKEN in .env');
        }

        try {
            Artisan::call('db:seed', [
                '--class' => DockPizzaMenuUpdateSeeder::class,
                '--force' => true,
            ]);

            $output = trim(Artisan::output());

            $categories = Category::query()
                ->where('is_active', true)
                ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Category $c) => [
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'products' => $c->products_count,
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Dock Pizza menu seeded successfully. Open /menu to order.',
                'artisan_output' => $output ?: 'done',
                'active_products' => Product::where('is_active', true)->count(),
                'categories' => $categories,
                'menu_url' => url('/menu'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Menu seed failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function tokenIsValid(Request $request): bool
    {
        $token = (string) env('SEED_MENU_TOKEN', '');

        // Fallback default so the route works after deploy if .env token was not set yet
        if ($token === '') {
            $token = 'dock-pizza-seed';
        }

        return hash_equals($token, (string) $request->query('token', ''));
    }
}
