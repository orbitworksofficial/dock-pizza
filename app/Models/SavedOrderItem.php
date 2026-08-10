<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedOrderItem extends Model
{
    protected $fillable = [
        'saved_order_id',
        'product_id',
        'product_variation_id',
        'quantity',
        'toppings',
        'special_instructions',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'toppings' => 'array',
    ];

    public function savedOrder(): BelongsTo
    {
        return $this->belongsTo(SavedOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }
}
