<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\Store;
use App\Models\Topping;
use App\Models\ToppingCategory;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Premium Pizzas',
                'slug' => 'premium-pizzas',
                'description' => 'Fresh off the dock — our signature specialty pizzas loaded with premium toppings and dock seasoning.',
                'image' => 'assets/images/menu/dock-pizza.png',
                'icon' => 'anchor',
                'color' => '#1B3A5C',
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Single Topping',
                'slug' => 'single-topping',
                'description' => 'Build your perfect pizza — choose one topping on our hand-tossed crust.',
                'image' => 'assets/images/menu/single-topping-pizza.png',
                'icon' => 'pizza-slice',
                'color' => '#E07B2D',
                'sort_order' => 2,
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Salads',
                'slug' => 'salads',
                'description' => 'Fresh, crisp salads prepared daily with your choice of dressings and add-ons.',
                'image' => 'assets/images/menu/greek-salad.png',
                'icon' => 'leaf',
                'color' => '#16A34A',
                'sort_order' => 3,
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Salad Add-Ons',
                'slug' => 'salad-add-ons',
                'description' => 'Extra proteins and toppings for your salad.',
                'icon' => 'plus',
                'color' => '#16A34A',
                'sort_order' => 4,
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Sides & Apps',
                'slug' => 'sides-apps',
                'description' => 'Perfect companions to your pizza — crispy fries, wings, and more.',
                'image' => 'assets/images/menu/mozzarella-sticks.png',
                'icon' => 'utensils',
                'color' => '#D97706',
                'sort_order' => 5,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Drinks',
                'slug' => 'drinks',
                'description' => 'Soda can, 20oz fountain, or 2 liter — ice-cold beverages.',
                'image' => 'assets/images/menu/drinks-category.png',
                'icon' => 'glass-water',
                'color' => '#2563EB',
                'sort_order' => 6,
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Desserts',
                'slug' => 'desserts',
                'description' => 'Sweet treats from our printed menu.',
                'image' => 'assets/images/menu/cheesecake.png',
                'icon' => 'ice-cream',
                'color' => '#BE185D',
                'sort_order' => 7,
                'is_active' => true,
                'is_featured' => false,
            ],
        ];

        $categoryModels = [];
        foreach ($categories as $cat) {
            $categoryModels[$cat['slug']] = Category::create($cat);
        }

        $toppingCategories = [
            [
                'name' => 'Cheese & Sauce',
                'slug' => 'cheese-sauce',
                'sort_order' => 1,
                'is_active' => true,
                'max_selections' => 3,
                'is_required' => false,
            ],
            [
                'name' => 'Premium Meats',
                'slug' => 'meats',
                'sort_order' => 2,
                'is_active' => true,
                'max_selections' => null,
                'is_required' => false,
            ],
            [
                'name' => 'Fresh Veggies',
                'slug' => 'veggies',
                'sort_order' => 3,
                'is_active' => true,
                'max_selections' => null,
                'is_required' => false,
            ],
        ];

        $toppingCategoryModels = [];
        foreach ($toppingCategories as $tc) {
            $toppingCategoryModels[$tc['slug']] = ToppingCategory::create($tc);
        }

        $toppings = [
            ['tc' => 'cheese-sauce', 'name' => 'Extra Cheese', 'slug' => 'extra-cheese', 'price' => 1.99, 'is_premium' => false],
            ['tc' => 'cheese-sauce', 'name' => 'Feta Cheese', 'slug' => 'feta-cheese', 'price' => 2.49, 'is_premium' => true],
            ['tc' => 'cheese-sauce', 'name' => 'Alfredo Sauce', 'slug' => 'alfredo-sauce', 'price' => 1.49, 'is_premium' => false],

            ['tc' => 'meats', 'name' => 'Pepperoni', 'slug' => 'pepperoni', 'price' => 2.25, 'is_premium' => false],
            ['tc' => 'meats', 'name' => 'Italian Sausage', 'slug' => 'italian-sausage', 'price' => 2.25, 'is_premium' => false],
            ['tc' => 'meats', 'name' => 'Smoked Ham', 'slug' => 'smoked-ham', 'price' => 2.25, 'is_premium' => false],
            ['tc' => 'meats', 'name' => 'Bacon', 'slug' => 'bacon', 'price' => 2.75, 'is_premium' => true],
            ['tc' => 'meats', 'name' => 'Grilled Chicken', 'slug' => 'grilled-chicken', 'price' => 2.75, 'is_premium' => true],

            ['tc' => 'veggies', 'name' => 'Mushrooms', 'slug' => 'mushrooms', 'price' => 1.75, 'is_premium' => false],
            ['tc' => 'veggies', 'name' => 'Onions', 'slug' => 'onions', 'price' => 1.50, 'is_premium' => false],
            ['tc' => 'veggies', 'name' => 'Green Peppers', 'slug' => 'green-peppers', 'price' => 1.50, 'is_premium' => false],
            ['tc' => 'veggies', 'name' => 'Black Olives', 'slug' => 'black-olives', 'price' => 1.75, 'is_premium' => false],
            ['tc' => 'veggies', 'name' => 'Jalapeños', 'slug' => 'jalapenos', 'price' => 1.50, 'is_premium' => false],
            ['tc' => 'veggies', 'name' => 'Fresh Spinach', 'slug' => 'fresh-spinach', 'price' => 1.75, 'is_premium' => false],
            ['tc' => 'veggies', 'name' => 'Pineapple', 'slug' => 'pineapple', 'price' => 1.75, 'is_premium' => false],
        ];

        $toppingModels = [];
        foreach ($toppings as $top) {
            $tcModel = $toppingCategoryModels[$top['tc']];
            $toppingModels[$top['slug']] = Topping::create([
                'topping_category_id' => $tcModel->id,
                'name' => $top['name'],
                'slug' => $top['slug'],
                'price' => $top['price'],
                'is_premium' => $top['is_premium'],
                'is_active' => true,
            ]);
        }

        $premiumSizes = [
            ['name' => 'Small 10"', 'slug' => 'small', 'price' => 17.99],
            ['name' => 'Medium 12"', 'slug' => 'medium', 'price' => 19.99],
            ['name' => 'Large 14"', 'slug' => 'large', 'price' => 20.99, 'is_default' => true],
            ['name' => 'X-Large 16"', 'slug' => 'x-large', 'price' => 22.99],
        ];

        $singleToppingSizes = [
            ['name' => 'Small 10"', 'slug' => 'small', 'price' => 8.99],
            ['name' => 'Medium 12"', 'slug' => 'medium', 'price' => 10.99],
            ['name' => 'Large 14"', 'slug' => 'large', 'price' => 14.99, 'is_default' => true],
            ['name' => 'X-Large 16"', 'slug' => 'x-large', 'price' => 18.99],
        ];

        $productsData = [
            // Premium Pizzas
            [
                'cat' => 'premium-pizzas',
                'name' => 'Supreme Pizza',
                'slug' => 'supreme-pizza',
                'description' => 'Pepperoni, sausage, ground beef, bacon, green peppers, mushroom, onion, olive, jalapeño.',
                'short_description' => 'The ultimate loaded supreme.',
                'base_price' => 17.99,
                'is_featured' => true,
                'is_customizable' => true,
                'icon' => 'shell',
                'variations' => $premiumSizes,
            ],
            [
                'cat' => 'premium-pizzas',
                'name' => 'Dock Pizza',
                'slug' => 'dock-pizza',
                'description' => 'Crispy chicken, green pepper, onion, tomato, olive, dock seasoning.',
                'short_description' => 'Our signature house specialty.',
                'base_price' => 17.99,
                'is_featured' => true,
                'is_customizable' => true,
                'icon' => 'anchor',
                'variations' => $premiumSizes,
            ],
            [
                'cat' => 'premium-pizzas',
                'name' => 'Polo Ranch Pizza',
                'slug' => 'polo-ranch-pizza',
                'description' => 'Red onion, BBQ sauce, chicken.',
                'short_description' => 'Tangy BBQ chicken favorite.',
                'base_price' => 17.99,
                'is_featured' => false,
                'is_customizable' => true,
                'icon' => 'compass',
                'variations' => $premiumSizes,
            ],
            [
                'cat' => 'premium-pizzas',
                'name' => 'Mega Meat',
                'slug' => 'mega-meat',
                'description' => 'Pepperoni, bacon, sausage, ground beef, ham, salami.',
                'short_description' => 'For serious meat lovers.',
                'base_price' => 17.99,
                'is_featured' => true,
                'is_customizable' => true,
                'icon' => 'fish',
                'variations' => $premiumSizes,
            ],
            [
                'cat' => 'premium-pizzas',
                'name' => 'Feta Feast',
                'slug' => 'feta-feast',
                'description' => 'Tzatziki sauce, gyro meat, red onion, black olives, tomato, spinach, feta cheese.',
                'short_description' => 'Mediterranean-inspired delight.',
                'base_price' => 17.99,
                'is_featured' => false,
                'is_customizable' => true,
                'is_vegetarian' => false,
                'icon' => 'ship',
                'variations' => $premiumSizes,
            ],

            // Single Topping
            [
                'cat' => 'single-topping',
                'name' => 'Single Topping Pizza',
                'slug' => 'single-topping-pizza',
                'description' => 'Build your own pizza with one topping of your choice on our hand-tossed crust.',
                'short_description' => 'Your choice, one topping.',
                'base_price' => 8.99,
                'is_featured' => false,
                'is_customizable' => true,
                'variations' => $singleToppingSizes,
            ],

            // Salads
            [
                'cat' => 'salads',
                'name' => 'Garden Salad',
                'slug' => 'garden-salad',
                'description' => 'Fresh mixed greens, tomatoes, cucumbers, carrots, and croutons.',
                'short_description' => 'Classic garden fresh.',
                'base_price' => 8.99,
                'is_featured' => false,
                'is_customizable' => false,
            ],
            [
                'cat' => 'salads',
                'name' => 'Caesar Salad',
                'slug' => 'caesar-salad',
                'description' => 'Crisp romaine lettuce, parmesan cheese, croutons, and Caesar dressing.',
                'short_description' => 'Timeless Caesar classic.',
                'base_price' => 7.99,
                'is_featured' => false,
                'is_customizable' => false,
            ],
            [
                'cat' => 'salads',
                'name' => 'Greek Salad',
                'slug' => 'greek-salad',
                'description' => 'Romaine, feta cheese, kalamata olives, red onion, tomato, and cucumber.',
                'short_description' => 'Mediterranean flavors.',
                'base_price' => 10.99,
                'is_featured' => false,
                'is_customizable' => false,
            ],
            [
                'cat' => 'salads',
                'name' => 'Chef Salad',
                'slug' => 'chef-salad',
                'description' => 'Mixed greens topped with ham, turkey, cheese, hard-boiled egg, and fresh vegetables.',
                'short_description' => 'Hearty and satisfying.',
                'base_price' => 11.99,
                'is_featured' => false,
                'is_customizable' => false,
            ],
            [
                'cat' => 'salads',
                'name' => 'Grilled Chicken Salad',
                'slug' => 'grilled-chicken-salad',
                'description' => 'Mixed greens with grilled chicken breast, tomatoes, cucumbers, and your choice of dressing.',
                'short_description' => 'Protein-packed favorite.',
                'base_price' => 12.99,
                'is_featured' => true,
                'is_customizable' => false,
            ],
            [
                'cat' => 'salads',
                'name' => 'Cobb Salad',
                'slug' => 'bolis-cobb-salad',
                'description' => 'Mixed greens, grilled chicken, bacon, hard-boiled egg, tomato, cucumber, and cheese.',
                'short_description' => 'Loaded Cobb-style salad.',
                'base_price' => 13.99,
                'is_featured' => true,
                'is_customizable' => false,
            ],

            // Salad Add-Ons
            [
                'cat' => 'salad-add-ons',
                'name' => 'Grilled Chicken Add-On',
                'slug' => 'grilled-chicken-add-on',
                'description' => 'Add grilled chicken to any salad.',
                'short_description' => 'Salad protein add-on.',
                'base_price' => 3.99,
                'is_featured' => false,
                'is_customizable' => false,
            ],
            [
                'cat' => 'salad-add-ons',
                'name' => 'Crispy Chicken Add-On',
                'slug' => 'crispy-chicken-add-on',
                'description' => 'Add crispy chicken to any salad.',
                'short_description' => 'Salad protein add-on.',
                'base_price' => 3.49,
                'is_featured' => false,
                'is_customizable' => false,
            ],
            [
                'cat' => 'salad-add-ons',
                'name' => 'Grilled Shrimp Add-On',
                'slug' => 'grilled-shrimp-add-on',
                'description' => 'Add grilled shrimp to any salad.',
                'short_description' => 'Salad seafood add-on.',
                'base_price' => 4.49,
                'is_featured' => false,
                'is_customizable' => false,
            ],
            [
                'cat' => 'salad-add-ons',
                'name' => 'Boiled Egg Add-On',
                'slug' => 'boiled-egg-add-on',
                'description' => 'Add a boiled egg to any salad.',
                'short_description' => 'Salad add-on.',
                'base_price' => 0.99,
                'is_featured' => false,
                'is_customizable' => false,
            ],
            [
                'cat' => 'salad-add-ons',
                'name' => 'Gyro Meat Add-On',
                'slug' => 'gyro-meat-add-on',
                'description' => 'Add gyro meat to any salad.',
                'short_description' => 'Salad protein add-on.',
                'base_price' => 4.49,
                'is_featured' => false,
                'is_customizable' => false,
            ],

            // Sides & Apps
            [
                'cat' => 'sides-apps',
                'name' => 'French Fries',
                'slug' => 'french-fries',
                'description' => 'Golden crispy fries, lightly seasoned.',
                'short_description' => 'Crispy golden fries.',
                'base_price' => 6.99,
                'is_featured' => false,
                'is_customizable' => false,
                'variations' => [
                    ['name' => 'Small', 'slug' => 'small', 'price' => 4.99, 'is_default' => true],
                    ['name' => 'Large', 'slug' => 'large', 'price' => 6.99],
                ],
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Mozzarella Sticks',
                'slug' => 'mozzarella-sticks',
                'description' => 'Six golden-fried mozzarella sticks served with marinara sauce.',
                'short_description' => 'Cheesy and crispy.',
                'base_price' => 7.99,
                'is_featured' => true,
                'is_customizable' => false,
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Onion Rings',
                'slug' => 'onion-rings',
                'description' => 'Beer-battered onion rings, fried to perfection.',
                'short_description' => 'Crispy beer-battered rings.',
                'base_price' => 6.99,
                'is_featured' => false,
                'is_customizable' => false,
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Garlic Bread',
                'slug' => 'garlic-bread',
                'description' => 'Toasted bread brushed with garlic butter and herbs.',
                'short_description' => 'Warm and buttery.',
                'base_price' => 5.99,
                'is_featured' => false,
                'is_customizable' => false,
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Garlic Cheese Bread',
                'slug' => 'garlic-cheese-bread',
                'description' => 'Garlic bread topped with melted mozzarella cheese.',
                'short_description' => 'Cheesy garlic goodness.',
                'base_price' => 7.49,
                'is_featured' => false,
                'is_customizable' => false,
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Chicken Tenders',
                'slug' => 'chicken-tenders',
                'description' => 'Five crispy breaded chicken tenders with your choice of dipping sauce.',
                'short_description' => 'Crispy chicken strips.',
                'base_price' => 8.99,
                'is_featured' => false,
                'is_customizable' => false,
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Jalapeño Poppers',
                'slug' => 'jalapeno-poppers',
                'description' => 'Cream cheese stuffed jalapeños, breaded and fried.',
                'short_description' => 'Spicy and creamy.',
                'base_price' => 7.99,
                'is_featured' => false,
                'is_customizable' => false,
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Fried Mushrooms',
                'slug' => 'fried-mushrooms',
                'description' => 'Breaded and fried button mushrooms served with ranch.',
                'short_description' => 'Savory fried mushrooms.',
                'base_price' => 6.99,
                'is_featured' => false,
                'is_customizable' => false,
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Breadsticks',
                'slug' => 'breadsticks',
                'description' => 'Soft baked breadsticks served with marinara dipping sauce.',
                'short_description' => 'Warm breadsticks.',
                'base_price' => 5.49,
                'is_featured' => false,
                'is_customizable' => false,
                'image' => 'assets/images/menu/breadsticks.png',
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Buffalo Wings',
                'slug' => 'buffalo-wings',
                'description' => 'Eight jumbo wings tossed in hot Buffalo sauce with blue cheese or ranch.',
                'short_description' => 'Spicy Buffalo wings.',
                'base_price' => 9.99,
                'is_featured' => true,
                'is_customizable' => false,
                'variations' => [
                    ['name' => '8 Pieces', 'slug' => '8-pcs', 'price' => 9.99, 'is_default' => true],
                    ['name' => '16 Pieces', 'slug' => '16-pcs', 'price' => 17.99],
                ],
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Western Fries',
                'slug' => 'western-fries',
                'description' => 'Seasoned western-style fries.',
                'short_description' => 'Crispy western fries.',
                'base_price' => 5.99,
                'is_featured' => false,
                'is_customizable' => false,
                'variations' => [
                    ['name' => 'Small', 'slug' => 'small', 'price' => 4.49, 'is_default' => true],
                    ['name' => 'Large', 'slug' => 'large', 'price' => 5.99],
                ],
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Plain Potato Chips',
                'slug' => 'plain-potato-chips',
                'description' => 'Classic plain potato chips.',
                'short_description' => 'Crispy chips.',
                'base_price' => 0.89,
                'is_featured' => false,
                'is_customizable' => false,
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'BBQ Potato Chips',
                'slug' => 'bbq-potato-chips',
                'description' => 'BBQ flavored potato chips.',
                'short_description' => 'BBQ chips.',
                'base_price' => 0.89,
                'is_featured' => false,
                'is_customizable' => false,
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Chicken Strips with Fries',
                'slug' => 'chicken-strips-with-fries',
                'description' => 'Crispy chicken strips served with golden fries.',
                'short_description' => 'Chicken strips combo.',
                'base_price' => 9.49,
                'is_featured' => false,
                'is_customizable' => false,
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Butterfly Shrimp',
                'slug' => 'butterfly-shrimp',
                'description' => 'Breaded butterfly shrimp, fried golden and served with cocktail sauce.',
                'short_description' => 'Crispy butterfly shrimp.',
                'base_price' => 10.99,
                'is_featured' => false,
                'is_customizable' => false,
            ],
            [
                'cat' => 'sides-apps',
                'name' => 'Dock Chips',
                'slug' => 'dock-chips',
                'description' => 'House-made seasoned potato chips.',
                'short_description' => 'Crispy dock-style chips.',
                'base_price' => 3.99,
                'is_featured' => false,
                'is_customizable' => false,
            ],

            // Drinks — separate Can / 20oz / 2 Liter products
            [
                'cat' => 'drinks',
                'name' => 'Soda Can',
                'slug' => 'soda-can',
                'description' => 'Ice-cold canned soda.',
                'short_description' => 'Canned soda.',
                'base_price' => 1.49,
                'is_featured' => false,
                'is_customizable' => false,
                'image' => 'assets/images/menu/drink-can.png',
            ],
            [
                'cat' => 'drinks',
                'name' => '20oz Soda',
                'slug' => 'soda-20oz',
                'description' => 'Refreshing 20oz fountain soda with ice.',
                'short_description' => '20oz fountain soda.',
                'base_price' => 2.99,
                'is_featured' => false,
                'is_customizable' => false,
                'image' => 'assets/images/menu/drink-20oz.png',
            ],
            [
                'cat' => 'drinks',
                'name' => '2 Liter Soda',
                'slug' => 'soda-2-liter',
                'description' => 'Ice-cold 2 liter soda bottle.',
                'short_description' => '2 liter bottle.',
                'base_price' => 3.99,
                'is_featured' => false,
                'is_customizable' => false,
                'image' => 'assets/images/menu/drink-2-liter.png',
            ],

            // Desserts (printed menu)
            [
                'cat' => 'desserts',
                'name' => "Ben & Jerry's Ice Cream Pint",
                'slug' => 'ben-jerrys-ice-cream-pint',
                'description' => "A full pint of Ben & Jerry's ice cream.",
                'short_description' => "Ben & Jerry's pint.",
                'base_price' => 7.99,
                'is_featured' => true,
                'is_customizable' => false,
                'image' => 'assets/images/menu/ben-jerrys-ice-cream-pint.png',
            ],
            [
                'cat' => 'desserts',
                'name' => 'Cheesecake',
                'slug' => 'cheesecake',
                'description' => 'Creamy cheesecake slice.',
                'short_description' => 'Classic cheesecake.',
                'base_price' => 3.99,
                'is_featured' => false,
                'is_customizable' => false,
                'image' => 'assets/images/menu/cheesecake.png',
            ],
            [
                'cat' => 'desserts',
                'name' => 'Carrot Cake',
                'slug' => 'carrot-cake',
                'description' => 'Moist carrot cake slice.',
                'short_description' => 'Classic carrot cake.',
                'base_price' => 3.99,
                'is_featured' => false,
                'is_customizable' => false,
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
                'is_customizable' => false,
                'image' => 'assets/images/menu/funnel-cake.png',
            ],
        ];

        $allStores = Store::all();
        $customizableCategories = ['premium-pizzas', 'single-topping'];

        foreach ($productsData as $prod) {
            $catModel = $categoryModels[$prod['cat']];

            $product = Product::create([
                'category_id' => $catModel->id,
                'name' => $prod['name'],
                'slug' => $prod['slug'],
                'description' => $prod['description'],
                'short_description' => $prod['short_description'],
                'base_price' => $prod['base_price'],
                'compare_price' => $prod['compare_price'] ?? null,
                'is_featured' => $prod['is_featured'],
                'is_customizable' => $prod['is_customizable'],
                'is_vegetarian' => $prod['is_vegetarian'] ?? false,
                'calories' => $prod['calories'] ?? null,
                'is_active' => true,
            ]);

            ProductImage::create([
                'product_id' => $product->id,
                'path' => $prod['image'] ?? "assets/images/menu/{$product->slug}.jpg",
                'alt_text' => $product->name,
                'is_primary' => true,
                'sort_order' => 1,
            ]);

            if (!empty($prod['gallery'])) {
                foreach ($prod['gallery'] as $index => $galleryPath) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $galleryPath,
                        'alt_text' => $product->name,
                        'is_primary' => false,
                        'sort_order' => $index + 2,
                    ]);
                }
            }

            if (isset($prod['variations'])) {
                foreach ($prod['variations'] as $v) {
                    ProductVariation::create([
                        'product_id' => $product->id,
                        'name' => $v['name'],
                        'slug' => $v['slug'],
                        'price' => $v['price'],
                        'is_default' => $v['is_default'] ?? false,
                        'is_active' => true,
                    ]);
                }
            } else {
                ProductVariation::create([
                    'product_id' => $product->id,
                    'name' => 'Regular',
                    'slug' => 'regular',
                    'price' => $product->base_price,
                    'is_default' => true,
                    'is_active' => true,
                ]);
            }

            if ($product->is_customizable && in_array($prod['cat'], $customizableCategories)) {
                foreach ($toppingModels as $topModel) {
                    $product->toppings()->attach($topModel->id, ['is_default' => false]);
                }
            }

            foreach ($allStores as $store) {
                $store->products()->attach($product->id, ['is_available' => true]);
            }
        }
    }
}
