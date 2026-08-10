<?php

/**
 * Direct menu seed endpoint (works even if Laravel route cache is stale).
 *
 * Upload this file to the server public folder, then open:
 *   https://dockpizzamd.com/seed-menu.php?token=dock-pizza-seed
 *
 * Set SEED_MENU_TOKEN in .env on the server (recommended).
 */

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\DockPizzaMenuUpdateSeeder;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

header('Content-Type: application/json; charset=utf-8');

$expected = (string) env('SEED_MENU_TOKEN', '');
if ($expected === '') {
    $expected = 'dock-pizza-seed';
}

$provided = (string) ($_GET['token'] ?? '');

if ($provided === '' || ! hash_equals($expected, $provided)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or missing token. Set SEED_MENU_TOKEN in .env and pass ?token=...',
    ]);
    exit;
}

try {
    (new DockPizzaMenuUpdateSeeder)->run();

    $categories = Category::query()
        ->where('is_active', true)
        ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
        ->orderBy('sort_order')
        ->get()
        ->map(fn (Category $c) => [
            'name' => $c->name,
            'slug' => $c->slug,
            'products' => $c->products_count,
        ])
        ->values();

    echo json_encode([
        'success' => true,
        'message' => 'Dock Pizza menu seeded successfully.',
        'active_products' => Product::where('is_active', true)->count(),
        'categories' => $categories,
        'menu_url' => url('/menu'),
    ], JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Menu seed failed.',
        'error' => $e->getMessage(),
    ], JSON_PRETTY_PRINT);
}
