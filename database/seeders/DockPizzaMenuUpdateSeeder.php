<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\Setting;
use App\Models\Store;
use App\Models\StoreHour;
use App\Models\Topping;
use App\Models\ToppingCategory;
use Illuminate\Database\Seeder;

/**
 * Syncs every item from the printed Dock Pizza menus into the orderable catalog.
 *
 * Source cards:
 * - Premium pizzas / single-topping pricing / toppings banner
 * - Salads / sides & apps / dessert / drinks + store contact & hours
 */
class DockPizzaMenuUpdateSeeder extends Seeder
{
    public function run(): void
    {
        $this->syncStoreInfo();
        $this->syncCategories();
        $toppingModels = $this->syncToppings();
        $this->syncProducts($toppingModels);
    }

    private function syncStoreInfo(): void
    {
        $store = Store::query()->where('slug', 'dock-pizza-shady-side')->first();

        if ($store) {
            $store->update([
                'address' => '1484 Snug Harbor Road',
                'city' => 'Shady Side',
                'state' => 'MD',
                'zip_code' => '20764',
                'phone' => '443-203-6404',
                'email' => 'info@dockpizza.com',
            ]);

            // Sun–Thu 10:30am–10pm | Fri–Sat 10:30am–12am
            $hours = [
                0 => ['10:30:00', '22:00:00'], // Sunday
                1 => ['10:30:00', '22:00:00'], // Monday
                2 => ['10:30:00', '22:00:00'], // Tuesday
                3 => ['10:30:00', '22:00:00'], // Wednesday
                4 => ['10:30:00', '22:00:00'], // Thursday
                5 => ['10:30:00', '00:00:00'], // Friday → midnight
                6 => ['10:30:00', '00:00:00'], // Saturday → midnight
            ];

            foreach ($hours as $day => [$open, $close]) {
                StoreHour::updateOrCreate(
                    ['store_id' => $store->id, 'day_of_week' => $day],
                    [
                        'open_time' => $open,
                        'close_time' => $close,
                        'is_closed' => false,
                    ]
                );
            }
        }

        Setting::updateOrCreate(
            ['key' => 'contact_phone'],
            ['group' => 'general', 'value' => '443-203-6404', 'type' => 'string']
        );
        Setting::updateOrCreate(
            ['key' => 'site_name'],
            ['group' => 'general', 'value' => 'Dock Pizza', 'type' => 'string']
        );
        Setting::updateOrCreate(
            ['key' => 'website'],
            ['group' => 'general', 'value' => 'https://www.dockpizzamd.com', 'type' => 'string']
        );
    }

    private function syncCategories(): array
    {
        $categories = [
            [
                'slug' => 'premium-pizzas',
                'name' => 'Premium Pizzas',
                'description' => 'Fresh off the dock — signature specialty pizzas with premium toppings.',
                'icon' => 'anchor',
                'image' => 'assets/images/menu/dock-pizza.png',
                'color' => '#1B3A5C',
                'sort_order' => 1,
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'single-topping',
                'name' => 'Single Topping',
                'description' => 'Build your perfect pizza — one topping on our hand-tossed crust. Small 10" (6 slices), Medium 12", Large 14", X-Large 16" (8 slices).',
                'icon' => 'pizza-slice',
                'image' => 'assets/images/menu/single-topping-pizza.png',
                'color' => '#E07B2D',
                'sort_order' => 2,
                'is_featured' => false,
                'is_active' => true,
            ],
            [
                'slug' => 'salads',
                'name' => 'Salads',
                'description' => 'Fresh salads. Choice of 2 dressings: Ranch, Blue Cheese, Caesar, Fat-Free Italian, House Italian, Honey Mustard. Calories are approximate.',
                'icon' => 'leaf',
                'image' => 'assets/images/menu/greek-salad.png',
                'color' => '#16A34A',
                'sort_order' => 3,
                'is_featured' => false,
                'is_active' => true,
            ],
            [
                'slug' => 'salad-add-ons',
                'name' => 'Salad Add-Ons',
                'description' => 'Extra proteins and toppings for your salad.',
                'icon' => 'plus',
                'color' => '#16A34A',
                'sort_order' => 4,
                'is_featured' => false,
                'is_active' => true,
            ],
            [
                'slug' => 'sides-apps',
                'name' => 'Sides & Apps',
                'description' => 'Crispy fries, sticks, shrimp, and more from our printed menu.',
                'icon' => 'utensils',
                'image' => 'assets/images/menu/mozzarella-sticks.png',
                'color' => '#D97706',
                'sort_order' => 5,
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'drinks',
                'name' => 'Drinks',
                'description' => 'Soda can, 20oz fountain, or 2 liter — ice-cold beverages.',
                'icon' => 'glass-water',
                'image' => 'assets/images/menu/drinks-category.png',
                'color' => '#2563EB',
                'sort_order' => 6,
                'is_featured' => false,
                'is_active' => true,
            ],
            [
                'slug' => 'desserts',
                'name' => 'Desserts',
                'description' => 'Sweet treats from our printed menu.',
                'icon' => 'ice-cream',
                'image' => 'assets/images/menu/cheesecake.png',
                'color' => '#BE185D',
                'sort_order' => 7,
                'is_featured' => false,
                'is_active' => true,
            ],
        ];

        $categoryMap = [];
        foreach ($categories as $cat) {
            $model = Category::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
            $categoryMap[$cat['slug']] = $model->id;
        }

        return $categoryMap;
    }

