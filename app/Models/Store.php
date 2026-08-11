<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Earth radius in miles, used by the Haversine distance calculation.
     */
    private const EARTH_RADIUS_MILES = 3958.8;

    /**
     * Great-circle distance in miles between this store and a point.
     *
     * Computed in PHP rather than SQL because SQLite ships without the
     * trigonometric functions a SQL Haversine needs.
     */
    public function distanceTo(float $latitude, float $longitude): ?float
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        $latFrom = deg2rad((float) $this->latitude);
        $lonFrom = deg2rad((float) $this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2;

        return 2 * self::EARTH_RADIUS_MILES * asin(min(1.0, sqrt($a)));
    }

    /**
     * Whether a point falls inside this store's delivery radius.
     */
    public function deliversTo(float $latitude, float $longitude): bool
    {
        $distance = $this->distanceTo($latitude, $longitude);

        return $distance !== null && $distance <= (float) $this->delivery_radius;
    }

    /**
     * Narrow candidate stores to a bounding box around the point before the
     * exact Haversine check, so the PHP pass stays cheap as locations grow.
     *
     * The box is padded by the largest delivery_radius on record, which is
     * always at least as wide as any individual store's true circle.
     */
    public function scopeNearBoundingBox(Builder $query, float $latitude, float $longitude, float $paddingMiles): Builder
    {
        $latPadding = $paddingMiles / 69.0;
        // Longitude degrees compress toward the poles; guard against cos(90°) = 0.
        $lonPadding = $paddingMiles / max(0.1, 69.0 * cos(deg2rad($latitude)));

        return $query
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [$latitude - $latPadding, $latitude + $latPadding])
            ->whereBetween('longitude', [$longitude - $lonPadding, $longitude + $lonPadding]);
    }

    /**
     * The nearest active store that delivers to the given point, or null.
     *
     * Each store is judged against its own delivery_radius so locations can be
     * tuned independently from the admin side.
     *
     * @return array{store: self, distance: float}|null
     */
    public static function findDeliveringTo(float $latitude, float $longitude): ?array
    {
        $maxRadius = (float) (static::query()
            ->where('is_active', true)
            ->where('accepts_delivery', true)
            ->max('delivery_radius') ?? 0);

        if ($maxRadius <= 0) {
            return null;
        }

        $candidates = static::query()
            ->where('is_active', true)
            ->where('accepts_delivery', true)
            ->nearBoundingBox($latitude, $longitude, $maxRadius)
            ->get();

        $matches = [];

        foreach ($candidates as $store) {
            $distance = $store->distanceTo($latitude, $longitude);

            if ($distance !== null && $distance <= (float) $store->delivery_radius) {
                $matches[] = ['store' => $store, 'distance' => $distance];
            }
        }

        if ($matches === []) {
            return null;
        }

        usort($matches, fn (array $a, array $b) => $a['distance'] <=> $b['distance']);

        return $matches[0];
    }

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
