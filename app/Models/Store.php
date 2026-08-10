<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'latitude',
        'longitude',
        'phone',
        'email',
        'image',
        'delivery_radius',
        'minimum_order',
        'delivery_fee',
        'tax_rate',
        'estimated_delivery_time',
        'estimated_pickup_time',
        'is_active',
        'accepts_delivery',
        'accepts_pickup',
        'accepts_catering',
        'features',
        'sort_order',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'delivery_radius' => 'decimal:2',
        'minimum_order' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'estimated_delivery_time' => 'integer',
        'estimated_pickup_time' => 'integer',
        'is_active' => 'boolean',
        'accepts_delivery' => 'boolean',
        'accepts_pickup' => 'boolean',
        'accepts_catering' => 'boolean',
        'features' => 'array',
        'sort_order' => 'integer',
    ];

    public function hours(): HasMany
    {
        return $this->hasMany(StoreHour::class);
    }

    public function deliveryZones(): HasMany
    {
        return $this->hasMany(DeliveryZone::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(StoreSetting::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'store_menu_items')
            ->withPivot('is_available')
            ->withTimestamps();
    }
}
