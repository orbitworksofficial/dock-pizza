<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'short_description',
        'base_price',
        'compare_price',
        'sku',
        'is_active',
        'is_featured',
        'is_new',
        'is_customizable',
        'is_halal',
        'is_gluten_free',
        'is_vegetarian',
        'is_vegan',
        'calories',
        'nutritional_info',
        'allergens',
        'preparation_time',
        'sort_order',
        'views_count',
        'orders_count',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'is_customizable' => 'boolean',
        'is_halal' => 'boolean',
        'is_gluten_free' => 'boolean',
        'is_vegetarian' => 'boolean',
        'is_vegan' => 'boolean',
        'calories' => 'integer',
        'nutritional_info' => 'array',
        'allergens' => 'array',
        'preparation_time' => 'integer',
        'sort_order' => 'integer',
        'views_count' => 'integer',
        'orders_count' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class)->orderBy('sort_order');
    }

    public function toppings(): BelongsToMany
    {
        return $this->belongsToMany(Topping::class, 'product_toppings')
            ->withPivot('price_override', 'is_default');
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_menu_items')
            ->withPivot('is_available')
            ->withTimestamps();
    }
}
