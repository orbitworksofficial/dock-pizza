<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'minimum_order',
        'maximum_discount',
        'usage_limit',
        'usage_per_user',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
        'is_first_order_only',
        'applicable_categories',
        'applicable_products',
        'image',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_order' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_per_user' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'is_first_order_only' => 'boolean',
        'applicable_categories' => 'array',
        'applicable_products' => 'array',
    ];

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'coupon_stores');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }
}