    /**
     * Printed toppings banner — Regular + Premium.
     *
     * @return array<string, Topping>
     */
    private function syncToppings(): array
    {
        $toppingCategories = [
            [
                'slug' => 'regular-toppings',
                'name' => 'Regular Toppings',
                'sort_order' => 1,
                'is_active' => true,
                'max_selections' => null,
                'is_required' => false,
            ],
            [
                'slug' => 'premium-toppings',
                'name' => 'Premium Toppings',
                'sort_order' => 2,
                'is_active' => true,
                'max_selections' => null,
                'is_required' => false,
            ],
        ];

        $tcMap = [];
        foreach ($toppingCategories as $tc) {
            $tcMap[$tc['slug']] = ToppingCategory::updateOrCreate(
                ['slug' => $tc['slug']],
                $tc
            );
        }

        // Hide legacy topping groups not on the printed banner
        ToppingCategory::whereNotIn('slug', array_keys($tcMap))->update(['is_active' => false]);

        // Extra topping prices (not listed on card — keep reasonable add-on pricing)
        $regularPrice = 1.75;
        $premiumPrice = 2.75;

        $toppings = [
            // Regular Toppings (printed banner)
            ['tc' => 'regular-toppings', 'name' => 'Pizza Cheese', 'slug' => 'pizza-cheese', 'price' => 1.99, 'is_premium' => false, 'sort_order' => 1],
            ['tc' => 'regular-toppings', 'name' => 'Pepperoni', 'slug' => 'pepperoni', 'price' => $regularPrice, 'is_premium' => false, 'sort_order' => 2],
            ['tc' => 'regular-toppings', 'name' => 'Italian Sausage', 'slug' => 'italian-sausage', 'price' => $regularPrice, 'is_premium' => false, 'sort_order' => 3],
            ['tc' => 'regular-toppings', 'name' => 'Ground Beef', 'slug' => 'ground-beef', 'price' => $regularPrice, 'is_premium' => false, 'sort_order' => 4],
            ['tc' => 'regular-toppings', 'name' => 'Chicken Breast', 'slug' => 'chicken-breast', 'price' => $regularPrice, 'is_premium' => false, 'sort_order' => 5],
            ['tc' => 'regular-toppings', 'name' => 'Mushrooms', 'slug' => 'mushrooms', 'price' => $regularPrice, 'is_premium' => false, 'sort_order' => 6],
            ['tc' => 'regular-toppings', 'name' => 'Green Peppers', 'slug' => 'green-peppers', 'price' => $regularPrice, 'is_premium' => false, 'sort_order' => 7],
            ['tc' => 'regular-toppings', 'name' => 'Onions', 'slug' => 'onions', 'price' => $regularPrice, 'is_premium' => false, 'sort_order' => 8],
            ['tc' => 'regular-toppings', 'name' => 'Black Olives', 'slug' => 'black-olives', 'price' => $regularPrice, 'is_premium' => false, 'sort_order' => 9],
            ['tc' => 'regular-toppings', 'name' => 'Ham', 'slug' => 'ham', 'price' => $regularPrice, 'is_premium' => false, 'sort_order' => 10],
            ['tc' => 'regular-toppings', 'name' => 'Tomatoes', 'slug' => 'tomatoes', 'price' => $regularPrice, 'is_premium' => false, 'sort_order' => 11],
            ['tc' => 'regular-toppings', 'name' => 'Banana Peppers', 'slug' => 'banana-peppers', 'price' => $regularPrice, 'is_premium' => false, 'sort_order' => 12],
            ['tc' => 'regular-toppings', 'name' => 'Broccoli', 'slug' => 'broccoli', 'price' => $regularPrice, 'is_premium' => false, 'sort_order' => 13],
            ['tc' => 'regular-toppings', 'name' => 'Fresh Garlic', 'slug' => 'fresh-garlic', 'price' => $regularPrice, 'is_premium' => false, 'sort_order' => 14],
            ['tc' => 'regular-toppings', 'name' => 'Green Olives', 'slug' => 'green-olives', 'price' => $regularPrice, 'is_premium' => false, 'sort_order' => 15],
            ['tc' => 'regular-toppings', 'name' => 'Jalapeño Peppers', 'slug' => 'jalapeno-peppers', 'price' => $regularPrice, 'is_premium' => false, 'sort_order' => 16],
            ['tc' => 'regular-toppings', 'name' => 'Pineapple', 'slug' => 'pineapple', 'price' => $regularPrice, 'is_premium' => false, 'sort_order' => 17],
            ['tc' => 'regular-toppings', 'name' => 'Spinach', 'slug' => 'spinach', 'price' => $regularPrice, 'is_premium' => false, 'sort_order' => 18],
            ['tc' => 'regular-toppings', 'name' => 'Feta Cheese', 'slug' => 'feta-cheese', 'price' => 2.49, 'is_premium' => false, 'sort_order' => 19],

            // Premium Toppings (printed banner)
            ['tc' => 'premium-toppings', 'name' => 'Shrimp', 'slug' => 'shrimp', 'price' => $premiumPrice, 'is_premium' => true, 'sort_order' => 1],
            ['tc' => 'premium-toppings', 'name' => 'Chicken Steak', 'slug' => 'chicken-steak', 'price' => $premiumPrice, 'is_premium' => true, 'sort_order' => 2],
            ['tc' => 'premium-toppings', 'name' => 'Sirloin Steak', 'slug' => 'sirloin-steak', 'price' => $premiumPrice, 'is_premium' => true, 'sort_order' => 3],
            ['tc' => 'premium-toppings', 'name' => 'Meatballs', 'slug' => 'meatballs', 'price' => $premiumPrice, 'is_premium' => true, 'sort_order' => 4],
            ['tc' => 'premium-toppings', 'name' => 'Crab Meat', 'slug' => 'crab-meat', 'price' => $premiumPrice, 'is_premium' => true, 'sort_order' => 5],
        ];

        $toppingModels = [];
        $keepSlugs = [];

        foreach ($toppings as $top) {
            $keepSlugs[] = $top['slug'];
            $toppingModels[$top['slug']] = Topping::updateOrCreate(
                ['slug' => $top['slug']],
                [
                    'topping_category_id' => $tcMap[$top['tc']]->id,
                    'name' => $top['name'],
                    'price' => $top['price'],
                    'is_premium' => $top['is_premium'],
                    'sort_order' => $top['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        Topping::whereNotIn('slug', $keepSlugs)->update(['is_active' => false]);

        return $toppingModels;
    }

    /**
     * @param  array<string, Topping>  $toppingModels
     */
    private function syncProducts(array $toppingModels): void
    {
        $categoryMap = Category::query()->pluck('id', 'slug')->all();

        // Printed premium pricing table
        $premiumSizes = [
            ['name' => 'Small 10"', 'slug' => 'small', 'price' => 17.99],
            ['name' => 'Medium 12"', 'slug' => 'medium', 'price' => 19.99],
            ['name' => 'Large 14"', 'slug' => 'large', 'price' => 20.99, 'is_default' => true],
            ['name' => 'X-Large 16"', 'slug' => 'x-large', 'price' => 22.99],
        ];

        // Printed single-topping pricing table
        $singleSizes = [
            ['name' => 'Small 10"', 'slug' => 'small', 'price' => 8.99],
            ['name' => 'Medium 12"', 'slug' => 'medium', 'price' => 10.99],
            ['name' => 'Large 14"', 'slug' => 'large', 'price' => 14.99, 'is_default' => true],
            ['name' => 'X-Large 16"', 'slug' => 'x-large', 'price' => 18.99],
        ];

        $products = [
            // ── Premium Pizzas (card 1) ──────────────────────────────
            // Calorie callouts on card use SMALL (10") and LARGE (16" = X-Large)
            [
                'cat' => 'premium-pizzas',
                'name' => 'Supreme Pizza',
                'slug' => 'supreme-pizza',
                'description' => 'Pepperoni, sausage, ground beef, bacon, green peppers, mushroom, onion, olive, jalapeño.',
                'short_description' => 'The ultimate loaded supreme.',
                'base_price' => 17.99,
                'calories' => 1830,
                'nutritional_info' => ['small_10' => 820, 'x_large_16' => 1830],
                'is_customizable' => true,
                'is_featured' => true,
                'sort_order' => 1,
                'image' => 'assets/images/menu/supreme-pizza.png',
                'variations' => $premiumSizes,
            ],
            [
                'cat' => 'premium-pizzas',
                'name' => 'Dock Pizza',
                'slug' => 'dock-pizza',
                'description' => 'Crispy chicken, green pepper, onion, tomato, olive, dock seasoning.',
                'short_description' => 'Our signature house specialty.',
                'base_price' => 17.99,
                'calories' => 1680,
                'nutritional_info' => ['small_10' => 760, 'x_large_16' => 1680],
                'is_customizable' => true,
                'is_featured' => true,
                'sort_order' => 2,
                'image' => 'assets/images/menu/dock-pizza.png',
                'gallery' => [
                    'assets/images/menu/dock-pizza-cheese-pull.png',
                    'assets/images/menu/dock-pizza-top-view.png',
                ],
                'variations' => $premiumSizes,
            ],
            [
                'cat' => 'premium-pizzas',
                'name' => 'Polo Ranch Pizza',
                'slug' => 'polo-ranch-pizza',
                'description' => 'Red onion, BBQ sauce, chicken.',
                'short_description' => 'Tangy BBQ chicken favorite.',
                'base_price' => 17.99,
                'calories' => 1720,
                'nutritional_info' => ['small_10' => 780, 'x_large_16' => 1720],
                'is_customizable' => true,
                'is_featured' => false,
                'sort_order' => 3,
                'image' => 'assets/images/menu/polo-ranch-pizza.png',
                'gallery' => [
                    'assets/images/menu/polo-ranch-pizza-cheese-pull.png',
                    'assets/images/menu/polo-ranch-pizza-top-view.png',
                ],
                'variations' => $premiumSizes,
            ],
            [
                'cat' => 'premium-pizzas',
                'name' => 'Mega Meat',
                'slug' => 'mega-meat',
                'description' => 'Pepperoni, bacon, sausage, ground beef, ham, salami.',
                'short_description' => 'For serious meat lovers.',
                'base_price' => 17.99,
                'calories' => 2010,
                'nutritional_info' => ['small_10' => 900, 'x_large_16' => 2010],
                'is_customizable' => true,
                'is_featured' => true,
                'sort_order' => 4,
                'image' => 'assets/images/menu/mega-meat.png',
                'gallery' => [
                    'assets/images/menu/mega-meat-cheese-pull.png',
                    'assets/images/menu/mega-meat-top-view.png',
                ],
                'variations' => $premiumSizes,
            ],
            [
                'cat' => 'premium-pizzas',
                'name' => 'Feta Feast',
                'slug' => 'feta-feast',
                'description' => 'Tzatziki sauce, gyro meat, red onion, black olives, tomato, spinach, feta cheese.',
                'short_description' => 'Mediterranean-inspired delight.',
                'base_price' => 17.99,
                'calories' => 1630,
                'nutritional_info' => ['small_10' => 740, 'x_large_16' => 1630],
                'is_customizable' => true,
                'is_featured' => false,
                'sort_order' => 5,
                'image' => 'assets/images/menu/feta-feast.png',
                'gallery' => [
                    'assets/images/menu/feta-feast-gallery.png',
                ],
                'variations' => $premiumSizes,
            ],

            // ── Single Topping (card 1) ──────────────────────────────
            [
                'cat' => 'single-topping',
                'name' => 'Single Topping Pizza',
                'slug' => 'single-topping-pizza',
                'description' => 'Build your own pizza with one topping of your choice on our hand-tossed crust. Choose from regular or premium toppings.',
                'short_description' => 'Your choice, one topping.',
                'base_price' => 8.99,
                'is_customizable' => true,
                'is_featured' => false,
                'sort_order' => 1,
                'image' => 'assets/images/menu/single-topping-pizza.png',
                'variations' => $singleSizes,
            ],

            // ── Salads (card 2) ──────────────────────────────────────
            [
                'cat' => 'salads',
                'name' => 'Garden Salad',
                'slug' => 'garden-salad',
                'description' => 'Fresh mixed greens. Choice of 2 dressings: Ranch (220 CAL), Blue Cheese (230 CAL), Caesar (210 CAL), Fat-Free Italian (15 CAL), House Italian (160 CAL), or Honey Mustard (190 CAL).',
                'short_description' => 'Classic garden fresh.',
                'base_price' => 8.99,
                'calories' => 120,
                'sort_order' => 1,
                'image' => 'assets/images/menu/garden-salad.png',
            ],
            [
                'cat' => 'salads',
                'name' => 'Caesar Salad',
                'slug' => 'caesar-salad',
                'description' => 'Crisp romaine, parmesan, croutons, and Caesar dressing. Choice of 2 dressings available.',
                'short_description' => 'Timeless Caesar classic.',
                'base_price' => 7.99,
                'calories' => 310,
                'sort_order' => 2,
                'image' => 'assets/images/menu/caesar-salad.png',
            ],
            [
                'cat' => 'salads',
                'name' => 'Greek Salad',
                'slug' => 'greek-salad',
                'description' => 'Romaine, feta, olives, red onion, tomato, and cucumber. Choice of 2 dressings.',
                'short_description' => 'Mediterranean flavors.',
                'base_price' => 10.99,
                'calories' => 330,
                'sort_order' => 3,
                'image' => 'assets/images/menu/greek-salad.png',
                'gallery' => [
                    'assets/images/menu/greek-salad-rustic.png',
                    'assets/images/menu/greek-salad-top-view.png',
                ],
            ],
            [
                'cat' => 'salads',
                'name' => 'Chef Salad',
                'slug' => 'chef-salad',
                'description' => 'Mixed greens topped with meats, cheese, egg, and fresh vegetables. Choice of 2 dressings.',
                'short_description' => 'Hearty and satisfying.',
                'base_price' => 11.99,
                'calories' => 400,
                'sort_order' => 4,
                'image' => 'assets/images/menu/chef-salad.png',
                'gallery' => [
                    'assets/images/menu/chef-salad-rustic.png',
                    'assets/images/menu/chef-salad-top-view.png',
                ],
            ],
            [
                'cat' => 'salads',
                'name' => 'Cobb Salad',
                'slug' => 'bolis-cobb-salad',
                'description' => 'Mixed greens, grilled chicken, bacon, hard-boiled egg, tomato, cucumber, and cheese. Choice of 2 dressings.',
                'short_description' => 'Loaded Cobb-style salad.',
                'base_price' => 13.99,
                'calories' => 520,
                'is_featured' => true,
                'sort_order' => 5,
                'image' => 'assets/images/menu/bolis-cobb-salad.png',
                'gallery' => [
                    'assets/images/menu/bolis-cobb-salad-rustic.png',
                    'assets/images/menu/bolis-cobb-salad-top-view.png',
                ],
            ],

            // ── Salad Add-Ons (card 2) ───────────────────────────────
            [
                'cat' => 'salad-add-ons',
                'name' => 'Grilled Chicken Add-On',
                'slug' => 'grilled-chicken-add-on',
                'description' => 'Add grilled chicken to any salad.',
                'short_description' => 'Salad protein add-on.',
                'base_price' => 3.99,
                'calories' => 150,
                'sort_order' => 1,
                'image' => 'assets/images/menu/grilled-chicken-add-on.png',
            ],
            [
                'cat' => 'salad-add-ons',
                'name' => 'Crispy Chicken Add-On',
                'slug' => 'crispy-chicken-add-on',
                'description' => 'Add crispy chicken to any salad.',
                'short_description' => 'Salad protein add-on.',
                'base_price' => 3.49,
                'calories' => 240,
                'sort_order' => 2,
                'image' => 'assets/images/menu/crispy-chicken-add-on.png',
            ],
            [
                'cat' => 'salad-add-ons',
                'name' => 'Grilled Shrimp Add-On',
                'slug' => 'grilled-shrimp-add-on',
                'description' => 'Add grilled shrimp to any salad.',
                'short_description' => 'Salad seafood add-on.',
                'base_price' => 4.49,
                'calories' => 80,
                'sort_order' => 3,
                'image' => 'assets/images/menu/grilled-shrimp-add-on.png',
            ],
            [
                'cat' => 'salad-add-ons',
                'name' => 'Boiled Egg Add-On',
                'slug' => 'boiled-egg-add-on',
                'description' => 'Add a boiled egg to any salad.',
                'short_description' => 'Salad add-on.',
                'base_price' => 0.99,
                'calories' => 70,
                'sort_order' => 4,
                'image' => 'assets/images/menu/boiled-egg-add-on.png',
            ],
            [
                'cat' => 'salad-add-ons',
                'name' => 'Gyro Meat Add-On',
                'slug' => 'gyro-meat-add-on',
                'description' => 'Add gyro meat to any salad.',
                'short_description' => 'Salad protein add-on.',
                'base_price' => 4.49,
                'calories' => 180,
                'sort_order' => 5,
                'image' => 'assets/images/menu/gyro-meat-add-on.png',
            ],

            // ── Sides & Apps (card 2) ────────────────────────────────
            [
                'cat' => 'sides-apps',
                'name' => 'Crispy French Fries',
                'slug' => 'french-fries',
                'description' => 'Golden crispy french fries.',
                'short_description' => 'Crispy golden fries.',
                'base_price' => 3.99,
                'calories' => 320,
                'nutritional_info' => ['small' => 320, 'large' => 620],
                'sort_order' => 1,
                'image' => 'assets/images/menu/french-fries-small.png',
                'gallery' => [
                    'assets/images/menu/french-fries-small-gallery.png',
                    'assets/images/menu/french-fries-large.png',
                    'assets/images/menu/french-fries-large-2.png',
                    'assets/images/menu/french-fries-large-3.png',
                ],
                'variations' => [
                    ['name' => 'Small', 'slug' => 'small', 'price' => 3.99, 'is_default' => true],
                    ['name' => 'Large', 'slug' => 'large', 'price' => 5.99],
                ],
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Western Fries',
                'slug' => 'western-fries',
                'description' => 'Seasoned western-style fries.',
                'short_description' => 'Crispy western fries.',
                'base_price' => 3.99,
                'calories' => 370,
                'nutritional_info' => ['small' => 370, 'large' => 740],
                'sort_order' => 2,
                'image' => 'assets/images/menu/western-fries-small.png',
                'gallery' => [
                    'assets/images/menu/western-fries.png',
                ],
                'variations' => [
                    ['name' => 'Small', 'slug' => 'small', 'price' => 3.99, 'is_default' => true],
                    ['name' => 'Large', 'slug' => 'large', 'price' => 5.99],
                ],
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Crispy French Fries & Cheddar Cheese Sauce',
                'slug' => 'french-fries-cheddar',
                'description' => 'Crispy french fries served with cheddar cheese sauce.',
                'short_description' => 'Fries with cheese sauce.',
                'base_price' => 6.99,
                'calories' => 590,
                'sort_order' => 3,
                'image' => 'assets/images/menu/french-fries-cheddar.png',
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Western Fries & Cheddar Cheese Sauce',
                'slug' => 'western-fries-cheddar',
                'description' => 'Western fries served with cheddar cheese sauce.',
                'short_description' => 'Western fries with cheese.',
                'base_price' => 6.99,
                'calories' => 800,
                'sort_order' => 4,
                'image' => 'assets/images/menu/western-fries-cheddar.png',
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Mozzarella Sticks (6 Pcs)',
                'slug' => 'mozzarella-sticks',
                'description' => 'Six golden-fried mozzarella sticks served with marinara sauce.',
                'short_description' => 'Cheesy and crispy.',
                'base_price' => 6.99,
                'calories' => 450,
                'is_featured' => true,
                'sort_order' => 5,
                'image' => 'assets/images/menu/mozzarella-sticks.png',
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Onion Rings',
                'slug' => 'onion-rings',
                'description' => 'Crispy fried onion rings.',
                'short_description' => 'Crispy onion rings.',
                'base_price' => 5.99,
                'calories' => 400,
                'sort_order' => 6,
                'image' => 'assets/images/menu/onion-rings.png',
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Jalapeño Poppers (6 Pcs)',
                'slug' => 'jalapeno-poppers',
                'description' => 'Six cream cheese stuffed jalapeños, breaded and fried.',
                'short_description' => 'Spicy and creamy.',
                'base_price' => 5.99,
                'calories' => 420,
                'sort_order' => 7,
                'image' => 'assets/images/menu/jalapeno-poppers.png',
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Garlic Bread w/ Cheese',
                'slug' => 'garlic-bread-cheese',
                'description' => 'Garlic bread topped with melted cheese.',
                'short_description' => 'Cheesy garlic bread.',
                'base_price' => 5.49,
                'calories' => 380,
                'sort_order' => 8,
                'image' => 'assets/images/menu/garlic-cheese-bread.png',
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Bread Sticks w/ Cheese',
                'slug' => 'bread-sticks-cheese',
                'description' => 'Warm bread sticks topped with melted cheese.',
                'short_description' => 'Cheesy bread sticks.',
                'base_price' => 8.99,
                'calories' => 510,
                'sort_order' => 9,
                'image' => 'assets/images/menu/breadsticks.png',
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Chicken Strips w/ Fries',
                'slug' => 'chicken-strips-with-fries',
                'description' => 'Crispy chicken strips served with golden fries.',
                'short_description' => 'Chicken strips combo.',
                'base_price' => 8.99,
                'calories' => 620,
                'nutritional_info' => ['3_pcs' => 620, '5_pcs' => 860],
                'sort_order' => 10,
                'image' => 'assets/images/menu/chicken-strips-with-fries-3-pcs.png',
                'gallery' => [
                    'assets/images/menu/chicken-strips-with-fries.png',
                ],
                'variations' => [
                    ['name' => '3 Pcs w/ Fries', 'slug' => '3-pcs', 'price' => 8.99, 'is_default' => true],
                    ['name' => '5 Pcs w/ Fries', 'slug' => '5-pcs', 'price' => 9.99],
                ],
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Butterfly Shrimp w/ Fries',
                'slug' => 'butterfly-shrimp',
                'description' => 'Breaded butterfly shrimp fried golden, served with fries and cocktail sauce.',
                'short_description' => 'Crispy butterfly shrimp.',
                'base_price' => 12.99,
                'calories' => 700,
                'sort_order' => 11,
                'image' => 'assets/images/menu/butterfly-shrimp.png',
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Plain Potato Chips',
                'slug' => 'plain-potato-chips',
                'description' => 'Classic plain potato chips.',
                'short_description' => 'Crispy chips.',
                'base_price' => 0.89,
                'calories' => 150,
                'sort_order' => 12,
                'image' => 'assets/images/menu/plain-potato-chips.png',
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'BBQ Potato Chips',
                'slug' => 'bbq-potato-chips',
                'description' => 'BBQ flavored potato chips.',
                'short_description' => 'BBQ chips.',
                'base_price' => 0.89,
                'calories' => 150,
                'sort_order' => 13,
                'image' => 'assets/images/menu/bbq-potato-chips.png',
            ],

            // ── Drinks (card 2) — separate Can / 20oz / 2 Liter ──────
            [
                'cat' => 'drinks',
                'name' => 'Soda Can',
                'slug' => 'soda-can',
                'description' => 'Ice-cold canned soda.',
                'short_description' => 'Canned soda.',
                'base_price' => 1.49,
                'is_customizable' => false,
                'sort_order' => 1,
                'image' => 'assets/images/menu/drink-can.png',
                'variations' => [
                    ['name' => 'Can', 'slug' => 'can', 'price' => 1.49, 'is_default' => true],
                ],
            ],
            [
                'cat' => 'drinks',
                'name' => '20oz Soda',
                'slug' => 'soda-20oz',
                'description' => 'Refreshing 20oz fountain soda with ice.',
                'short_description' => '20oz fountain soda.',
                'base_price' => 2.99,
                'is_customizable' => false,
                'sort_order' => 2,
                'image' => 'assets/images/menu/drink-20oz.png',
                'variations' => [
                    ['name' => '20oz', 'slug' => '20oz', 'price' => 2.99, 'is_default' => true],
                ],
            ],
            [
                'cat' => 'drinks',
                'name' => '2 Liter Soda',
                'slug' => 'soda-2-liter',
                'description' => 'Ice-cold 2 liter soda bottle.',
                'short_description' => '2 liter bottle.',
                'base_price' => 3.99,
                'is_customizable' => false,
                'sort_order' => 3,
                'image' => 'assets/images/menu/drink-2-liter.png',
                'variations' => [
                    ['name' => '2 Liter', 'slug' => '2-liter', 'price' => 3.99, 'is_default' => true],
                ],
            ],

            // ── Desserts (card 2) ────────────────────────────────────
            [
                'cat' => 'desserts',
                'name' => "Ben & Jerry's Ice Cream Pint",
                'slug' => 'ben-jerrys-ice-cream-pint',
                'description' => "A full pint of Ben & Jerry's ice cream.",
                'short_description' => "Ben & Jerry's pint.",
                'base_price' => 7.99,
                'is_featured' => true,
                'sort_order' => 1,
                'image' => 'assets/images/menu/ben-jerrys-ice-cream-pint.png',
            ],
            [
                'cat' => 'desserts',
                'name' => 'Cheesecake',
                'slug' => 'cheesecake',
                'description' => 'Creamy cheesecake slice.',
                'short_description' => 'Classic cheesecake.',
                'base_price' => 3.99,
                'sort_order' => 2,
                'image' => 'assets/images/menu/cheesecake.png',
            ],
            [
                'cat' => 'desserts',
                'name' => 'Carrot Cake',
                'slug' => 'carrot-cake',
                'description' => 'Moist carrot cake slice.',
                'short_description' => 'Classic carrot cake.',
                'base_price' => 3.99,
                'sort_order' => 3,
                'image' => 'assets/images/menu/carrot-cake.png',
            ],
            [
                'cat' => 'desserts',
                'name' => 'Funnel Cake',
                'slug' => 'funnel-cake',
                'description' => 'Crispy funnel cake, dusted and ready to enjoy.',
                'short_description' => 'Crispy funnel cake.',
                'base_price' => 7.99,
                'is_featured' => true,
                'sort_order' => 4,
                'image' => 'assets/images/menu/funnel-cake.png',
            ],
        ];

        // Hide items that are not on the printed menus
        $printedSlugs = collect($products)->pluck('slug')->all();
        Product::whereNotIn('slug', $printedSlugs)->update(['is_active' => false]);

        // Deactivate legacy bottled-water / combined soda if present
        Product::whereIn('slug', ['bottled-water', 'soda'])->update(['is_active' => false]);

        $stores = Store::all();
        $customizableCats = ['premium-pizzas', 'single-topping'];

        foreach ($products as $prod) {
            $product = Product::updateOrCreate(
                ['slug' => $prod['slug']],
                [
                    'category_id' => $categoryMap[$prod['cat']],
                    'name' => $prod['name'],
                    'description' => $prod['description'],
                    'short_description' => $prod['short_description'],
                    'base_price' => $prod['base_price'],
                    'calories' => $prod['calories'] ?? null,
                    'nutritional_info' => $prod['nutritional_info'] ?? null,
                    'is_featured' => $prod['is_featured'] ?? false,
                    'is_customizable' => $prod['is_customizable'] ?? false,
                    'sort_order' => $prod['sort_order'] ?? 0,
                    'is_active' => true,
                ]
            );

            ProductImage::updateOrCreate(
                ['product_id' => $product->id, 'is_primary' => true],
                [
                    'path' => $prod['image'] ?? "assets/images/menu/{$product->slug}.jpg",
                    'alt_text' => $product->name,
                    'sort_order' => 1,
                ]
            );

            if (!empty($prod['gallery'])) {
                foreach ($prod['gallery'] as $index => $galleryPath) {
                    ProductImage::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'path' => $galleryPath,
                        ],
                        [
                            'alt_text' => $product->name,
                            'is_primary' => false,
                            'sort_order' => $index + 2,
                        ]
                    );
                }
            }

            if (!empty($prod['variations'])) {
                $keepSlugs = [];
                foreach ($prod['variations'] as $v) {
                    $keepSlugs[] = $v['slug'];
                    ProductVariation::updateOrCreate(
                        ['product_id' => $product->id, 'slug' => $v['slug']],
                        [
                            'name' => $v['name'],
                            'price' => $v['price'],
                            'is_default' => $v['is_default'] ?? false,
                            'is_active' => true,
                        ]
                    );
                }
                ProductVariation::where('product_id', $product->id)
                    ->whereNotIn('slug', $keepSlugs)
                    ->delete();
            } else {
                ProductVariation::where('product_id', $product->id)
                    ->where('slug', '!=', 'regular')
                    ->delete();

                ProductVariation::updateOrCreate(
                    ['product_id' => $product->id, 'slug' => 'regular'],
                    [
                        'name' => 'Regular',
                        'price' => $product->base_price,
                        'is_default' => true,
                        'is_active' => true,
                    ]
                );
            }

            if ($product->is_customizable && in_array($prod['cat'], $customizableCats, true)) {
                $sync = [];
                foreach ($toppingModels as $topping) {
                    $sync[$topping->id] = ['is_default' => false];
                }
                $product->toppings()->sync($sync);
            }

            foreach ($stores as $store) {
                $store->products()->syncWithoutDetaching([
                    $product->id => ['is_available' => true],
                ]);
            }
        }
    }
}
